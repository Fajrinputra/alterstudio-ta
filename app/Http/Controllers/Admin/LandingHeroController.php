<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LandingHeroSlide;
use App\Support\ImageUploadValidation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

/**
 * CRUD slide hero landing page.
 * Sort order ditentukan otomatis (append-last), tidak perlu diisi manual.
 */
class LandingHeroController extends Controller
{
    /** Tampilkan daftar slide hero. */
    public function index()
    {
        $slides = LandingHeroSlide::orderBy('sort_order')->orderBy('slide_code')->get();

        return view('admin.landing.hero.index', compact('slides'));
    }

    /** Form tambah slide baru. */
    public function create()
    {
        $count = LandingHeroSlide::count();

        if ($count >= 10) {
            return redirect()->route('manager.landing.hero')
                ->with('error', 'Maksimal hanya 10 slide hero yang dapat dibuat.');
        }

        return view('admin.landing.hero.create');
    }

    /** Simpan slide baru beserta gambar hero. */
    public function store(Request $request)
    {
        if (LandingHeroSlide::count() >= 10) {
            return redirect()->route('manager.landing.hero')
                ->with('error', 'Maksimal hanya 10 slide hero yang dapat dibuat.');
        }

        $data = $request->validate([
            'eyebrow'  => ['nullable', 'string', 'max:50'],
            'title'    => ['required', 'string', 'max:50'],
            'subtitle' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
            'image'    => ImageUploadValidation::rules(required: true),
        ], ImageUploadValidation::messages(['image']));

        // Auto-assign sort_order — selalu append di urutan terakhir.
        $nextOrder = (LandingHeroSlide::max('sort_order') ?? 0) + 1;

        $imagePath = $request->file('image')->storePublicly('landing/hero', 'public');

        LandingHeroSlide::create([
            'eyebrow'    => $data['eyebrow'] ?? null,
            'title'      => $data['title'],
            'subtitle'   => $data['subtitle'] ?? null,
            'sort_order' => $nextOrder,
            'is_active'  => (bool) ($data['is_active'] ?? false),
            'image_path' => $imagePath,
            'user_id'    => $request->user()?->id,
        ]);

        Cache::forget('landing.page.data.v2');

        return redirect()->route('manager.landing.hero')
            ->with('success', 'Slide hero berhasil ditambahkan.');
    }

    /** Form edit slide yang sudah ada. */
    public function edit(LandingHeroSlide $slide)
    {
        return view('admin.landing.hero.edit', compact('slide'));
    }

    /** Perbarui konten slide (urutan tidak berubah kecuali ada perubahan foto). */
    public function update(Request $request, LandingHeroSlide $slide)
    {
        $data = $request->validate([
            'eyebrow'   => ['nullable', 'string', 'max:50'],
            'title'     => ['required', 'string', 'max:50'],
            'subtitle'  => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
            'image'     => ImageUploadValidation::rules(),
        ], ImageUploadValidation::messages(['image']));

        if ($request->hasFile('image')) {
            // Hapus file lama agar tidak menyisakan orphan file.
            if ($slide->image_path) {
                Storage::disk('public')->delete($slide->image_path);
            }
            $slide->image_path = $request->file('image')->storePublicly('landing/hero', 'public');
        }

        $slide->fill([
            'eyebrow'   => $data['eyebrow'] ?? null,
            'title'     => $data['title'],
            'subtitle'  => $data['subtitle'] ?? null,
            'is_active' => (bool) ($data['is_active'] ?? false),
            'user_id'   => $request->user()?->id,
        ])->save();

        Cache::forget('landing.page.data.v2');

        return redirect()->route('manager.landing.hero')
            ->with('success', 'Slide hero berhasil diperbarui.');
    }

    /** Hapus slide beserta file foto-nya. */
    public function destroy(LandingHeroSlide $slide)
    {
        if ($slide->image_path) {
            Storage::disk('public')->delete($slide->image_path);
        }
        $slide->delete();

        Cache::forget('landing.page.data.v2');

        return redirect()->route('manager.landing.hero')
            ->with('success', 'Slide hero berhasil dihapus.');
    }
}
