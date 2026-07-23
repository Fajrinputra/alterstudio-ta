<?php

namespace App\Support;

use App\Models\Booking;
use App\Models\ServicePackage;
use App\Models\StudioRoom;
use Carbon\Carbon;
use Illuminate\Support\Collection;

/**
 * Mengelola pengecekan ketersediaan slot booking per cabang studio.
 *
 * Setelah migrasi 2026_07_22_100000, semua lookup lokasi/ruangan
 * memakai varchar code (studio_location_code, studio_room_code / room_code)
 * bukan integer id lagi.
 */
class BookingAvailability
{
    /** Menentukan apakah studio tutup pada tanggal tertentu. */
    public function isClosedDate(Carbon $date, ?string $locationCode = null): bool
    {
        return in_array($date->dayOfWeek, config('studio.closed_weekdays', []), true);
    }

    /** Mengembalikan alasan penutupan tanggal agar mudah ditampilkan ke UI. */
    public function closedReason(Carbon $date, ?string $locationCode = null): ?string
    {
        if ($this->isClosedDate($date, $locationCode)) {
            return 'Studio tutup pada akhir pekan. Silakan pilih hari kerja.';
        }

        return null;
    }

    /** Menghasilkan daftar slot jam yang masih tersedia untuk tanggal dan cabang tertentu. */
    public function availableSlots(ServicePackage $package, string $locationCode, Carbon $date, int $extraDurationMinutes = 0, ?int $ignoreBookingId = null): array
    {
        if ($this->isClosedDate($date, $locationCode)) {
            return [];
        }

        [$openAt, $closeAt] = $this->operationalWindow($date);
        $duration   = $this->durationMinutes($package, $extraDurationMinutes);
        $buffer     = $this->bufferMinutes();
        $slotInterval = $this->slotIntervalMinutes();
        $lastStart  = $closeAt->copy()->subMinutes($duration);

        if ($lastStart->lt($openAt)) {
            return [];
        }

        $roomCodes = $this->activeRoomCodes($locationCode);
        if ($roomCodes->isEmpty()) {
            return [];
        }

        $bookedRanges = $this->bookedRanges($locationCode, $date, $ignoreBookingId);
        $slots = [];
        $now = Carbon::now();
        $isToday = $date->isSameDay($now);

        for ($cursor = $openAt->copy(); $cursor->lte($lastStart); $cursor->addMinutes($slotInterval)) {
            if ($isToday && $cursor->lte($now)) {
                continue;
            }

            $candidateEnd        = $cursor->copy()->addMinutes($duration);
            $candidateBlockedEnd = $candidateEnd->copy()->addMinutes($buffer);
            $availableRoomCode   = $this->firstAvailableRoomCode($roomCodes, $bookedRanges, $cursor, $candidateBlockedEnd);

            if ($availableRoomCode !== null) {
                $slots[] = [
                    'value'           => $cursor->format('H:i'),
                    'label'           => $cursor->format('H:i').' - '.$candidateEnd->format('H:i'),
                    'duration_minutes' => $duration,
                    'buffer_minutes'  => $buffer,
                ];
            }
        }

        return $slots;
    }

    /** Memastikan slot yang dipilih klien benar-benar masih tersedia. */
    public function isSlotAvailable(ServicePackage $package, string $locationCode, Carbon $date, string $time, int $extraDurationMinutes = 0, ?int $ignoreBookingId = null): bool
    {
        return $this->availableRoomForSlot($package, $locationCode, $date, $time, $extraDurationMinutes, $ignoreBookingId) !== null;
    }

    /** Mengambil ruangan aktif pertama yang bisa menampung slot tertentu. */
    public function availableRoomForSlot(ServicePackage $package, string $locationCode, Carbon $date, string $time, int $extraDurationMinutes = 0, ?int $ignoreBookingId = null): ?StudioRoom
    {
        if ($this->isClosedDate($date, $locationCode)) {
            return null;
        }

        [$openAt, $closeAt] = $this->operationalWindow($date);
        $start    = Carbon::parse($date->toDateString().' '.$time);
        $duration = $this->durationMinutes($package, $extraDurationMinutes);
        $buffer   = $this->bufferMinutes();
        $end      = $start->copy()->addMinutes($duration);

        if ($start->lt($openAt) || $end->gt($closeAt)) {
            return null;
        }

        if (! $this->isAlignedToSlotGrid($start, $openAt)) {
            return null;
        }

        if ($date->isSameDay(Carbon::now()) && $start->lte(Carbon::now())) {
            return null;
        }

        $roomCodes = $this->activeRoomCodes($locationCode);
        if ($roomCodes->isEmpty()) {
            return null;
        }

        $roomCode = $this->firstAvailableRoomCode(
            $roomCodes,
            $this->bookedRanges($locationCode, $date, $ignoreBookingId),
            $start,
            $end->copy()->addMinutes($buffer)
        );

        // find() menggunakan primaryKey ('room_code'), jadi ini langsung mencari berdasarkan room_code
        return $roomCode ? StudioRoom::find($roomCode) : null;
    }

    /**
     * Mengembalikan jam buka dan jam tutup studio pada tanggal tertentu.
     *
     * @return array{0: Carbon, 1: Carbon}
     */
    public function operationalWindow(Carbon $date): array
    {
        $openTime  = (string) config('studio.open_time', '11:00');
        $closeTime = (string) config('studio.close_time', '22:00');

        return [
            Carbon::parse($date->toDateString().' '.$openTime),
            Carbon::parse($date->toDateString().' '.$closeTime),
        ];
    }

    protected function bookedRanges(string $locationCode, Carbon $date, ?int $ignoreBookingId = null): Collection
    {
        $buffer = $this->bufferMinutes();

        return Booking::query()
            ->with('package:id,duration_minutes')
            ->where('studio_location_code', $locationCode)
            ->where('status', '!=', Booking::STATUS_CANCELLED)
            ->whereDate('booking_date', $date->toDateString())
            ->when($ignoreBookingId, fn ($query) => $query->whereKeyNot($ignoreBookingId))
            ->get()
            ->map(function (Booking $booking) use ($date, $buffer) {
                $duration = $booking->effectiveDurationMinutes();
                $start    = Carbon::parse($date->toDateString().' '.($booking->booking_time ?? '00:00'));
                $end      = $start->copy()->addMinutes($duration);

                return [
                    'room_code'   => $booking->studio_room_code,
                    'start'       => $start,
                    'end'         => $end,
                    'blocked_end' => $end->copy()->addMinutes($buffer),
                ];
            });
    }

    /**
     * Mengambil kode ruangan aktif di cabang tertentu.
     * Urutan room_code dipakai agar pilihan ruangan konsisten.
     */
    protected function activeRoomCodes(string $locationCode): Collection
    {
        return StudioRoom::query()
            ->where('studio_location_code', $locationCode)
            ->where('is_active', true)
            ->orderBy('room_code')
            ->pluck('room_code')
            ->values();
    }

    protected function firstAvailableRoomCode(Collection $roomCodes, Collection $bookedRanges, Carbon $start, Carbon $blockedEnd): ?string
    {
        $conflicts = $bookedRanges->filter(function (array $range) use ($start, $blockedEnd) {
            return $start->lt($range['blocked_end']) && $blockedEnd->gt($range['start']);
        });

        $blockedCodes = $conflicts
            ->pluck('room_code')
            ->filter()
            ->unique()
            ->values();

        $unassignedConflictCount = $conflicts
            ->filter(fn (array $range) => empty($range['room_code']))
            ->count();

        $availableCodes = $roomCodes
            ->reject(fn (string $code) => $blockedCodes->contains($code))
            ->values();

        return $availableCodes->slice($unassignedConflictCount)->first();
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
        $minutesFromOpen = (int) $openAt->diffInMinutes($start, false);

        return $minutesFromOpen >= 0 && $minutesFromOpen % $this->slotIntervalMinutes() === 0;
    }
}
