<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PengaduanController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\Admin\AdminPengaduanController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\KategoriController;
use App\Http\Controllers\Admin\GedungController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\ReportController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/
Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route('dashboard');
    }
    return view('welcome');
})->name('home');

// Public Track Pengaduan (tanpa login)
Route::get('/track', [PengaduanController::class, 'track'])->name('pengaduan.track');

/*
|--------------------------------------------------------------------------
| Authenticated Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'verified', 'active'])->group(function () {
    
    // Dashboard - routes to appropriate dashboard based on role
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Profile
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Notifications
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/{notification}/read', [NotificationController::class, 'markAsRead'])->name('notifications.read');
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllAsRead'])->name('notifications.read-all');
    Route::get('/notifications/unread-count', [NotificationController::class, 'unreadCount'])->name('notifications.unread-count');
    Route::get('/notifications/latest', [NotificationController::class, 'latest'])->name('notifications.latest');

    /*
    |--------------------------------------------------------------------------
    | Siswa & Guru Routes (Pengaduan)
    |--------------------------------------------------------------------------
    */
    Route::middleware(['role:siswa,guru,admin,superadmin'])->group(function () {
        // Pengaduan CRUD
        // Fix for legacy notifications with numeric IDs
        Route::get('/pengaduan/{id}', function ($id) {
            $pengaduan = \App\Models\Pengaduan::find($id);
            return $pengaduan 
                ? redirect()->route('pengaduan.show', $pengaduan) 
                : abort(404);
        })->where('id', '[0-9]+');
        
        Route::resource('pengaduan', PengaduanController::class);
        
        // Delete photo
        Route::delete('/pengaduan/photo/{photo}', [PengaduanController::class, 'deletePhoto'])
            ->name('pengaduan.photo.delete');
        
        // Submit feedback
        Route::post('/pengaduan/{pengaduan}/feedback', [PengaduanController::class, 'submitFeedback'])
            ->name('pengaduan.feedback');
        
        // AJAX endpoints
        Route::get('/api/kategori/{kategori}/sub-kategoris', [PengaduanController::class, 'getSubKategoris'])
            ->name('api.sub-kategoris');
        Route::get('/api/gedung/{gedung}/ruangans', [PengaduanController::class, 'getRuangans'])
            ->name('api.ruangans');
    });

    /*
    |--------------------------------------------------------------------------
    | Teknisi Routes
    |--------------------------------------------------------------------------
    */
    Route::middleware(['role:teknisi,admin,superadmin'])->prefix('teknisi')->name('teknisi.')->group(function () {
        // Fix for accidental double-prefix in notifications
        Route::get('/teknisi/pengaduan/{pengaduan}', function ($pengaduan) {
            return redirect()->route('teknisi.pengaduan.show', $pengaduan);
        });
        
        Route::get('/pengaduan', [App\Http\Controllers\Teknisi\TeknisiPengaduanController::class, 'index'])
            ->name('pengaduan.index');

        // Fix for legacy notifications with numeric IDs
        Route::get('/pengaduan/{id}', function ($id) {
            $pengaduan = \App\Models\Pengaduan::find($id);
            return $pengaduan 
                ? redirect()->route('teknisi.pengaduan.show', $pengaduan) 
                : abort(404);
        })->where('id', '[0-9]+');

        Route::get('/pengaduan/{pengaduan}', [App\Http\Controllers\Teknisi\TeknisiPengaduanController::class, 'show'])
            ->name('pengaduan.show');
        Route::post('/pengaduan/{pengaduan}/update-status', [App\Http\Controllers\Teknisi\TeknisiPengaduanController::class, 'updateStatus'])
            ->name('pengaduan.update-status');
        Route::post('/pengaduan/{pengaduan}/complete', [App\Http\Controllers\Teknisi\TeknisiPengaduanController::class, 'complete'])
            ->name('pengaduan.complete');
    });

    /*
    |--------------------------------------------------------------------------
    | Admin Routes
    |--------------------------------------------------------------------------
    */
    Route::middleware(['role:admin,superadmin'])->prefix('admin')->name('admin.')->group(function () {
        // Pengaduan Management
        Route::get('/pengaduan', [AdminPengaduanController::class, 'index'])->name('pengaduan.index');
        
        // Fix for legacy notifications with numeric IDs
        Route::get('/pengaduan/{id}', function ($id) {
            $pengaduan = \App\Models\Pengaduan::find($id);
            return $pengaduan 
                ? redirect()->route('admin.pengaduan.show', $pengaduan) 
                : abort(404);
        })->where('id', '[0-9]+');

        Route::get('/pengaduan/{pengaduan}', [AdminPengaduanController::class, 'show'])->name('pengaduan.show');
        Route::post('/pengaduan/{pengaduan}/status', [AdminPengaduanController::class, 'updateStatus'])->name('pengaduan.status');
        Route::post('/pengaduan/{pengaduan}/assign', [AdminPengaduanController::class, 'assign'])->name('pengaduan.assign');
        Route::post('/pengaduan/{pengaduan}/reject', [AdminPengaduanController::class, 'reject'])->name('pengaduan.reject');
        Route::post('/pengaduan/bulk-status', [AdminPengaduanController::class, 'bulkStatus'])->name('pengaduan.bulk-status');
        Route::post('/pengaduan/bulk-assign', [AdminPengaduanController::class, 'bulkAssign'])->name('pengaduan.bulk-assign');
        Route::get('/pengaduan-export', [AdminPengaduanController::class, 'export'])->name('pengaduan.export');

        // User Management
        Route::resource('users', UserController::class);
        Route::post('/users/{user}/toggle-status', [UserController::class, 'toggleStatus'])->name('users.toggle-status');
        Route::post('/users/import', [UserController::class, 'import'])->name('users.import');
        Route::get('/users-export', [UserController::class, 'export'])->name('users.export');

        // Kategori Management
        Route::resource('kategoris', KategoriController::class)->except(['show']);
        Route::get('/kategoris/{kategori}/sub-kategoris', [KategoriController::class, 'subKategoris'])->name('kategoris.sub-kategoris');
        Route::post('/kategoris/{kategori}/sub-kategoris', [KategoriController::class, 'storeSubKategori'])->name('kategoris.sub-kategoris.store');
        Route::put('/sub-kategoris/{subKategori}', [KategoriController::class, 'updateSubKategori'])->name('sub-kategoris.update');
        Route::delete('/sub-kategoris/{subKategori}', [KategoriController::class, 'destroySubKategori'])->name('sub-kategoris.destroy');

        // Gedung & Ruangan Management
        Route::resource('gedungs', GedungController::class)->except(['show']);
        Route::get('/gedungs/{gedung}/ruangans', [GedungController::class, 'ruangans'])->name('gedungs.ruangans');
        Route::post('/gedungs/{gedung}/ruangans', [GedungController::class, 'storeRuangan'])->name('gedungs.ruangans.store');
        Route::put('/ruangans/{ruangan}', [GedungController::class, 'updateRuangan'])->name('ruangans.update');
        Route::delete('/ruangans/{ruangan}', [GedungController::class, 'destroyRuangan'])->name('ruangans.destroy');

        // Reports
        Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
        Route::get('/reports/pengaduan', [ReportController::class, 'pengaduan'])->name('reports.pengaduan');
        Route::get('/reports/performance', [ReportController::class, 'performance'])->name('reports.performance');
        Route::get('/reports/export/{type}', [ReportController::class, 'export'])->name('reports.export');
    });

    /*
    |--------------------------------------------------------------------------
    | Kepsek Routes
    |--------------------------------------------------------------------------
    */
    Route::middleware(['role:kepsek,superadmin'])->prefix('kepsek')->name('kepsek.')->group(function () {
        // Redirect to main dashboard for consistency
        Route::get('/dashboard', function () {
            return redirect()->route('dashboard');
        })->name('dashboard');
        
        // Reports page
        Route::get('/reports', [App\Http\Controllers\Kepsek\KepsekDashboardController::class, 'reports'])
            ->name('reports');
    });

    /*
    |--------------------------------------------------------------------------
    | Superadmin Routes
    |--------------------------------------------------------------------------
    */
    Route::middleware(['role:superadmin'])->prefix('superadmin')->name('superadmin.')->group(function () {
        // Settings
        Route::get('/settings', [SettingController::class, 'index'])->name('settings.index');
        Route::post('/settings', [SettingController::class, 'update'])->name('settings.update');
        Route::post('/settings/logo', [SettingController::class, 'uploadLogo'])->name('settings.upload-logo');

        // Logs
        Route::get('/logs', [App\Http\Controllers\Admin\LogController::class, 'index'])->name('logs.index');
    });
});

require __DIR__.'/auth.php';
