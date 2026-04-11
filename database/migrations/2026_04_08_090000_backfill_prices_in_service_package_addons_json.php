<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('service_packages')
            ->select('id', 'addons')
            ->orderBy('id')
            ->eachById(function ($package) {
                $addons = json_decode((string) $package->addons, true);

                if (! is_array($addons)) {
                    return;
                }

                $updated = array_map(function ($addon) {
                    if (! is_array($addon)) {
                        return $addon;
                    }

                    $label = trim((string) ($addon['label'] ?? ''));
                    $price = (int) ($addon['price'] ?? 0);

                    if ($price > 0 || $label === '') {
                        return $addon;
                    }

                    [$cleanLabel, $parsedPrice] = $this->parseAddonLabelAndPrice($label);

                    return [
                        'label' => $cleanLabel,
                        'price' => $parsedPrice,
                        'is_active' => (bool) ($addon['is_active'] ?? true),
                    ];
                }, $addons);

                DB::table('service_packages')
                    ->where('id', $package->id)
                    ->update([
                        'addons' => json_encode($updated, JSON_UNESCAPED_UNICODE),
                    ]);
            }, 'id');
    }

    public function down(): void
    {
        // Data correction only; no rollback needed.
    }

    private function parseAddonLabelAndPrice(string $raw): array
    {
        $raw = trim($raw);

        if ($raw === '') {
            return ['', 0];
        }

        if (preg_match('/(rp)?\s*([0-9][0-9\.,]*)\s*(k|rb|ribu)?/i', $raw, $matches, PREG_OFFSET_CAPTURE)) {
            $fullMatch = $matches[0][0] ?? '';
            $offset = $matches[0][1] ?? null;
            $nominalRaw = $matches[2][0] ?? '0';
            $suffix = strtolower($matches[3][0] ?? '');

            $nominal = (float) preg_replace('/[^0-9]/', '', $nominalRaw);
            if (in_array($suffix, ['k', 'rb', 'ribu'], true)) {
                $nominal *= 1000;
            }

            $price = (int) $nominal;
            $label = $raw;

            if ($offset !== null) {
                $label = trim(substr($raw, 0, $offset).substr($raw, $offset + strlen($fullMatch)));
            }

            $label = trim(preg_replace('/[\-|:,\/]+$/', '', $label));
            $label = preg_replace('/\s+/', ' ', $label ?? '');

            return [$label !== '' ? $label : $raw, $price];
        }

        return [$raw, 0];
    }
};
