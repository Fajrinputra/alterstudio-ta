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
 * Mengelola cabang studio, ruangan, dan galeri lokasi.
 */
class StudioLocationController extends Controller
{
    /** Menampilkan daftar lokasi dalam format JSON. */
    public function index()
    {
        return response()->json(StudioLocation::orderBy('name')->get());
    }

    /** Menampilkan daftar cabang dan ringkasan ruangan. */
    public function manage()
    {
        $locations = StudioLocation::withCount('rooms')
            ->with(['rooms' => fn ($query) => $query->orderBy('name')])
            ->orderBy('name')
            ->get();

        return view('admin.locations.index', compact('locations'));
    }

    public function create()
    {
        return view('admin.locations.create');
    }

    public function show(StudioLocation $studioLocation)
    {
        $studioLocation->load(['rooms' => fn ($query) => $query->orderBy('name')]);

        return view('admin.locations.show', compact('studioLocation'));
    }

    public function edit(StudioLocation $studioLocation)
    {
        $studioLocation->load(['rooms' => fn ($query) => $query->orderBy('name')]);

        return view('admin.locations.edit', compact('studioLocation'));
    }

    public function store(Request $request)
    {
        $data = $this->validateData($request);
        $data['slug'] = Str::slug($data['name']);
        $data['is_active'] = $request->boolean('is_active');
        unset($data['photos'], $data['remove_photos']);

        $location = StudioLocation::create($data);

        if ($request->hasFile('photos')) {
            // Simpan beberapa foto sekaligus untuk galeri lokasi.
            $paths = [];
            foreach ($request->file('photos') as $file) {
                $paths[] = $file->storePublicly("locations/{$location->id}", 'public');
            }
            $this->syncPhotos($location, $paths);
        }

        Cache::forget('landing.page.data.v2');

        if ($request->wantsJson()) {
            return response()->json($location, 201);
        }
        return redirect()->route('admin.locations.show', $location)->with('status', 'Cabang berhasil ditambahkan.');
    }

    public function update(Request $request, StudioLocation $studioLocation)
    {
        $data = $this->validateData($request);
        if ($studioLocation->name !== $data['name']) {
            $data['slug'] = Str::slug($data['name']);
        }
        $data['is_active'] = $request->boolean('is_active');
        unset($data['photos'], $data['remove_photos']);
        $studioLocation->update($data);

        $gallery = collect($studioLocation->photo_gallery ?? []);

        if ($request->boolean('remove_photos')) {
            // Hapus seluruh foto lama jika pengguna meminta reset galeri.
            foreach ($gallery as $p) {
                \Storage::disk('public')->delete($p);
            }
            $gallery = collect();
        }

        if ($request->hasFile('photos')) {
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

    public function destroy(StudioLocation $studioLocation)
    {
        $hasBookingHistory = $studioLocation->bookings()->exists()
            || $studioLocation->rooms()->whereHas('bookings')->exists();

        if ($hasBookingHistory) {
            $studioLocation->update(['is_active' => false]);
            $studioLocation->rooms()->update(['is_active' => false]);
            Cache::forget('landing.page.data.v2');

            $message = 'Cabang sudah digunakan pada pemesanan, sehingga dinonaktifkan untuk menjaga riwayat transaksi.';

            return request()->wantsJson()
                ? response()->json(['message' => $message])
                : redirect()->route('admin.locations.manage')->with('status', $message);
        }

        foreach ($studioLocation->photo_gallery as $photo) {
            Storage::disk('public')->delete($photo);
        }
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

    protected function validateData(Request $request): array
    {
        // Satu aturan validasi dipakai bersama agar hasil create dan update konsisten.
        return $request->validate([
            'name' => ['required', 'string', 'max:50'],
            'address' => ['nullable', 'string', 'max:500'],
            'description' => ['nullable', 'string'],
            'map_url' => ['nullable', 'url', 'max:500'],
            'photos' => ['nullable', 'array', 'max:10'],
            'photos.*' => ImageUploadValidation::rules(),
            'is_active' => ['nullable', 'boolean'],
            'remove_photos' => ['nullable', 'boolean'],
        ], ImageUploadValidation::messages(['photos.*']));
    }

    /** Menambah ruangan pada cabang studio tertentu. */
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

    /** Hapus satu foto dari galeri lokasi berdasarkan indeks. */
    public function destroyPhoto(Request $request, StudioLocation $studioLocation)
    {
        $data = $request->validate([
            'photo_index' => ['required', 'integer', 'min:0'],
        ]);

        $gallery = collect($studioLocation->photo_gallery ?? []);
        $index   = (int) $data['photo_index'];

        if (! $gallery->has($index)) {
            return back()->with('error', 'Foto tidak ditemukan.');
        }

        $photoPath = $gallery->get($index);

        // Hapus file fisik.
        Storage::disk('public')->delete($photoPath);

        // Update galeri tanpa foto yang dihapus.
        $newGallery = $gallery->forget($index)->values()->all();
        $this->syncPhotos($studioLocation, $newGallery);

        Cache::forget('landing.page.data.v2');

        return back()->with('status', 'Foto berhasil dihapus.');
    }

    protected function syncPhotos(StudioLocation $location, array $paths): void
    {
        $location->update([
            'photo_gallery' => array_values(array_filter($paths)),
        ]);
    }
}
