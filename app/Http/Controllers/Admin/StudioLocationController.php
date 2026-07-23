<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\StudioLocation;
use App\Models\StudioRoom;
use App\Support\ImageUploadValidation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Controller untuk mengelola Cabang Studio, Ruangan, dan Galeri Foto Lokasi.
 *
 * Bertanggung jawab atas:
 * - CRUD data cabang/lokasi studio
 * - CRUD ruangan di dalam setiap cabang
 * - Pengelolaan galeri foto lokasi (tambah, hapus per-item)
 * - Sinkronisasi cache landing page setiap kali data berubah
 *
 * Catatan arsitektur:
 * - Foto galeri disimpan sebagai array path di kolom JSON `photo_gallery`.
 * - File fisik foto ada di storage/public/locations/{id}/...
 * - Cache key 'landing.page.data.v2' harus di-clear setiap ada perubahan
 *   agar tampilan landing page tidak menampilkan data lama.
 */
class StudioLocationController extends Controller
{
    /**
     * Mengembalikan daftar semua lokasi dalam format JSON.
     * Dipakai oleh API internal (misalnya AJAX di halaman booking).
     */
    public function index()
    {
        return response()->json(StudioLocation::orderBy('name')->get());
    }

    /**
     * Menampilkan halaman daftar seluruh cabang studio (untuk owner/admin).
     * Memuat jumlah ruangan per cabang dan daftar ruangan terurut alfabetis.
     */
    public function manage()
    {
        // withCount('rooms') menambahkan kolom rooms_count tanpa query N+1.
        $locations = StudioLocation::withCount('rooms')
            ->with(['rooms' => fn ($query) => $query->orderBy('name')])
            ->orderBy('name')
            ->get();

        return view('admin.locations.index', compact('locations'));
    }

    /** Menampilkan form tambah cabang baru. */
    public function create()
    {
        return view('admin.locations.create');
    }

    /**
     * Menampilkan detail satu cabang beserta daftar ruangannya.
     * Menggunakan load() karena model sudah di-resolve oleh route model binding.
     */
    public function show(StudioLocation $studioLocation)
    {
        $studioLocation->load(['rooms' => fn ($query) => $query->orderBy('name')]);

        return view('admin.locations.show', compact('studioLocation'));
    }

    /**
     * Menampilkan form edit data cabang dan manajemen ruangan di sidebar.
     * Ruangan dimuat secara eager agar tidak terjadi query N+1 di view.
     */
    public function edit(StudioLocation $studioLocation)
    {
        $studioLocation->load(['rooms' => fn ($query) => $query->orderBy('name')]);

        return view('admin.locations.edit', compact('studioLocation'));
    }

    /**
     * Menyimpan cabang baru ke database beserta foto galeri (jika ada).
     *
     * Alur:
     * 1. Validasi data (pakai validateData yang dipakai bersama dengan update).
     * 2. Generate slug dari nama cabang untuk URL yang ramah.
     * 3. Simpan record cabang.
     * 4. Upload setiap foto ke storage dan simpan path-nya di galeri.
     * 5. Hapus cache landing page agar tampilan publik langsung diperbarui.
     */
    public function store(Request $request)
    {
        $data = $this->validateData($request);
        $data['slug'] = Str::slug($data['name']);           // Slug untuk URL SEO-friendly.
        $data['is_active'] = $request->boolean('is_active');
        unset($data['photos'], $data['remove_photos']);      // Hapus key file — ditangani terpisah.

        $location = StudioLocation::create($data);

        if ($request->hasFile('photos')) {
            // Upload semua foto sekaligus ke folder per-ID lokasi agar terorganisir.
            $paths = [];
            foreach ($request->file('photos') as $file) {
                $paths[] = $file->storePublicly("locations/{$location->id}", 'public');
            }
            $this->syncPhotos($location, $paths);
        }

        // Hapus cache agar landing page tidak menampilkan data lama.
        Cache::forget('landing.page.data.v2');

        if ($request->wantsJson()) {
            return response()->json($location, 201);
        }
        return redirect()->route('admin.locations.show', $location)->with('status', 'Cabang berhasil ditambahkan.');
    }

    /**
     * Memperbarui data cabang dan galeri fotonya.
     *
     * Alur pengelolaan foto:
     * - Foto lama tetap dipertahankan kecuali di-delete satu per satu via destroyPhoto().
     * - Foto baru yang di-upload ditambahkan ke galeri yang sudah ada (append).
     * - remove_photos (checkbox) sudah dihapus dari view, tapi logika ini dipertahankan
     *   untuk backward compatibility jika ada request API lama.
     */
    public function update(Request $request, StudioLocation $studioLocation)
    {
        $data = $this->validateData($request);

        // Perbarui slug hanya jika nama cabang berubah.
        if ($studioLocation->name !== $data['name']) {
            $data['slug'] = Str::slug($data['name']);
        }
        $data['is_active'] = $request->boolean('is_active');
        unset($data['photos'], $data['remove_photos']); // File ditangani di bawah.
        $studioLocation->update($data);

        // Ambil galeri yang ada saat ini sebagai Collection untuk manipulasi.
        $gallery = collect($studioLocation->photo_gallery ?? []);

        if ($request->boolean('remove_photos')) {
            // Hapus seluruh file fisik dari storage sebelum reset galeri.
            foreach ($gallery as $p) {
                \Storage::disk('public')->delete($p);
            }
            $gallery = collect();
        }

        if ($request->hasFile('photos')) {
            // Tambahkan foto baru ke galeri yang ada (bukan mengganti).
            foreach ($request->file('photos') as $file) {
                $gallery->push($file->storePublicly("locations/{$studioLocation->id}", 'public'));
            }
        }

        $this->syncPhotos($studioLocation, $gallery->values()->all());
        Cache::forget('landing.page.data.v2');

        if ($request->wantsJson()) {
            return response()->json($studioLocation);
        }
        return redirect()->route('admin.locations.show', $studioLocation)->with('status', 'Cabang berhasil diperbarui.');
    }

    /**
     * Menghapus cabang studio.
     *
     * Aturan bisnis:
     * - Jika cabang sudah pernah digunakan pada booking (ada riwayat transaksi),
     *   maka cabang dan seluruh ruangannya hanya DINONAKTIFKAN, bukan dihapus.
     *   Ini menjaga integritas data historis.
     * - Jika belum pernah digunakan, hapus fisik file foto galeri dan foto ruangan
     *   dari storage, lalu hapus record dari database.
     */
    public function destroy(StudioLocation $studioLocation)
    {
        // Cek apakah cabang atau salah satu ruangannya sudah pernah di-booking.
        $hasBookingHistory = $studioLocation->bookings()->exists()
            || $studioLocation->rooms()->whereHas('bookings')->exists();

        if ($hasBookingHistory) {
            // Soft-disable: nonaktifkan saja agar riwayat booking tetap valid.
            $studioLocation->update(['is_active' => false]);
            $studioLocation->rooms()->update(['is_active' => false]);
            Cache::forget('landing.page.data.v2');

            $message = 'Cabang sudah digunakan pada pemesanan, sehingga dinonaktifkan untuk menjaga riwayat transaksi.';

            return request()->wantsJson()
                ? response()->json(['message' => $message])
                : redirect()->route('admin.locations.manage')->with('status', $message);
        }

        // Hapus semua file foto galeri dari disk storage.
        foreach ($studioLocation->photo_gallery as $photo) {
            Storage::disk('public')->delete($photo);
        }

        // Hapus foto tiap ruangan dari disk storage.
        foreach ($studioLocation->rooms as $room) {
            if ($room->photo_path) {
                Storage::disk('public')->delete($room->photo_path);
            }
        }

        $studioLocation->delete();
        Cache::forget('landing.page.data.v2');

        if (request()->wantsJson()) {
            return response()->json(['message' => 'Lokasi berhasil dihapus.']);
        }
        return redirect()->route('admin.locations.manage')->with('status', 'Cabang berhasil dihapus.');
    }

    /**
     * Aturan validasi yang dipakai bersama oleh store() dan update().
     * Menggunakan satu definisi agar kedua method selalu konsisten.
     *
     * @return array Data yang sudah tervalidasi.
     */
    protected function validateData(Request $request): array
    {
        return $request->validate([
            'name'          => ['required', 'string', 'max:50'],
            'address'       => ['nullable', 'string', 'max:500'],
            'description'   => ['nullable', 'string'],
            'map_url'       => ['nullable', 'url', 'max:500'],
            'photos'        => ['nullable', 'array', 'max:10'], // Maks. 10 foto per cabang.
            'photos.*'      => ImageUploadValidation::rules(),  // Validasi format & ukuran tiap file.
            'is_active'     => ['nullable', 'boolean'],
            'remove_photos' => ['nullable', 'boolean'],        // Dipertahankan untuk backward compat.
        ], ImageUploadValidation::messages(['photos.*']));
    }

    /**
     * Menambahkan ruangan baru ke dalam cabang studio yang dipilih.
     * Foto ruangan (opsional) disimpan di subfolder per-location_code.
     */
    public function storeRoom(Request $request)
    {
        $data = $request->validate([
            'studio_location_code' => ['required', 'exists:studio_locations,location_code'],
            'name' => ['required','string','max:50'],
            'description' => ['nullable','string'],
            'photo' => ImageUploadValidation::rules(),
            'is_active' => ['nullable', 'boolean'],
        ], ImageUploadValidation::messages(['photo']));

        $data['is_active'] = $request->boolean('is_active');

        if ($request->hasFile('photo')) {
            $data['photo_path'] = $request->file('photo')->storePublicly("rooms/{$data['studio_location_code']}", 'public');
        }
        unset($data['photo']);

        StudioRoom::create($data);

        Cache::forget('landing.page.data.v2');
        return back()->with('status', 'Studio/ruang berhasil ditambahkan.');
    }

    /** Mengubah nama, deskripsi, atau status ruangan studio. */
    public function updateRoom(Request $request, StudioRoom $studioRoom)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:50'],
            'description' => ['nullable', 'string'],
            'photo' => ImageUploadValidation::rules(),
            'is_active' => ['nullable', 'boolean'],
        ], ImageUploadValidation::messages(['photo']));

        $payload = [
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'is_active' => $request->boolean('is_active'),
        ];

        if ($request->hasFile('photo')) {
            if ($studioRoom->photo_path) {
                \Storage::disk('public')->delete($studioRoom->photo_path);
            }
            $payload['photo_path'] = $request->file('photo')->storePublicly("rooms/{$studioRoom->studio_location_code}", 'public');
        }

        $studioRoom->update($payload);

        Cache::forget('landing.page.data.v2');
        return back()->with('status', 'Studio/ruang berhasil diperbarui.');
    }

    /** Menghapus ruangan jika belum pernah dipakai; jika sudah pernah dipakai, ruangan dinonaktifkan. */
    public function destroyRoom(StudioRoom $studioRoom)
    {
        if ($studioRoom->bookings()->exists()) {
            $studioRoom->update(['is_active' => false]);
            Cache::forget('landing.page.data.v2');
            return back()->with('status', 'Ruangan sudah dipakai booking, status diubah menjadi nonaktif.');
        }

        if ($studioRoom->photo_path) {
            \Storage::disk('public')->delete($studioRoom->photo_path);
        }

        $studioRoom->delete();
        Cache::forget('landing.page.data.v2');
        return back()->with('status', 'Studio/ruang berhasil dihapus.');
    }

    /**
     * Menghapus satu foto dari galeri lokasi berdasarkan posisi (indeks).
     *
     * Cara kerja:
     * 1. Validasi bahwa photo_index yang dikirim ada di dalam array galeri.
     * 2. Hapus file fisik dari storage.
     * 3. Simpan ulang galeri tanpa foto tersebut (index di-reindex dari 0).
     *
     * Lebih aman dari "hapus semua" karena pengguna bisa kontrol foto mana yang dihapus.
     */
    public function destroyPhoto(Request $request, StudioLocation $studioLocation)
    {
        $data = $request->validate([
            'photo_index' => ['required', 'integer', 'min:0'],
        ]);

        $gallery = collect($studioLocation->photo_gallery ?? []);
        $index   = (int) $data['photo_index'];

        // Cek apakah indeks yang diminta benar-benar ada di dalam galeri.
        if (! $gallery->has($index)) {
            return back()->with('error', 'Foto tidak ditemukan.');
        }

        $photoPath = $gallery->get($index);

        // Hapus file fisik dari disk storage terlebih dahulu.
        Storage::disk('public')->delete($photoPath);

        // Simpan ulang galeri tanpa foto yang dihapus; values() mereindex array dari 0.
        $newGallery = $gallery->forget($index)->values()->all();
        $this->syncPhotos($studioLocation, $newGallery);

        Cache::forget('landing.page.data.v2');

        return back()->with('status', 'Foto berhasil dihapus.');
    }

    /**
     * Menyimpan ulang array path foto ke kolom photo_gallery.
     * Menggunakan array_values() untuk memastikan indeks selalu berurutan dari 0.
     * Menggunakan array_filter() untuk membuang nilai null/kosong.
     */
    protected function syncPhotos(StudioLocation $location, array $paths): void
    {
        $location->update([
            'photo_gallery' => array_values(array_filter($paths)),
        ]);
    }
}
