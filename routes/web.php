<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PengaduanController;
use App\Http\Controllers\PinjamanController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ChatbotController;
use App\Http\Controllers\Admin\AdminPengaduanController;
use App\Http\Controllers\Admin\AdminPinjamanController;
use App\Http\Controllers\Admin\BarangController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\KategoriController;
use App\Http\Controllers\Admin\GedungController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\InternalHealthController;
use App\Http\Controllers\Admin\MasterDataController;
use App\Http\Controllers\Admin\SchoolMapController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/
Route::redirect('/', '/login')->name('home');

// Temporary one-time database seed route for cloud deployment
Route::get('/seed-production-db-now', function () {
    try {
        \Illuminate\Support\Facades\Artisan::call('db:seed', ['--force' => true]);
        return response()->json([
            'status' => 'success',
            'output' => \Illuminate\Support\Facades\Artisan::output(),
            'users_count' => \App\Models\User::count(),
            'pengaduan_count' => \App\Models\Pengaduan::count(),
        ]);
    } catch (\Throwable $e) {
        return response()->json([
            'status' => 'error',
            'message' => $e->getMessage(),
        ], 500);
    }
});

// Public Track Pengaduan (tanpa login)
Route::get('/track', [PengaduanController::class, 'track'])->name('pengaduan.track');

/*
|--------------------------------------------------------------------------
| Authenticated Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'active', 'force.password.change'])->group(function () {
    
    // Dashboard - routes to appropriate dashboard based on role
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/school-map-files/{map}', [SchoolMapController::class, 'file'])->name('school-map.file');

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
    Route::get('/notifications/dropdown', [NotificationController::class, 'dropdown'])->name('notifications.dropdown');

    Route::get('/api/school-map/active', [SchoolMapController::class, 'activeMap'])->name('api.school-map.active');

    // Chatbot AI
    Route::post('/api/chatbot', [ChatbotController::class, 'chat'])
        ->name('chatbot.chat')
        ->middleware('throttle:30,1');

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

        Route::post('/pengaduan/{pengaduan}/reopen-request', [PengaduanController::class, 'requestReopen'])
            ->name('pengaduan.reopen-request');
        
        // AJAX endpoints
        Route::get('/api/kategori/{kategori}/sub-kategoris', [PengaduanController::class, 'getSubKategoris'])
            ->name('api.sub-kategoris');
        Route::get('/api/gedung/{gedung}/ruangans', [PengaduanController::class, 'getRuangans'])
            ->name('api.ruangans');
        Route::get('/api/pengaduan/check-duplicate', [PengaduanController::class, 'checkDuplicate'])
            ->name('api.pengaduan.check-duplicate')
            ->middleware('throttle:30,1');

        // Pinjaman Barang
        Route::get('/pinjaman', [PinjamanController::class, 'index'])->name('pinjaman.index');
        Route::get('/pinjaman/create', [PinjamanController::class, 'create'])->name('pinjaman.create');
        Route::post('/pinjaman', [PinjamanController::class, 'store'])->name('pinjaman.store');
        Route::get('/pinjaman/{pinjaman}', [PinjamanController::class, 'show'])->name('pinjaman.show');
        Route::post('/pinjaman/{pinjaman}/cancel', [PinjamanController::class, 'cancel'])->name('pinjaman.cancel');
        Route::post('/pinjaman/{pinjaman}/feedback', [PinjamanController::class, 'submitFeedback'])->name('pinjaman.feedback');
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
        Route::post('/pengaduan/{pengaduan}/reschedule', [App\Http\Controllers\Teknisi\TeknisiPengaduanController::class, 'reschedule'])
            ->name('pengaduan.reschedule');
        Route::post('/pengaduan/recompute-queue', [App\Http\Controllers\Teknisi\TeknisiPengaduanController::class, 'recomputeQueue'])
            ->name('pengaduan.recompute-queue');
        Route::post('/pengaduan/{pengaduan}/complete', [App\Http\Controllers\Teknisi\TeknisiPengaduanController::class, 'complete'])
            ->name('pengaduan.complete');
        Route::get('/peta-digital', [App\Http\Controllers\Teknisi\TeknisiSchoolMapController::class, 'index'])
            ->name('peta-digital.index');
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
        Route::post('/pengaduan/{pengaduan}/mark-duplicate', [AdminPengaduanController::class, 'markDuplicate'])->name('pengaduan.mark-duplicate');
        Route::delete('/pengaduan/{pengaduan}/mark-duplicate', [AdminPengaduanController::class, 'unmarkDuplicate'])->name('pengaduan.unmark-duplicate');
        Route::post('/pengaduan/bulk-status', [AdminPengaduanController::class, 'bulkStatus'])->name('pengaduan.bulk-status');
        Route::post('/pengaduan/bulk-assign', [AdminPengaduanController::class, 'bulkAssign'])->name('pengaduan.bulk-assign');
        Route::post('/pengaduan/{pengaduan}/approve-reopen', [AdminPengaduanController::class, 'approveReopen'])->name('pengaduan.approve-reopen');
        Route::post('/pengaduan/{pengaduan}/prioritas', [AdminPengaduanController::class, 'updatePrioritas'])->name('pengaduan.prioritas');
        Route::post('/pengaduan/{pengaduan}/force-priority', [AdminPengaduanController::class, 'forcePriority'])->name('pengaduan.force-priority');
        Route::get('/pengaduan-export', [AdminPengaduanController::class, 'export'])->name('pengaduan.export');
        Route::get('/overload-board', [AdminPengaduanController::class, 'overloadBoard'])->name('overload-board');

        // Pinjaman Management
        Route::resource('barangs', BarangController::class)->except(['create', 'edit', 'show']);
        Route::post('/barangs/{barang}/toggle-status', [BarangController::class, 'toggleStatus'])->name('barangs.toggle-status');
        Route::get('/pinjaman', [AdminPinjamanController::class, 'index'])->name('pinjaman.index');
        Route::get('/pinjaman/{pinjaman}', [AdminPinjamanController::class, 'show'])->name('pinjaman.show');
        Route::post('/pinjaman/{pinjaman}/approve', [AdminPinjamanController::class, 'approve'])->name('pinjaman.approve');
        Route::post('/pinjaman/{pinjaman}/reject', [AdminPinjamanController::class, 'reject'])->name('pinjaman.reject');
        Route::post('/pinjaman/{pinjaman}/checkout', [AdminPinjamanController::class, 'checkOut'])->name('pinjaman.checkout');
        Route::post('/pinjaman/{pinjaman}/checkin', [AdminPinjamanController::class, 'checkIn'])->name('pinjaman.checkin');
        Route::post('/pinjaman/{pinjaman}/force-close', [AdminPinjamanController::class, 'forceClose'])->name('pinjaman.force-close');
        Route::post('/barang/{barang}/adjust-stock', [AdminPinjamanController::class, 'adjustStock'])->name('barang.adjust-stock');
        Route::get('/pinjaman-export', [AdminPinjamanController::class, 'export'])->name('pinjaman.export');

        // Peta Digital Sekolah
        Route::get('/denah', [SchoolMapController::class, 'index'])->name('denah.index');
        Route::post('/denah/chunked', [SchoolMapController::class, 'storeChunked'])->name('denah.store-chunked');
        Route::post('/denah', [SchoolMapController::class, 'store'])->name('denah.store');
        Route::put('/denah/{map}', [SchoolMapController::class, 'update'])->name('denah.update');
        Route::delete('/denah/{map}', [SchoolMapController::class, 'destroy'])->name('denah.destroy');
        Route::get('/denah/{map}/edit', [SchoolMapController::class, 'edit'])->name('denah.edit');
        Route::put('/denah/{map}/layers/{layer}', [SchoolMapController::class, 'updateLayer'])->name('denah.layers.update');
        Route::post('/denah/{map}/layers/{layer}/areas', [SchoolMapController::class, 'storeArea'])->name('denah.layers.areas.store');
        Route::put('/denah/areas/{area}', [SchoolMapController::class, 'updateArea'])->name('denah.areas.update');
        Route::delete('/denah/areas/{area}', [SchoolMapController::class, 'destroyArea'])->name('denah.areas.destroy');
        Route::post('/denah/{map}/activate', [SchoolMapController::class, 'activate'])->name('denah.activate');
        Route::get('/peta-digital', [SchoolMapController::class, 'viewer'])->name('peta-digital');

        Route::get('/internal/health', InternalHealthController::class)->name('internal.health');

        // User Management
        Route::resource('users', UserController::class);
        Route::post('/users/{user}/toggle-status', [UserController::class, 'toggleStatus'])->name('users.toggle-status');
        Route::post('/users/import', [UserController::class, 'import'])->name('users.import');
        Route::get('/users/import/template', [UserController::class, 'importTemplate'])->name('users.import.template');
        Route::post('/users/promote/preview', [UserController::class, 'promotePreview'])->name('users.promote.preview');
        Route::post('/users/promote/apply', [UserController::class, 'promoteApply'])->name('users.promote.apply');
        Route::get('/users-export', [UserController::class, 'export'])->name('users.export');
        Route::get('/master-data', [MasterDataController::class, 'index'])->name('master-data.index');
        Route::post('/master-data/preview', [MasterDataController::class, 'preview'])->name('master-data.preview');
        Route::post('/master-data/commit/{batch}', [MasterDataController::class, 'commit'])->name('master-data.commit');
        Route::post('/master-data/import', [MasterDataController::class, 'import'])->name('master-data.import');
        Route::get('/master-data/error-report/{batch}', [MasterDataController::class, 'errorReport'])->name('master-data.error-report');
        Route::get('/master-data/template', [MasterDataController::class, 'template'])->name('master-data.template');
        Route::get('/master-data/validation', [MasterDataController::class, 'validation'])->name('master-data.validation');

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
        Route::get('/logs/export', [App\Http\Controllers\Admin\LogController::class, 'export'])->name('logs.export');
    });
});

require __DIR__.'/auth.php';
