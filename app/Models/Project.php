<?php

namespace App\Models;

use App\Enums\Role;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Carbon;

/**
 * Model Project — Workflow Pasca-Booking Produksi Foto.
 *
 * Setiap booking yang dikonfirmasi akan memiliki satu Project yang mencatat
 * seluruh alur produksi dari penjadwalan hingga pengiriman hasil final.
 *
 * Alur status project:
 *   DRAFT     → Belum dijadwalkan (default setelah booking dikonfirmasi)
 *   SCHEDULED → Admin sudah menetapkan jadwal, fotografer, dan editor
 *   SHOOT_DONE→ Fotografer sudah upload link Drive foto mentah
 *   EDITING   → Klien sudah memilih foto dan mengirim permintaan edit
 *   FINAL     → Editor sudah upload link Drive hasil final
 *
 * Relasi penting:
 * - booking      : booking yang memicu pembuatan project ini
 * - scheduleRecord: record jadwal dengan fotografer + editor yang ditugaskan
 * - mediaAssets  : file lama (dipertahankan untuk kompatibilitas data historis)
 * - selections   : pilihan foto lama (dipertahankan untuk kompatibilitas)
 *
 * Catatan arsitektur:
 * - photographer_id dan editor_id TIDAK lagi disimpan langsung di tabel projects.
 *   Keduanya kini ada di tabel project_schedules melalui relasi scheduleRecord.
 * - Mutator setPhotographerIdAttribute() dan setEditorIdAttribute() dipertahankan
 *   agar kode lama (sebelum refaktor) tetap bisa berjalan tanpa modifikasi.
 */
class Project extends Model
{
    use HasFactory;

    public const DRIVE_ACCESS_DAYS = 3;

    protected ?int $pendingPhotographerId = null;

    protected ?int $pendingEditorId = null;

    protected $fillable = [
        'booking_id',
        'status',
        'selections_locked',
        'photographer_id',
        'editor_id',
        'start_at',
        'end_at',
        'raw_drive_url',
        'raw_drive_uploaded_by',
        'raw_drive_uploaded_at',
        'edit_photo_codes',
        'edit_request_note',
        'edit_requested_at',
        'final_drive_url',
        'final_message',
        'final_drive_uploaded_by',
        'final_drive_uploaded_at',
    ];

    /**
     * Konstanta status workflow project.
     * Urutan harus diikuti secara berurutan; tidak bisa skip tahap.
     */
    public const STATUS_DRAFT     = 'DRAFT';      // Belum dijadwalkan.
    public const STATUS_SCHEDULED = 'SCHEDULED';  // Sudah dijadwalkan oleh admin.
    public const STATUS_SHOOT_DONE= 'SHOOT_DONE'; // Link foto mentah sudah tersedia.
    public const STATUS_EDITING   = 'EDITING';    // Klien sudah kirim permintaan edit.
    public const STATUS_FINAL     = 'FINAL';      // Hasil final sudah dikirim ke klien.

    /** Array semua status untuk validasi input dan iterasi UI. */
    public const STATUSES = [
        self::STATUS_DRAFT,
        self::STATUS_SCHEDULED,
        self::STATUS_SHOOT_DONE,
        self::STATUS_EDITING,
        self::STATUS_FINAL,
    ];

    /** Casts atribut untuk konversi otomatis tipe data saat akses/penyimpanan. */
    protected $casts = [
        'selections_locked' => 'boolean',
        'start_at' => 'datetime',
        'end_at' => 'datetime',
        'raw_drive_uploaded_at' => 'datetime',
        'edit_requested_at' => 'datetime',
        'final_drive_uploaded_at' => 'datetime',
    ];

    /**
     * Event observer yang berjalan setelah project disimpan (saved event).
     *
     * Tujuan: mengakomodasi kode lama yang mengisi photographer_id dan editor_id
     * langsung ke project. Setelah refaktor, data ini disimpan ke project_schedules,
     * bukan ke tabel projects. Hook ini menjembatani keduanya.
     *
     * Cara kerja:
     * 1. Jika pendingPhotographerId atau pendingEditorId diisi via mutator lama,
     *    hook ini akan membuat/memperbarui record di tabel project_schedules.
     * 2. Jika tidak ada pending data, hook ini tidak melakukan apa-apa.
     */
    protected static function booted(): void
    {
        static::saved(function (Project $project) {
            // Tidak ada pending data — lewati tanpa aksi.
            if (! $project->pendingPhotographerId && ! $project->pendingEditorId) {
                return;
            }

            // Pastikan semua data yang dibutuhkan untuk membuat jadwal sudah tersedia.
            if (! $project->start_at || ! $project->end_at || ! $project->booking) {
                return;
            }

            $existingSchedule = $project->scheduleRecord()->first();
            $photographerId = $project->pendingPhotographerId ?: $existingSchedule?->photographer_id;
            $editorId = $project->pendingEditorId ?: $existingSchedule?->editor_id;

            if (! $photographerId || ! $editorId) {
                return;
            }

            // Buat atau perbarui record jadwal dengan data terbaru.
            $project->scheduleRecord()->updateOrCreate(
                ['project_id' => $project->id],
                [
                    'booking_id'           => $project->booking_id,
                    'studio_location_code' => $project->booking->studio_location_code,
                    'studio_room_code'     => $project->booking->studio_room_code,
                    'scheduled_by'         => auth()->id()
                        ?? User::whereIn('role', [Role::ADMIN->value, Role::OWNER->value])->value('id')
                        ?? User::query()->value('id'),
                    'photographer_id'      => $photographerId,
                    'editor_id'            => $editorId,
                    'start_at'             => $project->start_at,
                    'end_at'               => $project->end_at,
                    'status'               => ProjectSchedule::STATUS_SCHEDULED,
                ]
            );

            // Reset relasi agar query berikutnya mengambil data terbaru dari DB.
            $project->unsetRelation('scheduleRecord');
        });
    }

    /**
     * Mutator photographer_id untuk kompatibilitas kode lama.
     * Nilai tidak langsung disimpan ke kolom (kolom tidak ada di tabel);
     * melainkan disimpan sementara di properti $pendingPhotographerId
     * dan kemudian diproses oleh booted() hook setelah model disimpan.
     */
    public function setPhotographerIdAttribute($value): void
    {
        $this->pendingPhotographerId = $value ? (int) $value : null;
    }

    /**
     * Mutator editor_id untuk kompatibilitas kode lama.
     * Sama seperti setPhotographerIdAttribute, nilai disimpan sementara
     * dan diproses oleh hook booted() setelah model disimpan.
     */
    public function setEditorIdAttribute($value): void
    {
        $this->pendingEditorId = $value ? (int) $value : null;
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    /** Metadata file lama, dipertahankan untuk kompatibilitas data historis. */
    public function mediaAssets(): HasMany
    {
        return $this->hasMany(MediaAsset::class);
    }

    /** Pilihan foto lama, dipertahankan untuk kompatibilitas data historis. */
    public function selections(): HasMany
    {
        return $this->hasMany(PhotoSelection::class);
    }

    public function scheduleRecord(): HasOne
    {
        return $this->hasOne(ProjectSchedule::class);
    }

    public function rawDriveUploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'raw_drive_uploaded_by');
    }

    public function finalDriveUploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'final_drive_uploaded_by');
    }

    public function getPhotographerIdAttribute(): ?int
    {
        return $this->scheduleRecord?->photographer_id;
    }

    public function getEditorIdAttribute(): ?int
    {
        return $this->scheduleRecord?->editor_id;
    }

    public function getPhotographerAttribute(): ?User
    {
        return $this->scheduleRecord?->photographer;
    }

    public function getEditorAttribute(): ?User
    {
        return $this->scheduleRecord?->editor;
    }

    /**
     * Akses kompatibilitas agar view lama tetap bisa memanggil $project->schedule.
     */
    public function getScheduleAttribute(): ?object
    {
        $scheduleRecord = $this->exists
            ? ($this->relationLoaded('scheduleRecord')
                ? $this->scheduleRecord
                : $this->scheduleRecord()->with(['photographer', 'editor'])->first())
            : null;

        if ($scheduleRecord) {
            return (object) [
                'id' => $scheduleRecord->id,
                'project_id' => $this->id,
                'photographer_id' => $scheduleRecord->photographer_id,
                'editor_id' => $scheduleRecord->editor_id,
                'start_at' => $scheduleRecord->start_at,
                'end_at' => $scheduleRecord->end_at,
                'photographer' => $scheduleRecord->photographer,
                'editor' => $scheduleRecord->editor,
                'location' => $this->booking?->location,
            ];
        }

        if (! $this->start_at && ! $this->end_at && ! $this->pendingPhotographerId && ! $this->pendingEditorId) {
            return null;
        }

        return (object) [
            'project_id' => $this->id,
            'photographer_id' => $this->photographer_id ?? $this->pendingPhotographerId,
            'editor_id' => $this->editor_id ?? $this->pendingEditorId,
            'start_at' => $this->start_at,
            'end_at' => $this->end_at,
            'photographer' => $this->photographer,
            'editor' => $this->editor,
            'location' => $this->booking?->location,
        ];
    }

    public function hasSchedule(): bool
    {
        return ($this->exists && $this->scheduleRecord()->exists())
            || ($this->start_at !== null && $this->end_at !== null);
    }

    public function hasRawDriveLink(): bool
    {
        return filled($this->raw_drive_url);
    }

    public function rawDriveExpiresAt(): ?Carbon
    {
        // Link Drive hanya ditampilkan selama masa akses yang ditentukan sistem.
        return $this->raw_drive_uploaded_at?->copy()->addDays(self::DRIVE_ACCESS_DAYS);
    }

    public function finalDriveExpiresAt(): ?Carbon
    {
        return $this->final_drive_uploaded_at?->copy()->addDays(self::DRIVE_ACCESS_DAYS);
    }

    public function isRawDriveExpired(): bool
    {
        return $this->rawDriveExpiresAt()?->isPast() ?? false;
    }

    public function isFinalDriveExpired(): bool
    {
        return $this->finalDriveExpiresAt()?->isPast() ?? false;
    }

    public function hasEditRequest(): bool
    {
        return $this->edit_requested_at !== null;
    }

    public function hasFinalDelivery(): bool
    {
        return $this->final_drive_uploaded_at !== null;
    }

    public function hasPostProductionActivity(): bool
    {
        return $this->hasRawDriveLink()
            || $this->hasEditRequest()
            || $this->hasFinalDelivery()
            || $this->mediaAssets()->exists();
    }

    public function bookingAllowsProduction(): bool
    {
        return $this->booking?->status === Booking::STATUS_PAID;
    }

    public function bookingAllowsScheduling(): bool
    {
        return in_array($this->booking?->status, [Booking::STATUS_DP_PAID, Booking::STATUS_PAID], true);
    }

    public function canContinueProduction(): bool
    {
        return $this->productionBlockMessage() === null;
    }

    public function canStartPostProduction(): bool
    {
        return $this->canContinueProduction()
            && $this->status === self::STATUS_SCHEDULED;
    }

    public function productionBlockMessage(): ?string
    {
        if ($this->booking?->status === Booking::STATUS_CANCELLED) {
            return 'Pemesanan sudah dibatalkan. Proses pasca-produksi tidak dapat dilanjutkan.';
        }

        if (! $this->bookingAllowsProduction()) {
            return 'Proses pasca-produksi hanya dapat dilanjutkan setelah pembayaran lunas.';
        }

        if (! $this->hasSchedule() || $this->status === self::STATUS_DRAFT) {
            return 'Proses pasca-produksi hanya dapat dilanjutkan setelah admin menjadwalkan project.';
        }

        return null;
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            self::STATUS_DRAFT => 'Belum Dijadwalkan',
            self::STATUS_SCHEDULED => 'Terjadwal',
            self::STATUS_SHOOT_DONE => 'Link Foto Mentah Tersedia',
            self::STATUS_EDITING => 'Permintaan Edit Dikirim',
            self::STATUS_FINAL => 'Hasil Final Siap',
            default => $this->status,
        };
    }
}
