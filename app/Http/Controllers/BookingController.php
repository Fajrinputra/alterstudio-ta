<?php

namespace App\Http\Controllers;

use App\Enums\Role;
use App\Models\Booking;
use App\Models\Payment;
use App\Models\Project;
use App\Models\ServicePackage;
use App\Support\BookingAvailability;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Support\Carbon;

/**
 * Menangani alur pemesanan klien serta pemantauan status untuk admin dan manajer.
 */
class BookingController extends Controller
{
    public function __construct(protected BookingAvailability $availability)
    {
    }

    /** Menampilkan daftar pemesanan sesuai peran pengguna. */
    public function index(Request $request)
    {
        $user = $request->user();

        $query = Booking::with([
            'package',
            'payments',
            'project.scheduleRecord.photographerAssignment.user',
            'project.scheduleRecord.editorAssignment.user',
            'client',
            'studioLocation',
            'studioRoom',
        ]);

        if ($user->role === Role::CLIENT) {
            $query->where('client_id', $user->id);
        } else {
            // Filter tambahan hanya dipakai oleh admin dan manajer.
            if ($request->filled('status')) {
                $status = $request->get('status');

                if ($status === 'SUBMITTED') {
                    $query->where('status', Booking::STATUS_WAITING_PAYMENT)
                        ->whereNull('confirmed_at');
                } elseif ($status === Booking::STATUS_WAITING_PAYMENT) {
                    $query->where('status', Booking::STATUS_WAITING_PAYMENT)
                        ->whereNotNull('confirmed_at');
                } else {
                    $query->where('status', $status);
                }
            }

            if ($request->filled('schedule_status')) {
                $hasSchedule = $request->get('schedule_status') === 'scheduled';
                $query->whereHas('project', function ($q) use ($hasSchedule) {
                    $hasSchedule
                        ? $q->whereNotNull('start_at')
                        : $q->whereNull('start_at');
                });
            }

            if ($request->filled('package_id')) {
                $query->where('package_id', $request->get('package_id'));
            }

            if ($request->filled('date_from')) {
                $query->whereDate('booking_date', '>=', $request->get('date_from'));
            }
            if ($request->filled('date_to')) {
                $query->whereDate('booking_date', '<=', $request->get('date_to'));
            }

            if ($request->filled('q')) {
                $keyword = $request->get('q');
                $query->where(function ($q) use ($keyword) {
                    $q->where('id', $keyword)
                      ->orWhereHas('client', fn($c) => $c->where('name', 'like', "%{$keyword}%"))
                      ->orWhereHas('package', fn($p) => $p->where('name', 'like', "%{$keyword}%"));
                });
            }
        }

        $bookings = $query->latest()->paginate(15);

        if ($request->wantsJson()) {
            return response()->json($bookings);
        }

        if ($user->role === Role::CLIENT) {
            return view('client.booking.index', compact('bookings'));
        }

        $statuses = [
            'SUBMITTED',
            Booking::STATUS_WAITING_PAYMENT,
            Booking::STATUS_DP_PAID,
            Booking::STATUS_PAID,
            Booking::STATUS_CANCELLED,
        ];
        $clients = \App\Models\User::where('role', Role::CLIENT)->orderBy('name')->get();
        $packages = ServicePackage::orderBy('name')->get();
        return view('admin.booking.index', compact('bookings','statuses','clients','packages'));
    }

    /** Menampilkan form pemesanan untuk klien. */
    public function create(Request $request)
    {
        $packages = ServicePackage::where('is_active', true)->orderBy('name')->get();
        $selectedPackage = null;

        if ($request->filled('package_id')) {
            $selectedPackage = ServicePackage::where('is_active', true)->find($request->integer('package_id'));
        }

        if (!$selectedPackage && $packages->count() === 1) {
            $selectedPackage = $packages->first();
        }

        $addonOptions = $selectedPackage ? $this->normalizePackageAddons($selectedPackage) : [];
        $locations = \App\Models\StudioLocation::where('is_active', true)->orderBy('name')->get();
        $maxBookingDate = Carbon::today()->addMonth()->toDateString();

        return view('client.booking.create', compact('packages', 'locations', 'selectedPackage', 'addonOptions', 'maxBookingDate'));
    }

    public function availability(Request $request)
    {
        $maxBookingDate = Carbon::today()->addMonth()->toDateString();

        $validated = $request->validate([
            'package_id' => ['required', Rule::exists(ServicePackage::class, 'id')],
            'studio_location_id' => ['required', 'exists:studio_locations,id'],
            'booking_date' => ['required', 'date', 'after_or_equal:today', 'before_or_equal:'.$maxBookingDate],
            'selected_addons' => ['nullable', 'array'],
            'selected_addons.*' => ['string'],
            'addon_quantities' => ['nullable', 'array'],
            'addon_quantities.*' => ['nullable', 'integer', 'min:1'],
        ], [
            'booking_date.required' => 'Tanggal pemesanan wajib dipilih.',
            'booking_date.after_or_equal' => 'Tanggal pemesanan tidak boleh sebelum hari ini.',
            'booking_date.before_or_equal' => 'Tanggal pemesanan hanya dapat dipilih maksimal 1 bulan ke depan.',
        ]);

        $package = ServicePackage::findOrFail($validated['package_id']);
        $date = Carbon::parse($validated['booking_date']);
        $chosenAddons = $this->chosenAddonsFromRequest($package, $validated);
        $extraDuration = Booking::extraDurationMinutesFromAddons($chosenAddons);
        $effectiveDuration = max(1, (int) ($package->duration_minutes ?? 60)) + $extraDuration;

        $slots = $this->availability->availableSlots(
            $package,
            (int) $validated['studio_location_id'],
            $date,
            $extraDuration
        );

        return response()->json([
            'date' => $date->toDateString(),
            'is_closed' => $this->availability->isClosedDate($date, (int) $validated['studio_location_id']),
            'reason' => $this->availability->closedReason($date, (int) $validated['studio_location_id']),
            'is_today' => $date->isToday(),
            'current_time' => Carbon::now()->format('H:i'),
            'duration_minutes' => $effectiveDuration,
            'base_duration_minutes' => max(1, (int) ($package->duration_minutes ?? 60)),
            'extra_duration_minutes' => $extraDuration,
            'buffer_minutes' => $this->availability->bufferMinutes(),
            'available_times' => $slots,
        ]);
    }

    /** Menyimpan pemesanan baru sebagai pengajuan dan langsung membuat project awal. */
    public function store(Request $request)
    {
        $user = Auth::user();
        $maxBookingDate = Carbon::today()->addMonth()->toDateString();

        $validated = $request->validate([
            'package_id' => ['required', Rule::exists(ServicePackage::class, 'id')],
            'booking_date' => ['required', 'date', 'after_or_equal:today', 'before_or_equal:'.$maxBookingDate],
            'booking_time' => ['required', 'date_format:H:i'],
            'studio_location_id' => ['required', 'exists:studio_locations,id'],
            'notes' => ['nullable', 'string'],
            'payment_type' => ['required', 'in:' . Booking::PAYMENT_TYPE_DP . ',' . Booking::PAYMENT_TYPE_FULL],
            'selected_addons' => ['nullable', 'array'],
            'selected_addons.*' => ['string'],
            'addon_quantities' => ['nullable', 'array'],
            'addon_quantities.*' => ['nullable', 'integer', 'min:1'],
        ], [
            'booking_date.required' => 'Tanggal pemesanan wajib dipilih.',
            'booking_date.after_or_equal' => 'Tanggal pemesanan tidak boleh sebelum hari ini.',
            'booking_date.before_or_equal' => 'Tanggal pemesanan hanya dapat dipilih maksimal 1 bulan ke depan.',
        ]);

        $package = ServicePackage::findOrFail($validated['package_id']);
        $bookingDate = Carbon::parse($validated['booking_date']);
        $chosenAddons = $this->chosenAddonsFromRequest($package, $validated);
        $extraDuration = Booking::extraDurationMinutesFromAddons($chosenAddons);

        if ($this->availability->isClosedDate($bookingDate, (int) $validated['studio_location_id'])) {
            return back()
                ->withInput()
                ->withErrors(['booking_date' => $this->availability->closedReason($bookingDate, (int) $validated['studio_location_id'])]);
        }

        $availableRoom = $this->availability->availableRoomForSlot(
            $package,
            (int) $validated['studio_location_id'],
            $bookingDate,
            $validated['booking_time'],
            $extraDuration
        );

        if (! $availableRoom) {
            return back()
                ->withInput()
                ->withErrors(['booking_time' => 'Jam yang dipilih sudah tidak tersedia. Silakan pilih slot lain yang masih kosong.']);
        }

        $addonTotal = (int) collect($chosenAddons)->sum('subtotal');
        $totalPrice = (int) $package->price + $addonTotal;

        $booking = Booking::create([
            'client_id' => $user->id,
            'package_id' => $package->id,
            'studio_location_id' => $validated['studio_location_id'],
            'studio_room_id' => $availableRoom->id,
            'booking_date' => $validated['booking_date'],
            'booking_time' => $validated['booking_time'],
            'notes' => $validated['notes'] ?? null,
            'status' => Booking::STATUS_WAITING_PAYMENT,
            'confirmed_at' => null,
            'payment_started_at' => null,
            'payment_type' => $validated['payment_type'],
            'addon_total' => $addonTotal,
            'total_price' => $totalPrice,
        ]);
        $this->syncBookingAddons($booking, $chosenAddons);

        Project::create([
            'booking_id' => $booking->id,
            'status' => Project::STATUS_DRAFT,
        ]);

        // Kirim notifikasi bahwa ada pemesanan baru yang perlu ditinjau.
        $admins = \App\Models\User::whereIn('role', [Role::ADMIN, Role::OWNER])->get();
        $notification = new \App\Notifications\BookingCreatedNotification(
            $booking->load(['package', 'client', 'studioLocation', 'studioRoom'])
        );
        $user->notify($notification);
        \Illuminate\Support\Facades\Notification::send($admins, $notification);

        if ($request->wantsJson()) {
            $booking->load(['package', 'project']);

            return response()->json([
                'id' => $booking->id,
                'status' => $booking->status,
                'display_status' => $booking->statusLabel(),
                'total_price' => $booking->total_price,
                'package' => [
                    'id' => $booking->package?->id,
                    'name' => $booking->package?->name,
                ],
                'project' => [
                    'id' => $booking->project?->id,
                    'status' => $booking->project?->status,
                ],
            ], 201);
        }

        return redirect()
            ->route('bookings.index')
            ->with('success', 'Pemesanan berhasil dikirim. Admin akan meninjau terlebih dahulu sebelum pembayaran dibuka.');
    }

    /** Menampilkan detail pemesanan; klien hanya boleh melihat miliknya sendiri. */
    public function show(Booking $booking)
    {
        $user = Auth::user();

        if ($user->role === Role::CLIENT && $booking->client_id !== $user->id) {
            abort(403);
        }

        return response()->json($booking->load(['package', 'project', 'payments']));
    }

    /** Admin mengubah status pemesanan: konfirmasi, pelunasan manual, atau pembatalan. */
    public function updateStatus(Request $request, Booking $booking)
    {
        $request->validate([
            'status' => ['required', 'in:' . implode(',', [
                Booking::STATUS_WAITING_PAYMENT,
                Booking::STATUS_DP_PAID,
                Booking::STATUS_PAID,
                Booking::STATUS_CANCELLED,
            ])],
        ]);

        $targetStatus = $request->string('status')->toString();
        $previousStatus = $booking->status;
        $remainingAmount = $booking->remainingAmount();
        $allowedTransitions = match (true) {
            $booking->isSubmitted() => [Booking::STATUS_WAITING_PAYMENT, Booking::STATUS_CANCELLED],
            $booking->isConfirmedAwaitingPayment() => [Booking::STATUS_CANCELLED],
            $booking->status === Booking::STATUS_DP_PAID => [Booking::STATUS_PAID],
            default => [],
        };

        if (! in_array($targetStatus, $allowedTransitions, true)) {
            return back()->with('error', 'Perubahan status tidak valid untuk kondisi pemesanan saat ini.');
        }

        $justConfirmed = $targetStatus === Booking::STATUS_WAITING_PAYMENT && $booking->confirmed_at === null;
        $updates = ['status' => $targetStatus];

        if ($justConfirmed) {
            $updates['confirmed_at'] = Carbon::now();
            $updates['payment_started_at'] = null;
        }

        $booking->update($updates);

        $pendingPayment = $booking->payments()
            ->where('status', Payment::STATUS_PENDING)
            ->when(
                $targetStatus === Booking::STATUS_PAID && $previousStatus === Booking::STATUS_DP_PAID,
                fn ($query) => $query->where('type', Payment::TYPE_FULL)
            )
            ->latest()
            ->first();

        if ($justConfirmed) {
            $booking->client?->notify(new \App\Notifications\BookingConfirmedNotification($booking->fresh()));
        }

        if (in_array($targetStatus, [Booking::STATUS_DP_PAID, Booking::STATUS_PAID], true) && $pendingPayment) {
            $pendingPayment->update([
                'status' => Payment::STATUS_PAID,
                'paid_at' => Carbon::now(),
                'transaction_status' => $pendingPayment->transaction_status ?: 'manual',
            ]);
        } elseif ($targetStatus === Booking::STATUS_PAID && $previousStatus === Booking::STATUS_DP_PAID && $remainingAmount > 0) {
            Payment::create([
                'booking_id' => $booking->id,
                'type' => Payment::TYPE_FULL,
                'amount' => $remainingAmount,
                'status' => Payment::STATUS_PAID,
                'reference' => 'manual_onsite_settlement',
                'transaction_status' => 'manual',
                'paid_at' => Carbon::now(),
            ]);
        }

        if ($targetStatus === Booking::STATUS_CANCELLED) {
            $booking->payments()
                ->where('status', Payment::STATUS_PENDING)
                ->update([
                    'status' => Payment::STATUS_FAILED,
                    'transaction_status' => 'cancelled_by_admin',
                    'paid_at' => null,
                ]);
        }

        return back()->with('success', 'Status pemesanan diperbarui.');
    }

    /** Menampilkan halaman pembayaran untuk klien. */
    public function pay(Booking $booking)
    {
        $user = Auth::user();
        if ($user->role === Role::CLIENT && $booking->client_id !== $user->id) {
            abort(403);
        }

        $booking->loadMissing(['package', 'payments']);

        if ($booking->status === Booking::STATUS_CANCELLED) {
            return redirect()
                ->route('bookings.index')
                ->with('error', 'Pemesanan sudah dibatalkan dan tidak dapat dibayar kembali.');
        }

        if ($booking->isSubmitted()) {
            return redirect()
                ->route('bookings.index')
                ->with('error', 'Pemesanan masih menunggu konfirmasi admin. Pembayaran baru bisa dilakukan setelah pemesanan dikonfirmasi.');
        }

        if ($booking->isConfirmedAwaitingPayment() && ! $booking->hasPaymentWindowStarted()) {
            $booking->update(['payment_started_at' => now()]);
            $booking->refresh();
        }

        if ($booking->isPaymentWindowExpired()) {
            $booking->update(['status' => Booking::STATUS_CANCELLED]);
            $booking->payments()
                ->where('status', Payment::STATUS_PENDING)
                ->update([
                    'status' => Payment::STATUS_EXPIRED,
                    'transaction_status' => 'payment_window_expired',
                    'paid_at' => null,
                ]);

            return redirect()
                ->route('bookings.index')
                ->with('error', 'Waktu pembayaran 30 menit sudah habis. Pemesanan dibatalkan otomatis, silakan pesan ulang.');
        }

        return view('client.booking.pay', compact('booking'));
    }

    /**
     * Mengubah data add-on paket ke format yang stabil untuk validasi dan perhitungan.
     *
     * @return array<string, array{label: string, price: int, unit: string}>
     */
    protected function normalizePackageAddons(ServicePackage $package): array
    {
        $addons = is_array($package->addons) ? $package->addons : [];
        $normalized = [];

        foreach ($addons as $item) {
            if (is_array($item)) {
                $label = trim((string) ($item['label'] ?? ''));
                $price = (int) ($item['price'] ?? 0);
                if ($label === '') {
                    continue;
                }
                $key = $label.'|'.$price;
                $normalized[md5($key)] = [
                    'label' => $label,
                    'price' => max(0, $price),
                    'unit' => trim((string) ($item['unit'] ?? '')),
                ];
                continue;
            }

            if (is_string($item)) {
                $raw = trim($item);
                if ($raw === '') {
                    continue;
                }

                [$label, $price] = $this->parseAddonLabelAndPrice($raw);
                $normalized[md5($raw)] = [
                    'label' => $label,
                    'price' => $price,
                    'unit' => '',
                ];
            }
        }

        return $normalized;
    }

    protected function chosenAddonsFromRequest(ServicePackage $package, array $validated): array
    {
        $addonMap = $this->normalizePackageAddons($package);
        $selectedAddonKeys = collect($validated['selected_addons'] ?? []);
        $addonQuantities = collect($validated['addon_quantities'] ?? []);

        return $selectedAddonKeys
            ->filter(fn ($key) => isset($addonMap[$key]))
            ->map(function ($key) use ($addonMap, $addonQuantities) {
                $addon = $addonMap[$key];
                $quantity = max(1, (int) $addonQuantities->get($key, 1));
                $price = (int) ($addon['price'] ?? 0);

                return [
                    'label' => $addon['label'],
                    'price' => $price,
                    'unit' => $addon['unit'] ?? '',
                    'quantity' => $quantity,
                    'subtotal' => $price * $quantity,
                ];
            })
            ->values()
            ->all();
    }

    /**
     * Format add-on yang masih diterima:
     * - "Nama Addon|50000"
     * - "Nama Addon:50000"
     * - "Nama Addon - 50000"
     */
    protected function parseAddonLabelAndPrice(string $raw): array
    {
        if (preg_match('/^(.*?)\s*(?:\||:|-)\s*([0-9][0-9\.,]*)$/', $raw, $matches)) {
            $label = trim($matches[1]) !== '' ? trim($matches[1]) : $raw;
            $price = (int) preg_replace('/[^0-9]/', '', $matches[2]);
            return [$label, $price];
        }

        // Format bebas, contoh:
        // "Tambah orang Rp50k", "Tambah waktu Rp100k/10m", "Ganti kostum 50rb"
        if (preg_match('/(rp)?\s*([0-9][0-9\.,]*)\s*(k|rb|ribu)?/i', $raw, $matches)) {
            $nominal = (float) str_replace([',', '.'], '', $matches[2]);
            $suffix = strtolower($matches[3] ?? '');

            if (in_array($suffix, ['k', 'rb', 'ribu'], true)) {
                $nominal *= 1000;
            }

            $price = (int) $nominal;
            if ($price > 0) {
                $token = trim($matches[0]);
                $label = trim(str_ireplace($token, '', $raw));
                $label = $label !== '' ? trim(preg_replace('/[\-:|]+$/', '', $label)) : $raw;
                return [$label, $price];
            }
        }

        return [$raw, 0];
    }

    protected function syncBookingAddons(Booking $booking, array $chosenAddons): void
    {
        $booking->update([
            'selected_addons' => array_values(array_map(function ($addon) {
                $price = max(0, (int) ($addon['price'] ?? 0));
                $quantity = max(1, (int) ($addon['quantity'] ?? 1));

                return [
                    'label' => trim((string) ($addon['label'] ?? '')),
                    'price' => $price,
                    'unit' => trim((string) ($addon['unit'] ?? '')),
                    'quantity' => $quantity,
                    'subtotal' => $price * $quantity,
                ];
            }, $chosenAddons)),
        ]);
    }
}
