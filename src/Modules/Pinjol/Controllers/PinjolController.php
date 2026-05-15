<?php

declare(strict_types=1);

namespace App\Modules\Pinjol\Controllers;

use App\Core\Http\{Request, Response};
use App\Core\Database\DatabaseManager;
use OpenApi\Attributes as OA;

#[OA\Tag(name: 'Pinjol', description: 'Manajemen data dan verifikasi legalitas pinjaman online')]
class PinjolController
{
    private const STATUS_VALID = ['legal', 'ilegal', 'dalam_pengawasan'];

    public function __construct(private DatabaseManager $db) {}

    #[OA\Get(
        path: '/api/pinjol/cek',
        summary: 'Cek legalitas pinjol berdasarkan nama',
        description: 'Mencari data pinjol untuk memverifikasi status legalitasnya di database.',
        tags: ['Pinjol'],
        parameters: [
            new OA\Parameter(
                name: 'nama',
                in: 'query',
                required: true,
                description: 'Nama pinjol yang ingin dicek',
                schema: new OA\Schema(type: 'string', minLength: 1)
            )
        ],
        responses: [
            new OA\Response(response: 200, description: 'Hasil pencarian ditemukan'),
            new OA\Response(response: 422, description: 'Parameter nama tidak valid')
        ]
    )]
    public function cek(Request $request): Response
    {
        $nama = trim((string) $request->input('nama', ''));
        
        if (empty($nama)) {
            return Response::error('Parameter nama wajib diisi', 422);
        }

        $results = $this->db->fetchAll(
            'SELECT id_pinjol, nama_pinjol, status_pinjol, website, tahun_berdiri, alamat
             FROM `pinjol`
             WHERE nama_pinjol LIKE ?
             ORDER BY nama_pinjol ASC LIMIT 20',
            ["%{$nama}%"]
        );

        $found = count($results) > 0;
        $pesan = match (true) {
            !$found                   => 'Pinjol tidak ditemukan dalam database. Waspadai pinjaman yang tidak terdaftar di OJK.',
            $this->hasIlegal($results) => 'PERINGATAN: Terdapat pinjol ilegal dalam hasil pencarian!',
            default                    => 'Pinjol ditemukan dalam database kami.',
        };

        return Response::success([
            'ditemukan'  => $found,
            'kata_kunci' => $nama,
            'total'      => count($results),
            'hasil'      => $results,
            'pesan'      => $pesan,
        ]);
    }

    private function hasIlegal(array $results): bool
    {
        return count(array_filter($results, fn($r) => $r['status_pinjol'] === 'ilegal')) > 0;
    }

    #[OA\Get(
        path: '/api/pinjol',
        summary: 'Daftar semua pinjol dengan filter',
        tags: ['Pinjol'],
        parameters: [
            new OA\Parameter(name: 'page', in: 'query', schema: new OA\Schema(type: 'integer', default: 1)),
            new OA\Parameter(name: 'per_page', in: 'query', schema: new OA\Schema(type: 'integer', default: 10)),
            new OA\Parameter(name: 'search', in: 'query', description: 'Cari berdasarkan nama, website, atau alamat', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'status', in: 'query', schema: new OA\Schema(type: 'string', enum: ['legal', 'ilegal', 'dalam_pengawasan']))
        ],
        responses: [
            new OA\Response(response: 200, description: 'Berhasil mengambil daftar pinjol')
        ]
    )]
    public function index(Request $request): Response
    {
        $page    = max(1, (int) $request->input('page', 1));
        $perPage = min(100, max(5, (int) $request->input('per_page', 10)));
        $search  = $request->input('search', '');
        $status  = $request->input('status', '');

        $where  = '1=1';
        $params = [];

        if ($search) {
            $where   .= ' AND (nama_pinjol LIKE ? OR website LIKE ? OR alamat LIKE ?)';
            $params[] = "%{$search}%";
            $params[] = "%{$search}%";
            $params[] = "%{$search}%";
        }
        
        if ($status && in_array($status, self::STATUS_VALID)) {
            $where   .= ' AND status_pinjol = ?';
            $params[] = $status;
        }

        $total = $this->db->count('pinjol', $where, $params);
        $offset = ($page - 1) * $perPage;
        
        $data = $this->db->fetchAll(
            "SELECT id_pinjol, nama_pinjol, tahun_berdiri, alamat, website, status_pinjol, created_at
             FROM `pinjol` WHERE {$where} ORDER BY nama_pinjol ASC LIMIT $perPage OFFSET $offset",
            $params
        );

        return Response::json([
            'success' => true,
            'data' => $data,
            'meta' => [
                'current_page' => $page,
                'per_page' => $perPage,
                'total' => $total
            ]
        ]);
    }

    #[OA\Post(
        path: '/api/admin/pinjol',
        summary: 'Tambah data pinjol baru (Admin)',
        security: [['BearerAuth' => []]],
        tags: ['Pinjol'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['nama_pinjol', 'status_pinjol'],
                properties: [
                    new OA\Property(property: 'nama_pinjol', type: 'string', example: 'Pinjam Maju Jaya'),
                    new OA\Property(property: 'status_pinjol', type: 'string', enum: ['legal', 'ilegal', 'dalam_pengawasan']),
                    new OA\Property(property: 'tahun_berdiri', type: 'integer', example: 2020),
                    new OA\Property(property: 'alamat', type: 'string'),
                    new OA\Property(property: 'website', type: 'string', format: 'uri')
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: 'Pinjol berhasil ditambahkan'),
            new OA\Response(response: 409, description: 'Nama pinjol sudah terdaftar'),
            new OA\Response(response: 422, description: 'Validasi gagal')
        ]
    )]
    public function store(Request $request): Response
    {
        $errors = $request->validate([
            'nama_pinjol'   => 'required|min:3|max:255',
            'status_pinjol' => 'required',
        ]);

        if (!empty($errors)) {
            return Response::error($errors[0], 422);
        }

        if (!in_array($request->input('status_pinjol'), self::STATUS_VALID)) {
            return Response::error('Status pinjol tidak valid', 422);
        }

        if ($this->db->fetchOne('SELECT id_pinjol FROM `pinjol` WHERE nama_pinjol = ?', [$request->input('nama_pinjol')])) {
            return Response::error('Nama pinjol sudah terdaftar', 409);
        }

        $admin = $request->user();
        $id = $this->db->insert('pinjol', [
            'nama_pinjol'   => sanitize((string)$request->input('nama_pinjol')),
            'tahun_berdiri' => $request->input('tahun_berdiri'),
            'alamat'        => $request->input('alamat'),
            'website'       => $request->input('website'),
            'status_pinjol' => $request->input('status_pinjol'),
            'created_by'    => $admin['id'] ?? null,
            'created_at'    => date('Y-m-d H:i:s'),
            'updated_at'    => date('Y-m-d H:i:s'),
        ]);

        $newPinjol = $this->db->fetchOne('SELECT * FROM `pinjol` WHERE id_pinjol = ?', [$id]);
        return Response::created($newPinjol, 'Pinjol berhasil ditambahkan');
    }

    #[OA\Put(
        path: '/api/admin/pinjol/{id}',
        summary: 'Update data pinjol (Admin)',
        security: [['BearerAuth' => []]],
        tags: ['Pinjol'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))
        ],
        requestBody: new OA\RequestBody(
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'nama_pinjol', type: 'string'),
                    new OA\Property(property: 'status_pinjol', type: 'string', enum: ['legal', 'ilegal', 'dalam_pengawasan']),
                    new OA\Property(property: 'tahun_berdiri', type: 'integer'),
                    new OA\Property(property: 'alamat', type: 'string'),
                    new OA\Property(property: 'website', type: 'string')
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'Update berhasil'),
            new OA\Response(response: 404, description: 'Pinjol tidak ditemukan')
        ]
    )]
    public function show(Request $request, string $id): Response
    {
        $auth = $request->user();

        if (!$auth || ($auth['type'] ?? '') !== 'admin') {
            return Response::error('Forbidden', 403);
        }

        $pinjol = $this->db->fetchOne(
            'SELECT id_pinjol, nama_pinjol, tahun_berdiri, alamat, website, status_pinjol, created_by, created_at, updated_at
             FROM `pinjol`
             WHERE id_pinjol = ?',
            [$id]
        );

        if (!$pinjol) {
            return Response::notFound('Pinjol tidak ditemukan');
        }

        return Response::success($pinjol);
    }

    public function update(Request $request, string $id): Response
    {
        $auth = $request->user();

        if (!$auth || ($auth['type'] ?? '') !== 'admin') {
            return Response::error('Forbidden', 403);
        }

        if (!$this->db->fetchOne('SELECT id_pinjol FROM `pinjol` WHERE id_pinjol = ?', [$id])) {
            return Response::notFound('Pinjol tidak ditemukan');
        }

        $allowed = ['nama_pinjol','tahun_berdiri','alamat','website','status_pinjol'];
        $data = array_intersect_key($request->all(), array_flip($allowed));

        if (isset($data['status_pinjol']) && !in_array($data['status_pinjol'], self::STATUS_VALID)) {
            return Response::error('Status tidak valid', 422);
        }

        $data['updated_at'] = date('Y-m-d H:i:s');
        $this->db->update('pinjol', $data, 'id_pinjol = ?', [$id]);

        return Response::success(
            $this->db->fetchOne('SELECT * FROM `pinjol` WHERE id_pinjol = ?', [$id]),
            'Pinjol berhasil diperbarui'
        );
    }

    public function destroy(Request $request, string $id): Response
    {
        $auth = $request->user();

        if (!$auth || ($auth['type'] ?? '') !== 'admin') {
            return Response::error('Forbidden', 403);
        }

        $pinjol = $this->db->fetchOne('SELECT id_pinjol FROM `pinjol` WHERE id_pinjol = ?', [$id]);
        if (!$pinjol) {
            return Response::notFound('Pinjol tidak ditemukan');
        }

        $this->db->delete('pinjol', 'id_pinjol = ?', [$id]);

        return Response::success(null, 'Pinjol berhasil dihapus');
    }
}
