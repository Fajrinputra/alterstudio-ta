<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ServiceCategory;
use Illuminate\Http\Request;

/**
 * Endpoint CRUD kategori layanan.
 */
class ServiceCategoryController extends Controller
{
    /** List kategori (dipakai oleh UI admin + API ringan). */
    public function index()
    {
        return response()->json(ServiceCategory::orderBy('name')->get());
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
        ]);

        $category = ServiceCategory::create($data);

        if ($request->wantsJson()) {
            return response()->json($category, 201);
        }
        return back()->with('status', 'Kategori ditambahkan.');
    }

    public function update(Request $request, ServiceCategory $serviceCategory)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
        ]);

        $serviceCategory->update($data);

        if ($request->wantsJson()) {
            return response()->json($serviceCategory);
        }
        return back()->with('status', 'Kategori diperbarui.');
    }

    public function destroy(ServiceCategory $serviceCategory)
    {
        // Guard: kategori hanya boleh dihapus jika tidak punya paket aktif/terdaftar.
        if ($serviceCategory->packages()->exists()) {
            $message = 'Kategori tidak bisa dihapus karena masih memiliki paket. Hapus semua paket dalam kategori ini terlebih dahulu.';

            if (request()->wantsJson()) {
                return response()->json(['message' => $message], 422);
            }

            return back()->with('error', $message);
        }

        $serviceCategory->delete();

        if (request()->wantsJson()) {
            return response()->json(['message' => 'deleted']);
        }
        return back()->with('status', 'Kategori dihapus.');
    }
}
