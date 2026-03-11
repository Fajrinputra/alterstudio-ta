<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LandingController;
use Illuminate\Support\Facades\Route;

// Landing publik.
Route::get('/', LandingController::class);

// Detail cabang publik untuk guest/user login.
Route::get('/locations/{studioLocation}', [\App\Http\Controllers\Guest\LocationPublicController::class, 'show'])
    ->name('locations.public.show');

// Dashboard berbasis role setelah login.
Route::get('/dashboard', DashboardController::class)
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/catalog', [\App\Http\Controllers\Admin\CatalogController::class, 'publicIndex'])->name('catalog.public');
    Route::get('/catalog/packages/{servicePackage}', [\App\Http\Controllers\Admin\CatalogController::class, 'publicShow'])->name('catalog.package.show');

    // Profil user
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile.edit');
    Route::get('/profile/edit', [ProfileController::class, 'edit'])->name('profile.form');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Client booking flow
    Route::middleware('role:CLIENT')->group(function () {
        Route::get('/bookings/create', [\App\Http\Controllers\BookingController::class, 'create'])->name('bookings.create');
        Route::post('/bookings', [\App\Http\Controllers\BookingController::class, 'store'])->name('bookings.store');
        Route::get('/bookings', [\App\Http\Controllers\BookingController::class, 'index'])->name('bookings.index');
        Route::get('/bookings/{booking}', [\App\Http\Controllers\BookingController::class, 'show'])->name('bookings.show');
        Route::get('/bookings/{booking}/pay', [\App\Http\Controllers\BookingController::class, 'pay'])->name('bookings.pay');
        Route::post('/bookings/{booking}/pay', [\App\Http\Controllers\PaymentController::class, 'createSnap'])->name('bookings.pay.snap');
        Route::post('/bookings/{booking}/pay/confirm', [\App\Http\Controllers\PaymentController::class, 'confirm'])->name('bookings.pay.confirm');

        Route::post('/projects/{project}/selections', [\App\Http\Controllers\PhotoSelectionController::class, 'store']);
        Route::post('/projects/{project}/selections/finalize', [\App\Http\Controllers\PhotoSelectionController::class, 'finalize'])->name('projects.selections.finalize');
        Route::post('/projects/{project}/revision-pins', [\App\Http\Controllers\RevisionPinController::class, 'store']);
        Route::get('/projects/{project}/raw-download', [\App\Http\Controllers\MediaAssetController::class, 'downloadRaw'])->name('projects.raw.download');
    });

    // Avatar upload
    Route::post('/profile/avatar', \App\Http\Controllers\ProfileAvatarController::class)->name('profile.avatar');
    Route::post('/profile/avatar/delete', [\App\Http\Controllers\ProfileAvatarController::class, 'destroy'])->name('profile.avatar.delete');

    // Admin / Manager / Owner
    Route::middleware('role:ADMIN,MANAGER')->group(function () {
        Route::get('/admin/bookings', [\App\Http\Controllers\BookingController::class, 'index']);
        Route::post('/admin/bookings/{booking}/status', [\App\Http\Controllers\BookingController::class, 'updateStatus'])->name('admin.bookings.status');
        Route::post('/projects/{project}/schedule', [\App\Http\Controllers\ScheduleController::class, 'store']);
        Route::put('/projects/{project}/schedule', [\App\Http\Controllers\ScheduleController::class, 'update'])->name('projects.schedule.update');
        Route::delete('/projects/{project}/schedule', [\App\Http\Controllers\ScheduleController::class, 'destroy'])->name('projects.schedule.destroy');
        Route::get('/payroll', [\App\Http\Controllers\PayrollController::class, 'index'])->name('payroll.index');

        // Kelola pengguna
        Route::get('/admin/users', [\App\Http\Controllers\UserManagementController::class, 'index'])->name('admin.users.index');
        Route::get('/admin/users/create', [\App\Http\Controllers\UserManagementController::class, 'create'])->name('admin.users.create');
        Route::get('/admin/users/{user}/edit', [\App\Http\Controllers\UserManagementController::class, 'edit'])->name('admin.users.edit');
        Route::post('/admin/users', [\App\Http\Controllers\UserManagementController::class, 'store'])->name('admin.users.store');
        Route::put('/admin/users/{user}', [\App\Http\Controllers\UserManagementController::class, 'update'])->name('admin.users.update');
        Route::post('/admin/users/{user}/toggle', [\App\Http\Controllers\UserManagementController::class, 'toggle'])->name('admin.users.toggle');
        Route::delete('/admin/users/{user}', [\App\Http\Controllers\UserManagementController::class, 'destroy'])->name('admin.users.destroy');

        // Kelola katalog (view)
        Route::get('/admin/catalog', [\App\Http\Controllers\Admin\CatalogController::class, 'index'])->name('admin.catalog');
        Route::get('/admin/catalog/create', [\App\Http\Controllers\Admin\CatalogController::class, 'create'])->name('admin.catalog.create');
        Route::post('/admin/catalog', [\App\Http\Controllers\Admin\CatalogController::class, 'store'])->name('admin.catalog.store');
        Route::get('/admin/catalog/{category}', [\App\Http\Controllers\Admin\CatalogController::class, 'packages'])->name('admin.catalog.packages');
        Route::get('/admin/catalog/{category}/packages/create', [\App\Http\Controllers\Admin\CatalogController::class, 'createPackage'])->name('admin.catalog.packages.create');
        Route::post('/admin/catalog/{category}/packages', [\App\Http\Controllers\Admin\CatalogController::class, 'storePackage'])->name('admin.catalog.packages.store');

        // Kelola kategori & paket
        Route::get('/admin/categories', [\App\Http\Controllers\Admin\ServiceCategoryController::class, 'index']);
        Route::post('/admin/categories', [\App\Http\Controllers\Admin\ServiceCategoryController::class, 'store']);
        Route::put('/admin/categories/{serviceCategory}', [\App\Http\Controllers\Admin\ServiceCategoryController::class, 'update']);
        Route::delete('/admin/categories/{serviceCategory}', [\App\Http\Controllers\Admin\ServiceCategoryController::class, 'destroy']);

        Route::get('/admin/packages', [\App\Http\Controllers\Admin\ServicePackageController::class, 'index']);
        Route::get('/admin/packages/{servicePackage}', [\App\Http\Controllers\Admin\ServicePackageController::class, 'show'])->name('admin.packages.show');
        Route::get('/admin/packages/{servicePackage}/edit', [\App\Http\Controllers\Admin\ServicePackageController::class, 'edit'])->name('admin.packages.edit');
        Route::post('/admin/packages', [\App\Http\Controllers\Admin\ServicePackageController::class, 'store'])->name('admin.packages.store');
        Route::put('/admin/packages/{servicePackage}', [\App\Http\Controllers\Admin\ServicePackageController::class, 'update'])->name('admin.packages.update');
        Route::delete('/admin/packages/{servicePackage}', [\App\Http\Controllers\Admin\ServicePackageController::class, 'destroy'])->name('admin.packages.destroy');

        // Kelola lokasi
        Route::get('/admin/locations', [\App\Http\Controllers\Admin\StudioLocationController::class, 'index']);
        Route::get('/admin/locations/manage', [\App\Http\Controllers\Admin\StudioLocationController::class, 'manage'])->name('admin.locations.manage');
        Route::post('/admin/locations', [\App\Http\Controllers\Admin\StudioLocationController::class, 'store']);
        Route::put('/admin/locations/{studioLocation}', [\App\Http\Controllers\Admin\StudioLocationController::class, 'update']);
        Route::delete('/admin/locations/{studioLocation}', [\App\Http\Controllers\Admin\StudioLocationController::class, 'destroy']);
        Route::post('/admin/locations/room', [\App\Http\Controllers\Admin\StudioLocationController::class, 'storeRoom'])->name('admin.locations.room.store');
    });

    Route::middleware('role:ADMIN')->group(function () {
        Route::get('/admin/landing/hero', [\App\Http\Controllers\Admin\LandingHeroController::class, 'index'])->name('admin.landing.hero');
        Route::post('/admin/landing/hero', [\App\Http\Controllers\Admin\LandingHeroController::class, 'store'])->name('admin.landing.hero.store');
        Route::put('/admin/landing/hero/{slide}', [\App\Http\Controllers\Admin\LandingHeroController::class, 'update'])->name('admin.landing.hero.update');
        Route::delete('/admin/landing/hero/{slide}', [\App\Http\Controllers\Admin\LandingHeroController::class, 'destroy'])->name('admin.landing.hero.destroy');
    });

    // Schedules view for admin/manager/photographer/editor
    Route::middleware('role:ADMIN,MANAGER,PHOTOGRAPHER,EDITOR')->group(function () {
        Route::get('/admin/schedules', [\App\Http\Controllers\ScheduleController::class, 'index'])->name('admin.schedules');
    });

    // Editor & Photographer uploads
    Route::middleware('role:EDITOR,PHOTOGRAPHER,ADMIN')->group(function () {
        Route::post('/projects/{project}/assets', [\App\Http\Controllers\MediaAssetController::class, 'store']);
        Route::post('/revision-pins/{revisionPin}/resolve', [\App\Http\Controllers\RevisionPinController::class, 'resolve']);
        Route::get('/projects/{project}', [\App\Http\Controllers\ProjectController::class, 'show'])->name('projects.show');
    });
});

require __DIR__.'/auth.php';

// Midtrans webhook (no auth)
Route::post('/midtrans/webhook', [\App\Http\Controllers\PaymentController::class, 'webhook']);
