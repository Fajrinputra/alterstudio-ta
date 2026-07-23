<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LandingHeroSlide;
use App\Support\ImageUploadValidation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

/**
 * Controller untuk mengelola Slide Hero pada Landing Page.
 *
 * Bertanggung jawab atas:
 * - Menampilkan daftar slide yang sudah ada (index)
 * - Form tambah slide baru (create)
 * - Menyimpan slide baru dengan foto (store)
 * - Form edit slide yang ada (edit)
 * - Memperbarui konten/foto slide (update)
 * - Menghapus slide beserta foto fisiknya (destroy)
 *
 * Catatan penting:
 * - Sort order ditentukan OTOMATIS sebagai (max sort_order + 1).
 *   Manajer tidak perlu mengisi sort order secara manual.
 * - Batas maksimal slide: 10 slide.
 * - Setiap perubahan harus menghapus cache 'landing.page.data.v2'
 *   agar halaman landing page tidak menampilkan data lama.
 * - File foto disimpan di storage/public/landing/hero/.
 */
class LandingHeroController extends Controller
{
    /**
     * Menampilkan halaman daftar seluruh slide hero.
     * Slide diurutkan berdasarkan sort_order, lalu slide_code sebagai tiebreaker.
     */
    public function index()
    {
        $slides = LandingHeroSlide::orderBy('sort_order')->orderBy('slide_code')->get();

        return view('admin.landing.hero.index', compact('slides'));
    }

    /**
     * Menampilkan form tambah slide baru.
     * Jika sudah ada 10 slide (batas maksimal), redirect ke index dengan pesan error.
     */
    public function create()
    {
        $count = LandingHeroSlide::count();

        // Tolak pembuatan slide ke-11 dan seterusnya.
        if ($count >= 10) {
            return redirect()->route('manager.landing.hero')
                ->with('error', 'Maksimal hanya 10 slide hero yang dapat dibuat.');
        }

        return view('admin.landing.hero.create');
    }

    /**
     * Menyimpan slide hero baru ke database.
     *
     * Alur:
     * 1. Tolak jika sudah ada 10 slide (double-check di server).
     * 2. Validasi input teks dan file foto.
     * 3. Tentukan sort_order secara otomatis: (nilai tertinggi saat ini + 1).
     * 4. Upload foto ke storage/public/landing/hero/.
     * 5. Simpan record slide ke database.
     * 6. Hapus cache landing page.
     */
    public function store(Request $request)
    {
        // Double-check di sisi server — tidak hanya mengandalkan cek di create().
        if (LandingHeroSlide::count() >= 10) {
            return redirect()->route('manager.landing.hero')
                ->with('error', 'Maksimal hanya 10 slide hero yang dapat dibuat.');
        }

        $data = $request->validate([
            'eyebrow'  => ['nullable', 'string', 'max:50'],
            'title'    => ['required', 'string', 'max:50'],
            'subtitle' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
            'image'    => ImageUploadValidation::rules(required: true), // Foto wajib di create.
        ], ImageUploadValidation::messages(['image']));

        // Sort order otomatis: ambil nilai tertinggi + 1 (append ke akhir list).
        $nextOrder = (LandingHeroSlide::max('sort_order') ?? 0) + 1;

        // Simpan file foto ke folder landing/hero/ di disk public.
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

        // Invalidate cache agar landing page menampilkan slide terbaru.
        Cache::forget('landing.page.data.v2');

        return redirect()->route('manager.landing.hero')
            ->with('success', 'Slide hero berhasil ditambahkan.');
    }

    /**
     * Menampilkan form edit slide yang sudah ada.
     * Sort order tidak bisa diubah — ini disengaja agar urutan slide stabil.
     */
    public function edit(LandingHeroSlide $slide)
    {
        return view('admin.landing.hero.edit', compact('slide'));
    }

    /**
     * Memperbarui konten slide (teks dan opsional foto baru).
     *
     * Sort order TIDAK diubah — manajer tidak perlu mengisi ulang urutan.
     * Foto lama dihapus dari storage jika ada foto baru yang diupload,
     * untuk menghindari penumpukan file orphan di server.
     */
    public function update(Request $request, LandingHeroSlide $slide)
    {
        $data = $request->validate([
            'eyebrow'   => ['nullable', 'string', 'max:50'],
            'title'     => ['required', 'string', 'max:50'],
            'subtitle'  => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
            'image'     => ImageUploadValidation::rules(), // Foto opsional saat edit.
        ], ImageUploadValidation::messages(['image']));

        if ($request->hasFile('image')) {
            // Hapus foto lama dari storage sebelum menyimpan foto baru.
            // Ini mencegah penumpukan file yang tidak terpakai di server.
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
            'user_id'   => $request->user()?->id, // Catat siapa yang terakhir mengubah.
        ])->save();

        Cache::forget('landing.page.data.v2');

        return redirect()->route('manager.landing.hero')
            ->with('success', 'Slide hero berhasil diperbarui.');
    }

    /**
     * Menghapus slide hero beserta file foto fisiknya dari server.
     * File foto harus dihapus manual karena Laravel tidak menghapus file
     * secara otomatis saat record model didelete dari database.
     */
    public function destroy(LandingHeroSlide $slide)
    {
        // Hapus file foto dari storage terlebih dahulu.
        if ($slide->image_path) {
            Storage::disk('public')->delete($slide->image_path);
        }

        // Hapus record dari database.
        $slide->delete();

        Cache::forget('landing.page.data.v2');

        return redirect()->route('manager.landing.hero')
            ->with('success', 'Slide hero berhasil dihapus.');
    }
}
