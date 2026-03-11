<?php

namespace App\Http\Controllers;

use App\Enums\Role;
use App\Models\Booking;
use App\Models\Project;
use App\Models\ServicePackage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

/**
 * Alur pemesanan client + monitoring status booking admin/manager.
 */
class BookingController extends Controller
{
    /** Menangani list booking; client hanya miliknya, admin/manager bisa filter status. */
    public function index(Request $request)
    {
        $user = $request->user();

        $query = Booking::with([
            'package',
            'project.mediaAssets',
            'project.selections.mediaAsset',
            'project.schedule',
            'client',
        ]);

        if ($user->role === Role::CLIENT) {
            $query->where('client_id', $user->id);
        } else {
            // admin / manager filters
            if ($request->filled('status')) {
                $query->where('status', $request->get('status'));
            }

            if ($request->filled('schedule_status')) {
                $hasSchedule = $request->get('schedule_status') === 'scheduled';
                $query->whereHas('project', function ($q) use ($hasSchedule) {
                    $hasSchedule
                        ? $q->whereHas('schedule')
                        : $q->whereDoesntHave('schedule');
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

        $statuses = ['WAITING_PAYMENT','DP_PAID','PAID','CANCELLED'];
        $clients = \App\Models\User::where('role', Role::CLIENT)->orderBy('name')->get();
        $packages = ServicePackage::orderBy('name')->get();
        return view('admin.booking.index', compact('bookings','statuses','clients','packages'));
    }

    /** Form booking untuk client. */
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
        return view('client.booking.create', compact('packages', 'locations', 'selectedPackage', 'addonOptions'));
    }

    /** Membuat booking baru, set status WAITING_PAYMENT dan otomatis buat project DRAFT. */
    public function store(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'package_id' => ['required', Rule::exists(ServicePackage::class, 'id')],
            'booking_date' => ['required', 'date'],
            'booking_time' => ['required', 'date_format:H:i', 'after_or_equal:11:00', 'before_or_equal:22:00'],
            'studio_location_id' => ['required', 'exists:studio_locations,id'],
            'notes' => ['nullable', 'string'],
            'payment_type' => ['required', 'in:DP,FULL'],
            'selected_addons' => ['nullable', 'array'],
            'selected_addons.*' => ['string'],
        ]);

        $package = ServicePackage::findOrFail($validated['package_id']);
        $locationModel = \App\Models\StudioLocation::findOrFail($validated['studio_location_id']);
        $addonMap = $this->normalizePackageAddons($package);
        $selectedAddonKeys = collect($validated['selected_addons'] ?? []);
        $chosenAddons = $selectedAddonKeys
            ->filter(fn ($key) => isset($addonMap[$key]))
            ->map(fn ($key) => $addonMap[$key])
            ->values()
            ->all();
        $addonTotal = (int) collect($chosenAddons)->sum('price');
        $totalPrice = (int) $package->price + $addonTotal;

        $booking = Booking::create([
            'client_id' => $user->id,
            'package_id' => $package->id,
            'studio_location_id' => $validated['studio_location_id'],
            'booking_date' => $validated['booking_date'],
            'booking_time' => $validated['booking_time'],
            'location' => $locationModel->address ?? $locationModel->name,
            'notes' => $validated['notes'] ?? null,
            'status' => 'WAITING_PAYMENT',
            'payment_type' => $validated['payment_type'],
            'selected_addons' => $chosenAddons,
            'addon_total' => $addonTotal,
            'total_price' => $totalPrice,
        ]);

        Project::create([
            'booking_id' => $booking->id,
            'status' => 'DRAFT',
        ]);

        // Notifikasi email
        $admins = \App\Models\User::whereIn('role', [Role::ADMIN, Role::MANAGER])->get();
        $notification = new \App\Notifications\BookingCreatedNotification($booking->load('package','client'));
        $user->notify($notification); // client
        \Illuminate\Support\Facades\Notification::send($admins, $notification);

        if ($request->wantsJson()) {
            return response()->json($booking->load(['package', 'project']), 201);
        }

        return redirect()->route('bookings.pay', $booking);
    }

    /** Detail booking; client hanya bisa lihat miliknya. */
    public function show(Booking $booking)
    {
        $user = Auth::user();

        if ($user->role === Role::CLIENT && $booking->client_id !== $user->id) {
            abort(403);
        }

        return response()->json($booking->load(['package', 'project', 'payments']));
    }

    /** Admin/Manager ubah status pembayaran (DP_PAID/PAID/CANCELLED). */
    public function updateStatus(Request $request, Booking $booking)
    {
        $request->validate([
            'status' => ['required', 'in:DP_PAID,PAID,CANCELLED'],
        ]);

        $booking->update(['status' => $request->status]);

        return back()->with('success', 'Status pembayaran diperbarui.');
    }

    /** Halaman bayar booking (client). */
    public function pay(Booking $booking)
    {
        $user = Auth::user();
        if ($user->role === Role::CLIENT && $booking->client_id !== $user->id) {
            abort(403);
        }
        return view('client.booking.pay', compact('booking'));
    }

    /**
     * Ubah addons paket ke map stabil untuk validasi + kalkulasi.
     *
     * @return array<string, array{label: string, price: int}>
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
                ];
            }
        }

        return $normalized;
    }

    /**
     * Format didukung:
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
}
