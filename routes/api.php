<?php

declare(strict_types=1);

use App\Modules\Auth\Controllers\AuthController;
use App\Modules\Pinjol\Controllers\PinjolController;
use App\Modules\Laporan\Controllers\LaporanController;
use App\Modules\Ulasan\UlasanController;
use App\Modules\Artikel\Controllers\ArtikelController;
use App\Modules\Simulasi\SimulasiController;
use App\Modules\Regulasi\Controllers\RegulasiController;
use App\Modules\Admin\Controllers\AdminController;
use App\Modules\Health\Controllers\HealthController;
use App\Modules\Docs\Controllers\DocsController; 
use App\Modules\File\Controllers\FileController;
use App\Core\Middleware\OptionalAuthMiddleware;
use App\Core\Middleware\AuthMiddleware;
use App\Core\Middleware\AdminMiddleware;
use App\Core\Middleware\SuperAdminMiddleware;
use App\Core\Routing\Router;

return static function (Router $router): void {

    // ─── Health & Docs ──────────────────────────────────────────────────────────
    $router->get('/health',               [HealthController::class, 'check']);
    // Sesuaikan dengan DocsController yang tadi kita buat (ui dan spec)
    $router->get('/api/docs',             [DocsController::class, 'ui']);
    $router->get('/api/docs/openapi.json', [DocsController::class, 'spec']);

    // ─── Auth ───────────────────────────────────────────────────────────────────
    $router->post('/api/auth/register',        [AuthController::class, 'register']);
    $router->post('/api/auth/login',           [AuthController::class, 'login']);
    $router->post('/api/auth/admin/login',      [AuthController::class, 'adminLogin']);
    $router->post('/api/auth/admin/verify-2fa', [AuthController::class, 'verifyAdminTwoFactor']);
    $router->get('/api/auth/me',               [AuthController::class, 'me'],             [AuthMiddleware::class]);
    $router->put('/api/auth/profile',          [AuthController::class, 'updateProfile'],  [AuthMiddleware::class]);
    $router->get('/api/auth/2fa/setup',        [AuthController::class, 'twoFactorSetup'], [AuthMiddleware::class]);
    $router->post('/api/auth/2fa/confirm',     [AuthController::class, 'confirmTwoFactor'], [AuthMiddleware::class]);
    $router->delete('/api/auth/2fa',           [AuthController::class, 'disableTwoFactor'], [AuthMiddleware::class]);
    $router->post('/api/auth/change-password', [AuthController::class, 'changePassword'], [AuthMiddleware::class]);

    // ─── Pinjol (public) ────────────────────────────────────────────────────────
    $router->get('/api/pinjol/cek',       [PinjolController::class, 'cek']);
    $router->get('/api/pinjol',           [PinjolController::class, 'index']);
    $router->get('/api/pinjol/:id',       [PinjolController::class, 'show']);

    // ─── Laporan ────────────────────────────────────────────────────────────────
    $router->get('/api/laporan/kode/:kode', [LaporanController::class, 'cekStatus']);
    $router->post('/api/laporan',           [LaporanController::class, 'store'], [OptionalAuthMiddleware::class]);
    $router->get('/api/laporan',            [LaporanController::class, 'index'], [AuthMiddleware::class]);
    $router->get('/api/laporan/:id',        [LaporanController::class, 'show'],  [AuthMiddleware::class]);

    // ─── Ulasan ─────────────────────────────────────────────────────────────────
    $router->get('/api/ulasan',     [UlasanController::class, 'index']);
    $router->post('/api/ulasan',    [UlasanController::class, 'store'], [OptionalAuthMiddleware::class]);

    // ─── Artikel ────────────────────────────────────────────────────────────────
    $router->get('/api/artikel/kategori', [ArtikelController::class, 'kategori'], [OptionalAuthMiddleware::class]);
    $router->get('/api/artikel',          [ArtikelController::class, 'index'], [OptionalAuthMiddleware::class]);
    $router->get('/api/artikel/:id',      [ArtikelController::class, 'show'], [OptionalAuthMiddleware::class]);

    // ─── Files ─────────────────────────────────────────────────────────────────
    $router->get('/api/uploads/:path', [FileController::class, 'show']);

    // ─── Simulasi ───────────────────────────────────────────────────────────────
    $router->post('/api/simulasi',           [SimulasiController::class, 'hitung'], [OptionalAuthMiddleware::class]);
    $router->get('/api/simulasi/riwayat',    [SimulasiController::class, 'riwayat'], [AuthMiddleware::class]);
    $router->delete('/api/simulasi/:id',     [SimulasiController::class, 'destroy'], [AuthMiddleware::class]);

    // ─── Regulasi ───────────────────────────────────────────────────────────────
    $router->get('/api/regulasi',     [RegulasiController::class, 'index']);
    $router->get('/api/regulasi/:id', [RegulasiController::class, 'show']);

    // ═══ ADMIN ROUTES (Flat - Tanpa Group) ═══════════════════════════════════════
    $router->get('/api/admin/dashboard', [AdminController::class, 'dashboard'], [AdminMiddleware::class]);
    $router->get('/api/admin/pengaturan',  [AdminController::class, 'pengaturan'], [AdminMiddleware::class]);
    $router->get('/api/admin/pengaturan/weekly-recap-preview',  [AdminController::class, 'weeklyRecapPreview'], [AdminMiddleware::class]);
    $router->put('/api/admin/pengaturan',  [AdminController::class, 'updatePengaturan'], [AdminMiddleware::class]);
    $router->get('/api/admin/users',      [AdminController::class, 'users'], [AdminMiddleware::class]);
    $router->get('/api/admin/users/:id',  [AdminController::class, 'showUser'], [AdminMiddleware::class]);

    $router->post('/api/admin/pinjol',       [PinjolController::class, 'store'], [AdminMiddleware::class]);
    $router->get('/api/admin/pinjol/:id',    [PinjolController::class, 'show'], [AdminMiddleware::class]);
    $router->put('/api/admin/pinjol/:id',    [PinjolController::class, 'update'], [AdminMiddleware::class]);
    $router->delete('/api/admin/pinjol/:id', [PinjolController::class, 'destroy'], [AdminMiddleware::class]);

    $router->get('/api/admin/laporan',            [AdminController::class, 'laporan'], [AdminMiddleware::class]);
    $router->get('/api/admin/laporan/statistik',      [LaporanController::class, 'statistik'], [AdminMiddleware::class]);
    $router->patch('/api/admin/laporan/:id/status',   [LaporanController::class, 'updateStatus'], [AdminMiddleware::class]);
    $router->patch('/api/admin/laporan/:id/reply',    [LaporanController::class, 'reply'], [AdminMiddleware::class]);

    $router->post('/api/admin/artikel',       [ArtikelController::class, 'store'], [AdminMiddleware::class]);
    $router->post('/api/admin/artikel/:id',    [ArtikelController::class, 'update'], [AdminMiddleware::class]);
    $router->delete('/api/admin/artikel/:id', [ArtikelController::class, 'destroy'], [AdminMiddleware::class]);

    $router->post('/api/admin/regulasi',       [RegulasiController::class, 'store'], [AdminMiddleware::class]);
    $router->put('/api/admin/regulasi/:id',    [RegulasiController::class, 'update'], [AdminMiddleware::class]);
    $router->delete('/api/admin/regulasi/:id', [RegulasiController::class, 'destroy'], [AdminMiddleware::class]);

    $router->delete('/api/admin/ulasan/:id', [UlasanController::class, 'destroy'], [AdminMiddleware::class]);

    // Superadmin (Gunakan SuperAdminMiddleware langsung di parameter ketiga)
    $router->get('/api/admin/admins',              [AdminController::class, 'admins'], [SuperAdminMiddleware::class]);
    $router->post('/api/admin/admins',             [AdminController::class, 'createAdmin'], [SuperAdminMiddleware::class]);
    $router->patch('/api/admin/admins/:id/toggle', [AdminController::class, 'toggleAdmin'], [SuperAdminMiddleware::class]);
};
