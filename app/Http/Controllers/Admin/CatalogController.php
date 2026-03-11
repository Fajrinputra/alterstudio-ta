<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ServiceCategory;
use App\Models\ServicePackage;
use App\Models\StudioLocation;
use Illuminate\Http\Request;

/**
 * Kelola kategori/paket katalog dari sisi admin/manager.
 */
class CatalogController extends Controller
{
    /** Daftar kategori untuk halaman katalog admin. */
    public function index()
    {
        $categories = ServiceCategory::withCount('packages')->orderBy('name')->get();
        return view('admin.catalog.index', compact('categories'));
    }

    /** Tampilan katalog read-only untuk semua role. */
    public function publicIndex()
    {
        $categories = ServiceCategory::with(['packages'])->orderBy('name')->get();
        return view('catalog.index', compact('categories'));
    }

    public function publicShow(ServicePackage $servicePackage)
    {
        // Detail paket pada halaman katalog client (read-only).
        $servicePackage->load('category');
        return view('catalog.show', compact('servicePackage'));
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
            $package = ServicePackage::create([
                'category_id' => $category->id,
                'name' => $pkg['name'],
                'price' => $pkg['price'] ?? 0,
                'description' => $pkg['description'] ?? null,
                'features' => $this->toArray($pkg['features'] ?? null, "\n"),
                'addons' => $this->toArray($pkg['addons'] ?? null, ','),
                'terms' => $pkg['terms'] ?? null,
                'is_active' => true,
            ]);

            // overview image per paket (packages[index][overview_image])
            if ($request->hasFile("packages.$index.overview_image")) {
                $path = $request->file("packages.$index.overview_image")->storePublicly("packages/{$package->id}/overview", 'public');
                $package->update(['overview_image' => $path]);
            }
            // gallery per paket
            if ($request->hasFile("packages.$index.gallery")) {
                $paths = [];
                foreach ($request->file("packages.$index.gallery") as $file) {
                    $paths[] = $file->storePublicly("packages/{$package->id}/gallery", 'public');
                }
                $package->update(['gallery' => array_slice($paths, 0, 20)]);
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
            'terms' => ['nullable','string'],
            'overview_image' => ['nullable','image','max:20480'],
            'is_active' => ['boolean'],
            'gallery' => ['nullable','array','max:20'],
            'gallery.*' => ['image','max:20480'],
        ]);

        $package = ServicePackage::create([
            'category_id' => $category->id,
            'name' => $data['name'],
            'price' => $data['price'],
            'max_people' => $data['max_people'] ?? null,
            'description' => $data['description'] ?? null,
            'features' => $this->toArray($data['features'] ?? null, "\n"),
            'addons' => $this->normalizeAddons($data['addons'] ?? []),
            'terms' => $data['terms'] ?? null,
            'is_active' => $data['is_active'] ?? false,
        ]);

        if ($request->hasFile('overview_image')) {
            $path = $request->file('overview_image')->storePublicly("packages/{$package->id}/overview", 'public');
            $package->update(['overview_image' => $path]);
        }

        if ($request->hasFile('gallery')) {
            $paths = [];
            foreach ($request->file('gallery') as $file) {
                $paths[] = $file->storePublicly("packages/{$package->id}/gallery", 'public');
            }
            $package->update(['gallery' => array_slice($paths, 0, 20)]);
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
                ];
            })
            ->filter()
            ->values()
            ->all();
    }
}
