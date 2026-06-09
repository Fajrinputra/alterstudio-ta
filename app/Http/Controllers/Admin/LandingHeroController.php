<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LandingHeroSlide;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

/**
 * CRUD slide hero landing page.
 */
class LandingHeroController extends Controller
{
    /** Tampilkan seluruh slide hero untuk dikelola admin. */
    public function index()
    {
        $slides = LandingHeroSlide::orderBy('sort_order')->orderBy('id')->get();
        $usedOrders = $slides->pluck('sort_order')->map(fn ($value) => (int) $value)->all();

        return view('admin.landing.hero', compact('slides', 'usedOrders'));
    }

    /** Simpan slide baru beserta gambar hero. */
    public function store(Request $request)
    {
        if (LandingHeroSlide::count() >= 10) {
            return back()
                ->withErrors(['sort_order' => 'Maksimal hanya 10 slide hero yang dapat dibuat.'])
                ->withInput();
        }

        $data = $request->validate([
            'eyebrow' => ['nullable', 'string', 'max:255'],
            'title' => ['required', 'string', 'max:255'],
            'subtitle' => ['nullable', 'string'],
            'sort_order' => [
                'required',
                'integer',
                'min:1',
                'max:10',
                Rule::unique('landing_hero_slides', 'sort_order'),
            ],
            'is_active' => ['nullable', 'boolean'],
            'image' => ['required', 'image', 'max:20480'],
        ]);

        $imagePath = $request->file('image')->storePublicly('landing/hero', 'public');

        LandingHeroSlide::create([
            'eyebrow' => $data['eyebrow'] ?? null,
            'title' => $data['title'],
            'subtitle' => $data['subtitle'] ?? null,
            'sort_order' => $data['sort_order'],
            'is_active' => (bool) ($data['is_active'] ?? false),
            'image_path' => $imagePath,
            'user_id' => $request->user()?->id,
        ]);

        Cache::forget('landing.page.data.v2');

        return back()->with('success', 'Slide hero berhasil ditambahkan.');
    }

    public function update(Request $request, LandingHeroSlide $slide)
    {
        $data = $request->validate([
            'eyebrow' => ['nullable', 'string', 'max:255'],
            'title' => ['required', 'string', 'max:255'],
            'subtitle' => ['nullable', 'string'],
            'sort_order' => [
                'required',
                'integer',
                'min:1',
                'max:10',
                Rule::unique('landing_hero_slides', 'sort_order')->ignore($slide->id),
            ],
            'is_active' => ['nullable', 'boolean'],
            'image' => ['nullable', 'image', 'max:20480'],
        ]);

        if ($request->hasFile('image')) {
            // Ganti file lama agar tidak menyisakan orphan file.
            Storage::disk('public')->delete($slide->image_path);
            $slide->image_path = $request->file('image')->storePublicly('landing/hero', 'public');
        }

        $slide->fill([
            'eyebrow' => $data['eyebrow'] ?? null,
            'title' => $data['title'],
            'subtitle' => $data['subtitle'] ?? null,
            'sort_order' => $data['sort_order'],
            'is_active' => (bool) ($data['is_active'] ?? false),
            'user_id' => $request->user()?->id,
        ])->save();

        Cache::forget('landing.page.data.v2');

        return back()->with('success', 'Slide hero berhasil diperbarui.');
    }

    public function destroy(LandingHeroSlide $slide)
    {
        // Hapus file fisik dulu, lalu record database.
        Storage::disk('public')->delete($slide->image_path);
        $slide->delete();

        Cache::forget('landing.page.data.v2');

        return back()->with('success', 'Slide hero berhasil dihapus.');
    }
}
