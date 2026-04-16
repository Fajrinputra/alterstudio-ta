<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\StudioHoliday;
use App\Models\StudioLocation;
use App\Models\StudioRoom;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

/**
 * Mengelola cabang studio, ruangan, galeri lokasi, dan hari libur manual.
 */
class StudioLocationController extends Controller
{
    /** Menampilkan daftar lokasi dalam format JSON. */
    public function index()
    {
        return response()->json(StudioLocation::orderBy('name')->get());
    }

    /** Menampilkan halaman kelola cabang beserta ruangan dan hari libur. */
    public function manage(Request $request)
    {
        $locations = StudioLocation::with(['rooms', 'holidays'])->orderBy('name')->get();
        $holidays = StudioHoliday::with('studioLocation')->orderBy('holiday_date')->get();
        $editing = null;
        if ($request->filled('edit')) {
            $editing = StudioLocation::find($request->query('edit'));
        }
        return view('admin.locations.index', compact('locations', 'editing', 'holidays'));
    }

    public function store(Request $request)
    {
        $data = $this->validateData($request);
        $data['slug'] = Str::slug($data['name']);

        $location = StudioLocation::create($data);

        if ($request->hasFile('photos')) {
            // Simpan beberapa foto sekaligus untuk galeri lokasi.
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
            return response()->json(['message' => 'Lokasi berhasil dihapus.']);
        }
        return back()->with('status', 'Lokasi dihapus.');
    }

    protected function validateData(Request $request): array
    {
        // Satu aturan validasi dipakai bersama agar hasil create dan update konsisten.
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

    /** Menambah ruangan pada cabang studio tertentu. */
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

    /** Mengubah nama, deskripsi, atau status ruangan studio. */
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

    /** Menghapus ruangan jika belum pernah dipakai; jika sudah pernah dipakai, ruangan dinonaktifkan. */
    public function destroyRoom(StudioRoom $studioRoom)
    {
        if ($studioRoom->bookings()->exists()) {
            $studioRoom->update(['is_active' => false]);
            return back()->with('status', 'Ruangan sudah dipakai booking, status diubah menjadi nonaktif.');
        }

        $studioRoom->delete();
        return back()->with('status', 'Studio/ruang dihapus.');
    }

    public function storeHoliday(Request $request)
    {
        $data = $request->validate([
            'studio_location_id' => ['required', 'exists:studio_locations,id'],
            'holiday_date' => ['required', 'date'],
            'name' => ['required', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:255'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $exists = StudioHoliday::query()
            ->where('studio_location_id', $data['studio_location_id'])
            ->whereDate('holiday_date', $data['holiday_date'])
            ->exists();

        if ($exists) {
            return back()
                ->withInput()
                ->withErrors(['holiday_date' => 'Tanggal libur untuk cabang yang dipilih sudah terdaftar.']);
        }

        StudioHoliday::create([
            'studio_location_id' => $data['studio_location_id'],
            'holiday_date' => $data['holiday_date'],
            'name' => $data['name'],
            'notes' => $data['notes'] ?? null,
            'is_active' => (bool) ($data['is_active'] ?? true),
        ]);

        return back()->with('status', 'Hari libur studio ditambahkan.');
    }

    public function updateHoliday(Request $request, StudioHoliday $studioHoliday)
    {
        $data = $request->validate([
            'studio_location_id' => ['required', 'exists:studio_locations,id'],
            'holiday_date' => ['required', 'date'],
            'name' => ['required', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:255'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $exists = StudioHoliday::query()
            ->where('studio_location_id', $data['studio_location_id'])
            ->whereDate('holiday_date', $data['holiday_date'])
            ->whereKeyNot($studioHoliday->id)
            ->exists();

        if ($exists) {
            return back()
                ->withInput()
                ->withErrors(['holiday_date' => 'Tanggal libur untuk cabang yang dipilih sudah terdaftar.']);
        }

        $studioHoliday->update([
            'studio_location_id' => $data['studio_location_id'],
            'holiday_date' => $data['holiday_date'],
            'name' => $data['name'],
            'notes' => $data['notes'] ?? null,
            'is_active' => (bool) ($data['is_active'] ?? false),
        ]);

        return back()->with('status', 'Hari libur studio diperbarui.');
    }

    public function destroyHoliday(StudioHoliday $studioHoliday)
    {
        $studioHoliday->delete();

        return back()->with('status', 'Hari libur studio dihapus.');
    }

    protected function syncPhotos(StudioLocation $location, array $paths): void
    {
        $location->update([
            'photo_gallery' => array_values(array_filter($paths)),
        ]);
    }
}
