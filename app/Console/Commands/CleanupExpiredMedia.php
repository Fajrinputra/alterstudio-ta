<?php

namespace App\Console\Commands;

use App\Models\MediaAsset;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

/**
 * Command housekeeping untuk membersihkan file media kedaluwarsa.
 */
class CleanupExpiredMedia extends Command
{
    protected $signature = 'media:cleanup-expired';
    protected $description = 'Hapus file media yang sudah melewati masa simpan (expires_at)';

    public function handle(): int
    {
        // Ambil semua asset yang sudah melewati expires_at.
        $expired = MediaAsset::whereNotNull('expires_at')
            ->where('expires_at', '<', now())
            ->get();

        $count = 0;
        foreach ($expired as $asset) {
            // Hapus file fisik lebih dulu jika masih ada di disk.
            if ($asset->path && Storage::disk('public')->exists($asset->path)) {
                Storage::disk('public')->delete($asset->path);
            }
            // Hapus metadata dari database.
            $asset->delete();
            $count++;
        }

        $this->info("Expired media deleted: {$count}");
        return self::SUCCESS;
    }
}
