<?php

namespace App\Console\Commands;

use App\Enums\Role;
use App\Models\Booking;
use App\Models\Project;
use App\Models\User;
use App\Notifications\InactiveClientAccountDeletedNotification;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CleanupInactiveClients extends Command
{
    protected $signature = 'clients:cleanup-inactive {--dry-run : Hitung akun tanpa menghapus data}';
    protected $description = 'Hapus/anonymize akun klien yang tidak memiliki transaksi selama 6 bulan';

    public function handle(): int
    {
        $cutoff = now()->subMonths(6);
        $dryRun = (bool) $this->option('dry-run');

        $clients = User::query()
            ->where('role', Role::CLIENT)
            ->where('created_at', '<=', $cutoff)
            ->whereDoesntHave('bookings', fn ($query) => $query->where('created_at', '>=', $cutoff))
            ->get();

        $processed = 0;

        foreach ($clients as $client) {
            if ($this->hasActiveOperationalWork($client)) {
                continue;
            }

            $processed++;

            if ($dryRun) {
                continue;
            }

            $client->notifyNow(new InactiveClientAccountDeletedNotification($cutoff->format('d/m/Y')));

            if ($this->hasPersistentReferences($client)) {
                $this->anonymizeUser($client);
            } else {
                $this->deleteUserPermanently($client);
            }
        }

        $this->info("Akun klien tidak aktif yang diproses: {$processed}");

        return self::SUCCESS;
    }

    protected function hasActiveOperationalWork(User $user): bool
    {
        $hasActiveClientBookings = $user->bookings()
            ->where('status', '!=', Booking::STATUS_CANCELLED)
            ->where(function ($query) {
                $query->whereDoesntHave('project')
                    ->orWhereHas('project', fn ($project) => $project->where('status', '!=', Project::STATUS_FINAL));
            })
            ->exists();

        if ($hasActiveClientBookings) {
            return true;
        }

        return Project::query()
            ->where(function ($query) use ($user) {
                $query->where('photographer_id', $user->id)
                    ->orWhere('editor_id', $user->id);
            })
            ->whereHas('booking', fn ($booking) => $booking->where('status', '!=', Booking::STATUS_CANCELLED))
            ->where('status', '!=', Project::STATUS_FINAL)
            ->exists();
    }

    protected function hasPersistentReferences(User $user): bool
    {
        return $user->bookings()->exists()
            || $user->photoSelections()->exists()
            || $user->uploadedMediaAssets()->exists()
            || Project::query()
                ->where(function ($query) use ($user) {
                    $query->where('photographer_id', $user->id)
                        ->orWhere('editor_id', $user->id)
                        ->orWhere('raw_drive_uploaded_by', $user->id)
                        ->orWhere('final_drive_uploaded_by', $user->id);
                })
                ->exists();
    }

    protected function anonymizeUser(User $user): void
    {
        $this->deleteResetTokensAndAvatar($user);

        $user->forceFill([
            'name' => 'Akun Dihapus Otomatis',
            'email' => 'inactive-client-'.$user->id.'-'.Str::lower(Str::random(8)).'@alter.local',
            'no_hp' => null,
            'avatar_path' => null,
            'password' => Hash::make(Str::random(40)),
            'remember_token' => null,
            'email_verified_at' => null,
            'is_active' => false,
        ])->save();
    }

    protected function deleteUserPermanently(User $user): void
    {
        $this->deleteResetTokensAndAvatar($user);
        $user->delete();
    }

    protected function deleteResetTokensAndAvatar(User $user): void
    {
        DB::table('password_reset_tokens')
            ->where('email', $user->email)
            ->orWhere('user_id', $user->id)
            ->delete();

        if ($user->avatar_path) {
            Storage::disk('public')->delete($user->avatar_path);
        }
    }
}
