<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\StudioLocation;
use App\Models\StudioRoom;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

/**
 * Kelola data cabang studio, galeri foto, dan ruangan.
 */
class StudioLocationController extends Controller
{
    /** Endpoint list lokasi (format JSON). */
    public function index()
    {
        return response()->json(StudioLocation::orderBy('name')->get());
    }

    /** Halaman kelola cabang (form + list). */
    public function manage(Request $request)
    {
        $locations = StudioLocation::with(['rooms'])->orderBy('name')->get();
        $editing = null;
        if ($request->filled('edit')) {
            $editing = StudioLocation::find($request->query('edit'));
        }
        return view('admin.locations.index', compact('locations', 'editing'));
    }

    public function store(Request $request)
    {
        $data = $this->validateData($request);
        $data['slug'] = Str::slug($data['name']);

        $location = StudioLocation::create($data);

        if ($request->hasFile('photos')) {
            // Simpan multi foto sekaligus, dan foto pertama jadi cover default.
            $paths = [];
            foreach ($request->file('photos') as $file) {
                $paths[] = $file->storePublicly("locations/{$location->id}", 'public');
            }
            $this->syncPhotos($location, $paths);
        }

        if ($request->wantsJson()) {
            return response()->json($location, 201);
        }
        return back()->with('status', 'Lokasi ditambahkan.');
    }

    public function update(Request $request, StudioLocation $studioLocation)
    {
        $data = $this->validateData($request);
        if ($studioLocation->name !== $data['name']) {
            $data['slug'] = Str::slug($data['name']);
        }
        $studioLocation->update($data);

        $gallery = collect($studioLocation->photo_gallery ?? []);

        if ($request->boolean('remove_photos')) {
            // Hapus semua foto lama bila user meminta reset galeri.
            foreach ($gallery as $p) {
                \Storage::disk('public')->delete($p);
            }
            $gallery = collect();
        }

        if ($request->hasFile('photos')) {
            foreach ($request->file('photos') as $file) {
                $gallery->push($file->storePublicly("locations/{$studioLocation->id}", 'public'));
            }
            $this->syncPhotos($studioLocation, $gallery->values()->all());
        }

        if ($request->wantsJson()) {
            return response()->json($studioLocation);
        }
        return back()->with('status', 'Lokasi diperbarui.');
    }

    public function destroy(StudioLocation $studioLocation)
    {
        $studioLocation->delete();
        if (request()->wantsJson()) {
            return response()->json(['message' => 'deleted']);
        }
        return back()->with('status', 'Lokasi dihapus.');
    }

    protected function validateData(Request $request): array
    {
        // Validasi tunggal agar konsisten antara create/update.
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'address' => ['nullable', 'string', 'max:500'],
            'description' => ['nullable', 'string'],
            'map_url' => ['nullable', 'url', 'max:500'],
            'photos' => ['nullable', 'array', 'max:10'],
            'photos.*' => ['image', 'max:20480'],
            'is_active' => ['boolean'],
            'remove_photos' => ['nullable', 'boolean'],
        ]);
    }

    /** Tambah studio/ruang dalam cabang. */
    public function storeRoom(Request $request)
    {
        $data = $request->validate([
            'studio_location_id' => ['required', 'exists:studio_locations,id'],
            'name' => ['required','string','max:255'],
            'description' => ['nullable','string'],
            'is_active' => ['boolean'],
        ]);

        StudioRoom::create($data);

        return back()->with('status', 'Studio/ruang ditambahkan.');
    }

    /** Ubah nama/deskripsi/status ruangan studio. */
    public function updateRoom(Request $request, StudioRoom $studioRoom)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $studioRoom->update([
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'is_active' => (bool) ($data['is_active'] ?? false),
        ]);

        return back()->with('status', 'Studio/ruang diperbarui.');
    }

    /** Hapus ruangan jika belum pernah dipakai booking; jika sudah dipakai wajib nonaktifkan. */
    public function destroyRoom(StudioRoom $studioRoom)
    {
        if ($studioRoom->bookings()->exists()) {
            $studioRoom->update(['is_active' => false]);
            return back()->with('status', 'Ruangan sudah dipakai booking, status diubah menjadi nonaktif.');
        }

        $studioRoom->delete();
        return back()->with('status', 'Studio/ruang dihapus.');
    }

    protected function syncPhotos(StudioLocation $location, array $paths): void
    {
        $location->update([
            'photo_gallery' => array_values(array_filter($paths)),
        ]);
    }
}
