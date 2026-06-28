<?php

namespace App\Support;

use App\Models\Booking;
use App\Models\ServicePackage;
use App\Models\StudioRoom;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class BookingAvailability
{
    /** Menentukan apakah studio tutup pada tanggal tertentu. */
    public function isClosedDate(Carbon $date, ?int $locationId = null): bool
    {
        return in_array($date->dayOfWeek, config('studio.closed_weekdays', []), true);
    }

    /** Mengembalikan alasan penutupan tanggal agar mudah ditampilkan ke UI. */
    public function closedReason(Carbon $date, ?int $locationId = null): ?string
    {
        if ($this->isClosedDate($date, $locationId)) {
            return 'Studio tutup pada akhir pekan. Silakan pilih hari kerja.';
        }

        return null;
    }

    /** Menghasilkan daftar slot jam yang masih tersedia untuk tanggal dan cabang tertentu. */
    public function availableSlots(ServicePackage $package, int $locationId, Carbon $date, int $extraDurationMinutes = 0): array
    {
        if ($this->isClosedDate($date, $locationId)) {
            return [];
        }

        [$openAt, $closeAt] = $this->operationalWindow($date);
        $duration = $this->durationMinutes($package, $extraDurationMinutes);
        $buffer = $this->bufferMinutes();
        $slotInterval = $this->slotIntervalMinutes();
        $lastStart = $closeAt->copy()->subMinutes($duration);

        if ($lastStart->lt($openAt)) {
            return [];
        }

        $roomIds = $this->activeRoomIds($locationId);
        if ($roomIds->isEmpty()) {
            return [];
        }

        $bookedRanges = $this->bookedRanges($locationId, $date);
        $slots = [];
        $now = Carbon::now();
        $isToday = $date->isSameDay($now);

        for ($cursor = $openAt->copy(); $cursor->lte($lastStart); $cursor->addMinutes($slotInterval)) {
            if ($isToday && $cursor->lte($now)) {
                continue;
            }

            $candidateEnd = $cursor->copy()->addMinutes($duration);
            $candidateBlockedEnd = $candidateEnd->copy()->addMinutes($buffer);
            $availableRoomId = $this->firstAvailableRoomId($roomIds, $bookedRanges, $cursor, $candidateBlockedEnd);

            if ($availableRoomId !== null) {
                $slots[] = [
                    'value' => $cursor->format('H:i'),
                    'label' => $cursor->format('H:i').' - '.$candidateEnd->format('H:i'),
                    'duration_minutes' => $duration,
                    'buffer_minutes' => $buffer,
                ];
            }
        }

        return $slots;
    }

    /** Memastikan slot yang dipilih klien benar-benar masih tersedia. */
    public function isSlotAvailable(ServicePackage $package, int $locationId, Carbon $date, string $time, int $extraDurationMinutes = 0): bool
    {
        return $this->availableRoomForSlot($package, $locationId, $date, $time, $extraDurationMinutes) !== null;
    }

    /** Mengambil ruangan aktif pertama yang bisa menampung slot tertentu. */
    public function availableRoomForSlot(ServicePackage $package, int $locationId, Carbon $date, string $time, int $extraDurationMinutes = 0): ?StudioRoom
    {
        if ($this->isClosedDate($date, $locationId)) {
            return null;
        }

        [$openAt, $closeAt] = $this->operationalWindow($date);
        $start = Carbon::parse($date->toDateString().' '.$time);
        $duration = $this->durationMinutes($package, $extraDurationMinutes);
        $buffer = $this->bufferMinutes();
        $end = $start->copy()->addMinutes($duration);

        if ($start->lt($openAt) || $end->gt($closeAt)) {
            return null;
        }

        if (! $this->isAlignedToSlotGrid($start, $openAt)) {
            return null;
        }

        if ($date->isSameDay(Carbon::now()) && $start->lte(Carbon::now())) {
            return null;
        }

        $roomIds = $this->activeRoomIds($locationId);
        if ($roomIds->isEmpty()) {
            return null;
        }

        $roomId = $this->firstAvailableRoomId(
            $roomIds,
            $this->bookedRanges($locationId, $date),
            $start,
            $end->copy()->addMinutes($buffer)
        );

        return $roomId ? StudioRoom::find($roomId) : null;
    }

    /**
     * Mengembalikan jam buka dan jam tutup studio pada tanggal tertentu.
     *
     * @return array{0: Carbon, 1: Carbon}
     */
    public function operationalWindow(Carbon $date): array
    {
        $openTime = (string) config('studio.open_time', '11:00');
        $closeTime = (string) config('studio.close_time', '22:00');

        return [
            Carbon::parse($date->toDateString().' '.$openTime),
            Carbon::parse($date->toDateString().' '.$closeTime),
        ];
    }

    protected function bookedRanges(int $locationId, Carbon $date): Collection
    {
        $buffer = $this->bufferMinutes();

        // Booking aktif pada tanggal yang sama dianggap menahan ruangan sampai buffer selesai.
        return Booking::query()
            ->with('package:id,duration_minutes')
            ->where('studio_location_id', $locationId)
            ->where('status', '!=', Booking::STATUS_CANCELLED)
            ->whereDate('booking_date', $date->toDateString())
            ->get()
            ->map(function (Booking $booking) use ($date, $buffer) {
                $duration = $booking->effectiveDurationMinutes();
                $start = Carbon::parse($date->toDateString().' '.($booking->booking_time ?? '00:00'));
                $end = $start->copy()->addMinutes($duration);

                return [
                    'room_id' => $booking->studio_room_id ? (int) $booking->studio_room_id : null,
                    'start' => $start,
                    'end' => $end,
                    'blocked_end' => $end->copy()->addMinutes($buffer),
                ];
            });
    }

    protected function firstAvailableRoomId(Collection $roomIds, Collection $bookedRanges, Carbon $start, Carbon $blockedEnd): ?int
    {
        // Slot valid jika masih ada minimal satu ruangan aktif yang tidak bentrok.
        $conflicts = $bookedRanges->filter(function (array $range) use ($start, $blockedEnd) {
            return $start->lt($range['blocked_end']) && $blockedEnd->gt($range['start']);
        });

        $blockedRoomIds = $conflicts
            ->pluck('room_id')
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();

        $unassignedConflictCount = $conflicts
            ->filter(fn (array $range) => empty($range['room_id']))
            ->count();

        $availableRoomIds = $roomIds
            ->reject(fn (int $roomId) => $blockedRoomIds->contains($roomId))
            ->values();

        return $availableRoomIds->slice($unassignedConflictCount)->first();
    }

    protected function activeRoomIds(int $locationId): Collection
    {
        // Urutan id dipakai agar pilihan ruangan konsisten dari request ke request.
        return StudioRoom::query()
            ->where('studio_location_id', $locationId)
            ->where('is_active', true)
            ->orderBy('id')
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->values();
    }

    protected function durationMinutes(ServicePackage $package, int $extraDurationMinutes = 0): int
    {
        return max(1, (int) ($package->duration_minutes ?? 60)) + max(0, $extraDurationMinutes);
    }

    public function bufferMinutes(): int
    {
        return max(0, (int) config('studio.booking_buffer_minutes', 15));
    }

    protected function slotIntervalMinutes(): int
    {
        return max(5, (int) config('studio.slot_interval_minutes', 15));
    }

    protected function isAlignedToSlotGrid(Carbon $start, Carbon $openAt): bool
    {
        // Menolak jam manual yang tidak mengikuti interval slot sistem.
        $minutesFromOpen = (int) $openAt->diffInMinutes($start, false);

        return $minutesFromOpen >= 0 && $minutesFromOpen % $this->slotIntervalMinutes() === 0;
    }
}
