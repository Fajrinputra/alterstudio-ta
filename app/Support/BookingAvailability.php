<?php

namespace App\Support;

use App\Models\Booking;
use App\Models\ServicePackage;
use App\Models\StudioHoliday;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class BookingAvailability
{
    /** Menentukan apakah studio tutup pada tanggal tertentu. */
    public function isClosedDate(Carbon $date, ?int $locationId = null): bool
    {
        if (in_array($date->dayOfWeek, config('studio.closed_weekdays', []), true)) {
            return true;
        }

        return $this->holidayDates($locationId)->contains($date->toDateString());
    }

    /** Mengembalikan alasan penutupan tanggal agar mudah ditampilkan ke UI. */
    public function closedReason(Carbon $date, ?int $locationId = null): ?string
    {
        if (in_array($date->dayOfWeek, config('studio.closed_weekdays', []), true)) {
            return 'Studio tutup pada akhir pekan. Silakan pilih hari kerja.';
        }

        if ($this->holidayDates($locationId)->contains($date->toDateString())) {
            return 'Tanggal yang dipilih termasuk hari libur studio. Silakan pilih tanggal lain.';
        }

        return null;
    }

    /** Menghasilkan daftar slot jam yang masih tersedia untuk tanggal dan cabang tertentu. */
    public function availableSlots(ServicePackage $package, int $locationId, Carbon $date): array
    {
        if ($this->isClosedDate($date, $locationId)) {
            return [];
        }

        [$openAt, $closeAt] = $this->operationalWindow($date);
        $duration = max(1, (int) ($package->duration_minutes ?? 60));
        $slotInterval = max(15, (int) config('studio.slot_interval_minutes', 60));
        $lastStart = $closeAt->copy()->subMinutes($duration);

        if ($lastStart->lt($openAt)) {
            return [];
        }

        $bookedRanges = $this->bookedRanges($locationId, $date);
        $slots = [];

        for ($cursor = $openAt->copy(); $cursor->lte($lastStart); $cursor->addMinutes($slotInterval)) {
            $candidateEnd = $cursor->copy()->addMinutes($duration);

            $overlap = $bookedRanges->contains(function (array $range) use ($cursor, $candidateEnd) {
                return $cursor->lt($range['end']) && $candidateEnd->gt($range['start']);
            });

            if (! $overlap) {
                $slots[] = [
                    'value' => $cursor->format('H:i'),
                    'label' => $cursor->format('H:i').' - '.$candidateEnd->format('H:i'),
                ];
            }
        }

        return $slots;
    }

    /** Memastikan slot yang dipilih klien benar-benar masih tersedia. */
    public function isSlotAvailable(ServicePackage $package, int $locationId, Carbon $date, string $time): bool
    {
        return collect($this->availableSlots($package, $locationId, $date))
            ->contains(fn (array $slot) => $slot['value'] === $time);
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
        return Booking::query()
            ->with('package:id,duration_minutes')
            ->where('studio_location_id', $locationId)
            ->where('status', '!=', Booking::STATUS_CANCELLED)
            ->whereDate('booking_date', $date->toDateString())
            ->get()
            ->map(function (Booking $booking) use ($date) {
                $duration = max(1, (int) ($booking->package?->duration_minutes ?? 60));
                $start = Carbon::parse($date->toDateString().' '.($booking->booking_time ?? '00:00'));

                return [
                    'start' => $start,
                    'end' => $start->copy()->addMinutes($duration),
                ];
            });
    }

    protected function holidayDates(?int $locationId = null): Collection
    {
        $configDates = collect(config('studio.holidays', []))
            ->filter()
            ->values();

        $dbDates = StudioHoliday::query()
            ->where('is_active', true)
            ->when($locationId, fn ($query) => $query->where('studio_location_id', $locationId))
            ->pluck('holiday_date')
            ->map(fn ($date) => Carbon::parse($date)->toDateString());

        return $configDates
            ->merge($dbDates)
            ->unique()
            ->values();
    }
}
