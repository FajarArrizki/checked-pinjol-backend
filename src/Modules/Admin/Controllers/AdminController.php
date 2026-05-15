<?php

declare(strict_types=1);

namespace App\Modules\Admin\Controllers;

use App\Core\Http\{Request, Response};
use App\Core\Database\DatabaseManager;
use OpenApi\Attributes as OA;

class AdminController
{
    public function __construct(private DatabaseManager $db) {}

    #[OA\Get(
        path: '/api/admin/dashboard',
        summary: 'Mengambil ringkasan statistik untuk panel admin',
        tags: ['Admin'],
        security: [['BearerAuth' => []]],
        responses: [
            new OA\Response(response: 200, description: 'Statistik dashboard berhasil dimuat'),
            new OA\Response(response: 401, description: 'Unauthorized')
        ]
    )]
    public function dashboard(Request $request): Response
    {
        $today = date('Y-m-d');

        return Response::success([
            'overview' => [
                'laporan_hari_ini' => $this->db->count('laporan', 'DATE(tanggal_lapor) = ?', [$today]),
                'laporan_tertunda' => $this->db->count('laporan', 'status_laporan = ?', ['menunggu']),
                'aplikasi_baru_hari_ini' => $this->db->count('pinjol', 'DATE(created_at) = ?', [$today]),
                'tindakan_selesai' => $this->db->count('laporan', 'status_laporan = ?', ['selesai']),
                'tingkat_penyelesaian' => $this->calculateCompletionRate(),
            ],
            'total_pinjol'  => [
                'semua'            => $this->db->count('pinjol'),
                'legal'            => $this->db->count('pinjol', 'status_pinjol = ?', ['legal']),
                'ilegal'           => $this->db->count('pinjol', 'status_pinjol = ?', ['ilegal']),
                'dalam_pengawasan' => $this->db->count('pinjol', 'status_pinjol = ?', ['dalam_pengawasan']),
            ],
            'total_laporan' => [
                'semua'    => $this->db->count('laporan'),
                'menunggu' => $this->db->count('laporan', 'status_laporan = ?', ['menunggu']),
                'diproses' => $this->db->count('laporan', 'status_laporan = ?', ['diproses']),
                'selesai'  => $this->db->count('laporan', 'status_laporan = ?', ['selesai']),
                'ditolak'  => $this->db->count('laporan', 'status_laporan = ?', ['ditolak']),
            ],
            'total_user'    => $this->db->count('user'),
            'total_artikel' => $this->db->count('artikel_edukasi'),
            'total_ulasan'  => $this->db->count('ulasan'),
            'laporan_terbaru' => $this->db->fetchAll(
                "SELECT l.id_laporan, l.kode_laporan, l.judul_laporan, l.nama_pelapor, l.status_laporan, l.tanggal_lapor, p.nama_pinjol
                  FROM `laporan` l
                  LEFT JOIN `pinjol` p ON l.id_pinjol = p.id_pinjol
                 ORDER BY l.created_at DESC LIMIT 5"
            ),
            'status_laporan' => [
                'menunggu' => $this->db->count('laporan', 'status_laporan = ?', ['menunggu']),
                'diproses' => $this->db->count('laporan', 'status_laporan = ?', ['diproses']),
                'selesai'  => $this->db->count('laporan', 'status_laporan = ?', ['selesai']),
                'ditolak'  => $this->db->count('laporan', 'status_laporan = ?', ['ditolak']),
            ],
        ]);
    }

    private function calculateCompletionRate(): int
    {
        $total = $this->db->count('laporan');
        if ($total <= 0) {
            return 0;
        }

        $selesai = $this->db->count('laporan', 'status_laporan = ?', ['selesai']);
        return (int) round(($selesai / $total) * 100);
    }

    #[OA\Get(
        path: '/api/admin/laporan',
        summary: 'Daftar laporan untuk dashboard admin dengan pencarian dan pagination',
        tags: ['Admin'],
        security: [['BearerAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'page', in: 'query', schema: new OA\Schema(type: 'integer', default: 1)),
            new OA\Parameter(name: 'per_page', in: 'query', schema: new OA\Schema(type: 'integer', default: 10)),
            new OA\Parameter(name: 'search', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'status', in: 'query', schema: new OA\Schema(type: 'string'))
        ],
        responses: [
            new OA\Response(response: 200, description: 'Daftar laporan berhasil dimuat')
        ]
    )]
    public function laporan(Request $request): Response
    {
        $page    = max(1, (int) $request->input('page', 1));
        $perPage = min(100, max(5, (int) $request->input('per_page', 10)));
        $search  = trim((string) $request->input('search', ''));
        $status  = trim((string) $request->input('status', ''));
        $offset  = ($page - 1) * $perPage;

        $where = '1=1';
        $params = [];

        if ($search !== '') {
            $where .= ' AND (l.kode_laporan LIKE ? OR l.judul_laporan LIKE ? OR l.nama_pelapor LIKE ? OR p.nama_pinjol LIKE ?)';
            $like = '%' . $search . '%';
            array_push($params, $like, $like, $like, $like);
        }

        if ($status !== '' && in_array($status, ['menunggu', 'diproses', 'selesai', 'ditolak'], true)) {
            $where .= ' AND l.status_laporan = ?';
            $params[] = $status;
        }

        $total = $this->db->fetchOne(
            "SELECT COUNT(*) AS total
             FROM `laporan` l
             LEFT JOIN `pinjol` p ON p.id_pinjol = l.id_pinjol
             WHERE {$where}",
            $params
        );

        $data = $this->db->fetchAll(
            "SELECT l.id_laporan, l.kode_laporan, l.judul_laporan, l.nama_pelapor, l.kontak_pelapor,
                    l.email_pelapor, l.tautan_aplikasi, l.foto_bukti, l.status_laporan, l.tanggal_lapor,
                    l.id_pinjol, p.nama_pinjol, p.status_pinjol
             FROM `laporan` l
             LEFT JOIN `pinjol` p ON p.id_pinjol = l.id_pinjol
             WHERE {$where}
             ORDER BY l.created_at DESC
             LIMIT $perPage OFFSET $offset",
            $params
        );

        return Response::success([
            'data' => $data,
            'meta' => [
                'current_page' => $page,
                'per_page' => $perPage,
                'total' => (int) ($total['total'] ?? 0),
                'total_pages' => (int) max(1, ceil(((int) ($total['total'] ?? 0)) / $perPage)),
            ],
        ]);
    }

    #[OA\Get(
        path: '/api/admin/users',
        summary: 'Manajemen daftar user dengan fitur pencarian dan pagination',
        tags: ['Admin'],
        security: [['BearerAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'page', in: 'query', schema: new OA\Schema(type: 'integer', default: 1)),
            new OA\Parameter(name: 'per_page', in: 'query', schema: new OA\Schema(type: 'integer', default: 15)),
            new OA\Parameter(name: 'search', in: 'query', schema: new OA\Schema(type: 'string'))
        ],
        responses: [
            new OA\Response(response: 200, description: 'Daftar user ditemukan')
        ]
    )]
    public function users(Request $request): Response
    {
        $page    = max(1, (int) $request->input('page', 1));
        $perPage = min(100, max(5, (int) $request->input('per_page', 15)));
        $search  = $request->input('search', '');
        $offset  = ($page - 1) * $perPage;

        $where  = '1=1';
        $params = [];
        if ($search) {
            $where   .= ' AND (nama LIKE ? OR email LIKE ? OR no_hp LIKE ?)';
            $params[] = "%{$search}%";
            $params[] = "%{$search}%";
            $params[] = "%{$search}%";
        }

        $total = $this->db->count('user', $where, $params);
        $data  = $this->db->fetchAll(
            "SELECT id_user, nama, email, no_hp, created_at 
             FROM `user` 
             WHERE {$where} 
             ORDER BY created_at DESC 
             LIMIT $perPage OFFSET $offset",
            $params
        );

        return Response::json([
            'success' => true,
            'data'    => $data,
            'meta'    => [
                'current_page' => $page,
                'per_page'     => $perPage,
                'total'        => $total
            ]
        ]);
    }

    #[OA\Get(
        path: '/api/admin/users/{id}',
        summary: 'Detail profil user beserta statistik aktivitasnya',
        tags: ['Admin'],
        security: [['BearerAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))
        ],
        responses: [
            new OA\Response(response: 200, description: 'Detail user ditemukan'),
            new OA\Response(response: 404, description: 'User tidak ditemukan')
        ]
    )]
    public function showUser(Request $request): Response
    {
        $id   = $request->input('id');
        $user = $this->db->fetchOne(
            'SELECT id_user, nama, email, no_hp, created_at, updated_at 
             FROM `user` WHERE id_user = ?', 
            [$id]
        );
        
        if (!$user) return Response::notFound('User tidak ditemukan');

        $user['total_laporan']  = $this->db->count('laporan', 'id_user = ?', [$id]);
        $user['total_ulasan']   = $this->db->count('ulasan', 'id_user = ?', [$id]);
        $user['total_simulasi'] = $this->db->count('simulasi_pinjaman', 'id_user = ?', [$id]);

        return Response::success($user);
    }

    #[OA\Get(
        path: '/api/admin/admins',
        summary: 'Daftar semua administrator (Superadmin Only)',
        tags: ['Admin'],
        security: [['BearerAuth' => []]],
        responses: [
            new OA\Response(response: 200, description: 'Daftar admin dimuat')
        ]
    )]
    public function admins(Request $request): Response
    {
        $data = $this->db->fetchAll(
            'SELECT id_admin, nama, email, username, role, no_hp, is_active, created_at 
             FROM `admin` ORDER BY created_at DESC'
        );
        return Response::success($data);
    }

    #[OA\Post(
        path: '/api/admin/admins',
        summary: 'Membuat akun administrator baru',
        tags: ['Admin'],
        security: [['BearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['nama', 'email', 'username', 'password', 'role'],
                properties: [
                    new OA\Property(property: 'nama', type: 'string', example: 'Admin Baru'),
                    new OA\Property(property: 'email', type: 'string', format: 'email', example: 'admin@mail.com'),
                    new OA\Property(property: 'username', type: 'string', example: 'admin_dev'),
                    new OA\Property(property: 'password', type: 'string', format: 'password', example: 'rahasia123'),
                    new OA\Property(property: 'role', type: 'string', example: 'admin'),
                    new OA\Property(property: 'no_hp', type: 'string', example: '0812345678')
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: 'Admin berhasil dibuat'),
            new OA\Response(response: 409, description: 'Email atau username sudah digunakan'),
            new OA\Response(response: 422, description: 'Validasi gagal')
        ]
    )]
    public function createAdmin(Request $request): Response
    {
        $errors = $request->validate([
            'nama'     => 'required|min:3',
            'email'    => 'required|email',
            'username' => 'required|min:3',
            'password' => 'required|min:6',
            'role'     => 'required',
        ]);

        if (!empty($errors)) {
            return Response::error($errors[0], 422);
        }

        if ($this->db->fetchOne('SELECT id_admin FROM `admin` WHERE email = ? OR username = ?', [
            $request->input('email'), $request->input('username')
        ])) {
            return Response::error('Email atau username sudah digunakan', 409);
        }

        $id = $this->db->insert('admin', [
            'nama'          => sanitize((string)$request->input('nama')),
            'email'         => $request->input('email'),
            'username'      => $request->input('username'),
            'password_hash' => password_hash((string)$request->input('password'), PASSWORD_BCRYPT),
            'role'          => $request->input('role'),
            'no_hp'         => $request->input('no_hp'),
            'is_active'     => 1,
            'created_at'    => date('Y-m-d H:i:s'),
            'updated_at'    => date('Y-m-d H:i:s'),
        ]);

        $admin = $this->db->fetchOne(
            'SELECT id_admin, nama, email, username, role, no_hp, is_active 
             FROM `admin` WHERE id_admin = ?', 
            [$id]
        );
        
        return Response::created($admin, 'Admin berhasil dibuat');
    }

    #[OA\Patch(
        path: '/api/admin/admins/{id}/toggle',
        summary: 'Mengubah status aktif/nonaktif akun admin',
        tags: ['Admin'],
        security: [['BearerAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))
        ],
        responses: [
            new OA\Response(response: 200, description: 'Status berhasil diperbarui'),
            new OA\Response(response: 404, description: 'Admin tidak ditemukan')
        ]
    )]
    public function toggleAdmin(Request $request): Response
    {
        $id    = $request->input('id');
        $admin = $this->db->fetchOne('SELECT * FROM `admin` WHERE id_admin = ?', [$id]);
        
        if (!$admin) return Response::notFound('Admin tidak ditemukan');

        $newStatus = $admin['is_active'] ? 0 : 1;
        $this->db->update('admin', [
            'is_active'  => $newStatus, 
            'updated_at' => date('Y-m-d H:i:s')
        ], 'id_admin = ?', [$id]);

        return Response::success(
            ['is_active' => $newStatus],
            $newStatus ? 'Admin diaktifkan' : 'Admin dinonaktifkan'
        );
    }

    #[OA\Get(
        path: '/api/admin/pengaturan',
        summary: 'Mengambil pengaturan dashboard admin saat ini',
        tags: ['Admin'],
        security: [['BearerAuth' => []]],
        responses: [
            new OA\Response(response: 200, description: 'Pengaturan berhasil dimuat')
        ]
    )]
    public function pengaturan(Request $request): Response
    {
        $auth     = $request->user();
        $settings = $this->db->fetchOne(
            'SELECT * FROM `pengaturan_admin` WHERE id_admin = ?', 
            [$auth['id']]
        );

        if (!$settings) {
            $id = $this->db->insert('pengaturan_admin', [
                'id_admin'            => $auth['id'],
                'email_alert_darurat' => 1,
                'ringkasan_laporan'   => 1,
                'two_factor_enabled'  => 0,
                'updated_at'          => date('Y-m-d H:i:s'),
            ]);
            $settings = $this->db->fetchOne('SELECT * FROM `pengaturan_admin` WHERE id_pengaturan = ?', [$id]);
        }

        return Response::success($settings);
    }

    public function weeklyRecapPreview(Request $request): Response
    {
        $weekAgo = date('Y-m-d H:i:s', strtotime('-7 days'));

        $overview = [
            'laporan_masuk_7_hari' => $this->db->count('laporan', 'created_at >= ?', [$weekAgo]),
            'laporan_diproses_7_hari' => $this->db->count('laporan', 'updated_at >= ? AND status_laporan = ?', [$weekAgo, 'diproses']),
            'laporan_selesai_7_hari' => $this->db->count('laporan', 'updated_at >= ? AND status_laporan = ?', [$weekAgo, 'selesai']),
            'laporan_ditolak_7_hari' => $this->db->count('laporan', 'updated_at >= ? AND status_laporan = ?', [$weekAgo, 'ditolak']),
        ];

        $checkedReports = $this->db->fetchAll(
            "SELECT id_laporan, kode_laporan, judul_laporan, status_laporan, updated_at
             FROM `laporan`
             WHERE updated_at >= ?
               AND status_laporan IN ('diproses', 'selesai', 'ditolak')
             ORDER BY updated_at DESC
             LIMIT 10",
            [$weekAgo]
        );

        return Response::success([
            'overview' => $overview,
            'checked_reports' => $checkedReports,
        ]);
    }

    #[OA\Put(
        path: '/api/admin/pengaturan',
        summary: 'Memperbarui pengaturan panel admin',
        tags: ['Admin'],
        security: [['BearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'email_alert_darurat', type: 'integer', example: 1),
                    new OA\Property(property: 'ringkasan_laporan', type: 'integer', example: 1),
                    new OA\Property(property: 'two_factor_enabled', type: 'integer', example: 0)
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'Pengaturan berhasil disimpan')
        ]
    )]
    public function updatePengaturan(Request $request): Response
    {
        $auth    = $request->user();
        $allowed = ['email_alert_darurat', 'ringkasan_laporan', 'two_factor_enabled'];
        $data    = array_intersect_key($request->all(), array_flip($allowed));
        $data['updated_at'] = date('Y-m-d H:i:s');

        $existing = $this->db->fetchOne('SELECT id_pengaturan FROM `pengaturan_admin` WHERE id_admin = ?', [$auth['id']]);
        
        if ($existing) {
            $this->db->update('pengaturan_admin', $data, 'id_admin = ?', [$auth['id']]);
        } else {
            $data['id_admin'] = $auth['id'];
            $this->db->insert('pengaturan_admin', $data);
        }

        return Response::success(
            $this->db->fetchOne('SELECT * FROM `pengaturan_admin` WHERE id_admin = ?', [$auth['id']]),
            'Pengaturan berhasil disimpan'
        );
    }
}
