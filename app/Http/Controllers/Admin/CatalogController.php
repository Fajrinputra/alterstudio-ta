<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ServiceCategory;
use App\Models\ServicePackage;
use Illuminate\Http\Request;

/**
 * Kelola kategori/paket katalog dari sisi admin/manager.
 */
class CatalogController extends Controller
{
    /** Daftar kategori untuk halaman katalog admin. */
    public function index()
    {
        $categories = ServiceCategory::with(['packages' => fn ($q) => $q->orderBy('name')])->orderBy('name')->get();
        return view('admin.catalog.index', compact('categories'));
    }

    /** Tampilan katalog read-only untuk semua role. */
    public function publicIndex()
    {
        $categories = ServiceCategory::with(['packages' => fn ($q) => $q->orderBy('name')])->orderBy('name')->get();
        return view('admin.catalog.index', compact('categories'));
    }

    public function publicShow(ServicePackage $servicePackage)
    {
        // Detail paket pada halaman katalog client (read-only).
        $servicePackage->load(['category']);
        return view('admin.catalog.show', compact('servicePackage'));
    }

    public function create()
    {
        return view('admin.catalog.create');
    }

    public function store(Request $request)
    {
        // Simpan kategori baru beserta paket awal (opsional).
        $data = $request->validate([
            'name' => ['required','string','max:255'],
            'description' => ['nullable','string'],
            'packages' => ['nullable','array'],
            'packages.*.name' => ['required_with:packages.*.price','string','max:255'],
            'packages.*.price' => ['nullable','integer','min:0'],
            'packages.*.description' => ['nullable','string'],
            'packages.*.features' => ['nullable','string'],
            'packages.*.addons' => ['nullable','string'],
            'packages.*.terms' => ['nullable','string'],
        ]);

        $category = ServiceCategory::create([
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
        ]);

        foreach ($data['packages'] ?? [] as $index => $pkg) {
            if (!($pkg['name'] ?? null)) {
                continue;
            }
            $features = $this->toArray($pkg['features'] ?? null, "\n");
            $addons = $this->normalizeCsvAddons($pkg['addons'] ?? null);

            $package = ServicePackage::create([
                'category_id' => $category->id,
                'name' => $pkg['name'],
                'price' => $pkg['price'] ?? 0,
                'description' => $pkg['description'] ?? null,
                'terms' => $pkg['terms'] ?? null,
                'is_active' => true,
            ]);
            $this->syncFeatures($package, $features);
            $this->syncAddons($package, $addons);

            // overview image per paket (packages[index][overview_image])
            if ($request->hasFile("packages.$index.overview_image")) {
                $path = $request->file("packages.$index.overview_image")->storePublicly("packages/{$package->id}/overview", 'public');
                $this->syncGalleryItems($package, array_values(array_unique(array_merge([$path], $package->gallery))));
            }
            // gallery per paket
            if ($request->hasFile("packages.$index.gallery")) {
                $paths = [];
                foreach ($request->file("packages.$index.gallery") as $file) {
                    $paths[] = $file->storePublicly("packages/{$package->id}/gallery", 'public');
                }
                $this->syncGalleryItems($package, array_slice($paths, 0, 20));
            }
        }

        return redirect()->route('admin.catalog')->with('status', 'Katalog dan paket tersimpan.');
    }

    public function packages(ServiceCategory $category)
    {
        // Daftar paket per kategori.
        $packages = ServicePackage::where('category_id', $category->id)->orderBy('name')->get();
        return view('admin.catalog.packages', compact('category','packages'));
    }

    public function createPackage(ServiceCategory $category)
    {
        return view('admin.catalog.package-create', compact('category'));
    }

    public function storePackage(Request $request, ServiceCategory $category)
    {
        // Tambah satu paket ke kategori tertentu.
        $data = $request->validate([
            'name' => ['required','string','max:255'],
            'price' => ['required','integer','min:0'],
            'max_people' => ['nullable','integer','min:1'],
            'description' => ['nullable','string'],
            'features' => ['nullable','string'],
            'addons' => ['nullable','array'],
            'addons.*.label' => ['nullable','string','max:255'],
            'addons.*.price' => ['nullable','integer','min:0'],
            'addons.*.unit' => ['nullable','string','max:50'],
            'terms' => ['nullable','string'],
            'overview_image' => ['nullable','image','max:20480'],
            'is_active' => ['boolean'],
            'gallery' => ['nullable','array','max:20'],
            'gallery.*' => ['image','max:20480'],
        ]);

        $features = $this->toArray($data['features'] ?? null, "\n");
        $addons = $this->normalizeAddons($data['addons'] ?? []);

        $package = ServicePackage::create([
            'category_id' => $category->id,
            'name' => $data['name'],
            'price' => $data['price'],
            'max_people' => $data['max_people'] ?? null,
            'description' => $data['description'] ?? null,
            'terms' => $data['terms'] ?? null,
            'is_active' => $data['is_active'] ?? false,
        ]);
        $this->syncFeatures($package, $features);
        $this->syncAddons($package, $addons);

        if ($request->hasFile('overview_image')) {
            $path = $request->file('overview_image')->storePublicly("packages/{$package->id}/overview", 'public');
            $this->syncGalleryItems($package, array_values(array_unique(array_merge([$path], $package->gallery))));
        }

        if ($request->hasFile('gallery')) {
            $paths = [];
            foreach ($request->file('gallery') as $file) {
                $paths[] = $file->storePublicly("packages/{$package->id}/gallery", 'public');
            }
            $this->syncGalleryItems($package, array_slice($paths, 0, 20));
        }

        return redirect()->route('admin.catalog.packages', $category)->with('status', 'Paket ditambahkan.');
    }

    protected function toArray($value, string $delimiter): array
    {
        // Normalisasi input textarea/string menjadi array bersih.
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
        // Pastikan add-on tersimpan sebagai [{label, price}] yang valid.
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

    protected function normalizeCsvAddons($value): array
    {
        return collect($this->toArray($value, ','))
            ->map(function ($raw) {
                [$label, $price] = $this->parseAddonLabelAndPrice($raw);

                return ['label' => $label, 'price' => $price, 'unit' => ''];
            })
            ->values()
            ->all();
    }

    protected function parseAddonLabelAndPrice(string $raw): array
    {
        $raw = trim($raw);

        if (preg_match('/^(.*?)\s*(?:\||:|-)\s*([0-9][0-9\.,]*)$/', $raw, $matches)) {
            return [
                trim($matches[1]) !== '' ? trim($matches[1]) : $raw,
                (int) preg_replace('/[^0-9]/', '', $matches[2]),
            ];
        }

        return [$raw, 0];
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

    protected function syncGalleryItems(ServicePackage $package, array $paths): void
    {
        $package->update([
            'gallery' => array_values(array_filter($paths)),
            'cover_image' => $paths[0] ?? $package->cover_image,
        ]);
    }
}
