<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ServicePackage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

/**
 * CRUD paket layanan + manajemen file gambar paket.
 */
class ServicePackageController extends Controller
{
    public function index()
    {
        return response()->json(
            ServicePackage::with('category')
                ->orderBy('name')
                ->get()
        );
    }

    public function store(Request $request)
    {
        // Validasi + normalisasi field paket.
        $data = $this->prepareData($request);
        $package = ServicePackage::create($data);
        $this->syncFeatures($package, $data['features'] ?? []);
        $this->syncAddons($package, $data['addons'] ?? []);
        $this->handleOverviewImage($request, $package);
        $this->syncGallery($request, $package);

        if ($request->wantsJson()) {
            return response()->json($package->load('category'), 201);
        }
        return back()->with('status', 'Paket ditambahkan.');
    }

    public function show(ServicePackage $servicePackage)
    {
        return view('admin.catalog.package-show', [
            'package' => $servicePackage,
            'category' => $servicePackage->category,
            'gallery' => $this->cleanedGallery($servicePackage->gallery),
        ]);
    }

    public function edit(ServicePackage $servicePackage)
    {
        return view('admin.catalog.package-edit', [
            'package' => $servicePackage,
            'category' => $servicePackage->category,
            'gallery' => $this->cleanedGallery($servicePackage->gallery),
        ]);
    }

    public function update(Request $request, ServicePackage $servicePackage)
    {
        // Update data paket tanpa memaksa ganti file.
        $data = $this->prepareData($request, $servicePackage->id);
        $servicePackage->update($data);
        $this->syncFeatures($servicePackage, $data['features'] ?? []);
        $this->syncAddons($servicePackage, $data['addons'] ?? []);
        $this->handleOverviewImage($request, $servicePackage);
        $this->syncGallery($request, $servicePackage);

        if ($request->wantsJson()) {
            return response()->json($servicePackage->load('category'));
        }
        return redirect()
            ->route('admin.catalog.packages', $servicePackage->category_id)
            ->with('status', 'Paket diperbarui.');
    }

    public function destroy(ServicePackage $servicePackage)
    {
        // Jika paket masih dipakai booking berjalan, nonaktifkan alih-alih hard delete.
        $activeUsageExists = $servicePackage->bookings()
            ->where(function ($q) {
                $q->where('status', '!=', 'CANCELLED')
                    ->where(function ($qq) {
                        $qq->whereDoesntHave('project')
                            ->orWhereHas('project', fn ($p) => $p->where('status', '!=', 'FINAL'));
                    });
            })
            ->exists();

        if ($activeUsageExists) {
            $servicePackage->update(['is_active' => false]);
            $message = 'Paket sedang dipakai pada pemesanan aktif, paket dinonaktifkan otomatis dan tidak dapat dihapus.';

            if (request()->wantsJson()) {
                return response()->json(['message' => $message], 422);
            }

            return back()->with('error', $message);
        }

        $servicePackage->delete();

        if (request()->wantsJson()) {
            return response()->json(['message' => 'deleted']);
        }
        return back()->with('status', 'Paket dihapus.');
    }

    protected function prepareData(Request $request, ?int $id = null): array
    {
        // Data file (gallery/overview) diproses di method terpisah.
        $validated = $request->validate([
            'category_id' => ['required', 'exists:service_categories,id'],
            'name' => ['required', 'string', 'max:255'],
            'price' => ['required', 'integer', 'min:0'],
            'max_people' => ['nullable', 'integer', 'min:1'],
            'description' => ['nullable', 'string'],
            'features' => ['nullable'],
            'addons' => ['nullable', 'array'],
            'addons.*.label' => ['nullable', 'string', 'max:255'],
            'addons.*.price' => ['nullable', 'integer', 'min:0'],
            'addons.*.unit' => ['nullable', 'string', 'max:50'],
            'terms' => ['nullable', 'string'],
            'overview_image' => ['nullable', 'image', 'max:20480'],
            'is_active' => ['boolean'],
            'gallery' => ['nullable', 'array', 'max:20'],
            'gallery.*' => ['image', 'max:20480'],
            'duration_minutes' => ['nullable','integer','min:1'],
        ]);

        $validated['features'] = $this->toArray($validated['features'] ?? null, "\n");
        $validated['addons'] = $this->normalizeAddons($validated['addons'] ?? []);
        $validated['is_active'] = $request->boolean('is_active');

        // file ditangani terpisah
        unset($validated['gallery'], $validated['overview_image']);

        return $validated;
    }

    protected function toArray($value, string $delimiter): array
    {
        if (is_array($value)) {
            return array_values(array_filter(array_map('trim', $value), fn ($v) => $v !== ''));
        }

        if (is_string($value)) {
            $parts = $delimiter === "\n"
                ? preg_split('/\r\n|\r|\n/', $value)
                : explode($delimiter, $value);
            return array_values(array_filter(array_map('trim', $parts), fn ($v) => $v !== ''));
        }

        return [];
    }

    protected function normalizeAddons(array $addons): array
    {
        return collect($addons)
            ->map(function ($addon) {
                $label = trim((string) ($addon['label'] ?? ''));
                $price = (int) ($addon['price'] ?? 0);
                if ($label === '') {
                    return null;
                }

                return [
                    'label' => $label,
                    'price' => max(0, $price),
                    'unit' => trim((string) ($addon['unit'] ?? '')),
                ];
            })
            ->filter()
            ->values()
            ->all();
    }

    protected function syncGallery(Request $request, ServicePackage $package): void
    {
        // Tambah gallery baru sambil mempertahankan existing max 20 item.
        if (!$request->hasFile('gallery')) {
            return;
        }

        $paths = [];
        foreach ($request->file('gallery') as $file) {
            $paths[] = $file->storePublicly("packages/{$package->id}/gallery", 'public');
        }

        $existing = $this->cleanedGallery($package->gallery);
        $merged = array_slice(array_merge($existing, $paths), 0, 20);
        $this->syncGalleryItems($package, $merged);
    }

    protected function handleOverviewImage(Request $request, ServicePackage $package): void
    {
        // Mendukung hapus overview atau ganti overview.
        if ($request->boolean('remove_overview')) {
            $gallery = collect($package->gallery)->filter()->values();
            $cover = $package->overview_image;

            if ($cover) {
                Storage::disk('public')->delete($cover);
                $gallery = $gallery->reject(fn ($path) => $path === $cover)->values();
                $this->syncGalleryItems($package, $gallery->all());
            }
        }

        if ($request->hasFile('overview_image')) {
            $path = $request->file('overview_image')->storePublicly("packages/{$package->id}/overview", 'public');
            $previousCover = $package->overview_image;
            if ($previousCover) {
                Storage::disk('public')->delete($previousCover);
            }

            $gallery = collect($package->gallery)->filter()->values()->all();
            if ($previousCover) {
                $gallery = array_values(array_filter($gallery, fn ($item) => $item !== $previousCover));
            }
            array_unshift($gallery, $path);
            $gallery = array_values(array_unique($gallery));
            $this->syncGalleryItems($package, $gallery, $path);
        }
    }

    protected function cleanedGallery($raw): array
    {
        return collect($raw ?? [])
            ->map(function ($item) {
                if (is_string($item)) return $item;
                if (is_array($item)) {
                    foreach ($item as $v) {
                        if (is_string($v)) return $v;
                    }
                }
                return null;
            })
            ->filter()
            ->values()
            ->all();
    }

    protected function syncFeatures(ServicePackage $package, array $features): void
    {
        $package->update([
            'features' => array_values(array_filter(array_map('trim', $features), fn ($value) => $value !== '')),
        ]);
    }

    protected function syncAddons(ServicePackage $package, array $addons): void
    {
        $package->update([
            'addons' => array_values(array_map(function ($addon) {
                return [
                    'label' => trim((string) ($addon['label'] ?? '')),
                    'price' => (int) ($addon['price'] ?? 0),
                    'unit' => trim((string) ($addon['unit'] ?? '')),
                    'is_active' => (bool) ($addon['is_active'] ?? true),
                ];
            }, $addons)),
        ]);
    }

    protected function syncGalleryItems(ServicePackage $package, array $paths, ?string $coverPath = null): void
    {
        $cover = $coverPath ?? ($paths[0] ?? null);
        $package->update([
            'gallery' => array_values(array_filter($paths)),
            'cover_image' => $cover,
        ]);
    }
}
