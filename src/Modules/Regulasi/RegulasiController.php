<?php

declare(strict_types=1);

namespace App\Modules\Regulasi\Controllers;

use App\Core\Http\{Request, Response};
use App\Core\Database\DatabaseManager;
use OpenApi\Attributes as OA;

#[OA\Tag(name: 'Regulasi', description: 'Manajemen kriteria regulasi dan filter pelanggaran pinjol')]
class RegulasiController
{
    public function __construct(private DatabaseManager $db) {}

    #[OA\Get(
        path: '/api/regulasi',
        summary: 'Daftar kriteria regulasi',
        description: 'Mengambil daftar kriteria regulasi yang digunakan untuk memfilter jenis pelanggaran.',
        tags: ['Regulasi'],
        parameters: [
            new OA\Parameter(
                name: 'aktif',
                in: 'query',
                description: 'Filter status aktif (1 = aktif, 0 = semua)',
                schema: new OA\Schema(type: 'string', default: '1')
            )
        ],
        responses: [
            new OA\Response(response: 200, description: 'Berhasil mengambil daftar regulasi')
        ]
    )]
    public function index(Request $request): Response
    {
        $aktifSaja = $request->input('aktif', '1');
        $where     = '1=1';
        $params    = [];

        if ($aktifSaja === '1') {
            $where .= ' AND r.is_active = 1';
        }

        $data = $this->db->fetchAll(
            "SELECT r.*, a.nama as dibuat_oleh
             FROM `regulasi_filter` r
             LEFT JOIN `admin` a ON r.created_by = a.id_admin
             WHERE {$where}
             ORDER BY r.nama_kriteria ASC",
            $params
        );

        return Response::success($data);
    }

    #[OA\Get(
        path: '/api/regulasi/{id}',
        summary: 'Detail kriteria regulasi',
        tags: ['Regulasi'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))
        ],
        responses: [
            new OA\Response(response: 200, description: 'Berhasil mengambil detail regulasi'),
            new OA\Response(response: 404, description: 'Regulasi tidak ditemukan')
        ]
    )]
    public function show(Request $request): Response
    {
        $id = $request->input('id');

        $row = $this->db->fetchOne(
            'SELECT * FROM `regulasi_filter` WHERE id_regulasi = ?',
            [$id]
        );

        if (!$row) {
            return Response::notFound('Regulasi tidak ditemukan');
        }

        return Response::success($row);
    }

    #[OA\Post(
        path: '/api/admin/regulasi',
        summary: 'Tambah regulasi baru (Admin)',
        security: [['BearerAuth' => []]],
        tags: ['Regulasi'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['nama_kriteria', 'deskripsi'],
                properties: [
                    new OA\Property(property: 'nama_kriteria', type: 'string', example: 'Penyebaran Data Pribadi'),
                    new OA\Property(property: 'deskripsi', type: 'string', example: 'Melakukan penyebaran data kontak darurat tanpa izin.')
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: 'Regulasi berhasil ditambahkan'),
            new OA\Response(response: 422, description: 'Validasi gagal')
        ]
    )]
    public function store(Request $request): Response
    {
        $errors = $request->validate([
            'nama_kriteria' => 'required|min:3|max:255',
            'deskripsi'     => 'required|min:10',
        ]);

        if (!empty($errors)) {
            return Response::error($errors[0], 422);
        }

        $admin = $request->user();
        
        $id = $this->db->insert('regulasi_filter', [
            'nama_kriteria' => sanitize((string)$request->input('nama_kriteria')),
            'deskripsi'     => $request->input('deskripsi'),
            'is_active'     => 1,
            'created_by'    => $admin['id'] ?? null,
            'created_at'    => date('Y-m-d H:i:s'),
            'updated_at'    => date('Y-m-d H:i:s'),
        ]);

        $newRegulasi = $this->db->fetchOne('SELECT * FROM `regulasi_filter` WHERE id_regulasi = ?', [$id]);
        
        return Response::created($newRegulasi, 'Regulasi berhasil ditambahkan');
    }

    #[OA\Put(
        path: '/api/admin/regulasi/{id}',
        summary: 'Update data regulasi (Admin)',
        security: [['BearerAuth' => []]],
        tags: ['Regulasi'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))
        ],
        requestBody: new OA\RequestBody(
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'nama_kriteria', type: 'string'),
                    new OA\Property(property: 'deskripsi', type: 'string'),
                    new OA\Property(property: 'is_active', type: 'integer', enum: [0, 1])
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'Regulasi berhasil diperbarui'),
            new OA\Response(response: 404, description: 'Regulasi tidak ditemukan')
        ]
    )]
    public function update(Request $request): Response
    {
        $id = $request->input('id');
        
        if (!$this->db->fetchOne('SELECT id_regulasi FROM `regulasi_filter` WHERE id_regulasi = ?', [$id])) {
            return Response::notFound('Regulasi tidak ditemukan');
        }

        $allowed = ['nama_kriteria', 'deskripsi', 'is_active'];
        $data    = array_intersect_key($request->all(), array_flip($allowed));
        $data['updated_at'] = date('Y-m-d H:i:s');

        if (isset($data['nama_kriteria'])) {
            $data['nama_kriteria'] = sanitize((string)$data['nama_kriteria']);
        }

        $this->db->update('regulasi_filter', $data, 'id_regulasi = ?', [$id]);
        
        $updated = $this->db->fetchOne('SELECT * FROM `regulasi_filter` WHERE id_regulasi = ?', [$id]);

        return Response::success($updated, 'Regulasi berhasil diperbarui');
    }

    #[OA\Delete(
        path: '/api/admin/regulasi/{id}',
        summary: 'Nonaktifkan regulasi (Admin)',
        description: 'Melakukan soft delete dengan mengubah status is_active menjadi 0.',
        security: [['BearerAuth' => []]],
        tags: ['Regulasi'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))
        ],
        responses: [
            new OA\Response(response: 200, description: 'Regulasi berhasil dinonaktifkan'),
            new OA\Response(response: 404, description: 'Regulasi tidak ditemukan')
        ]
    )]
    public function destroy(Request $request): Response
    {
        $id = $request->input('id');

        if (!$this->db->fetchOne('SELECT id_regulasi FROM `regulasi_filter` WHERE id_regulasi = ?', [$id])) {
            return Response::notFound('Regulasi tidak ditemukan');
        }

        $this->db->update(
            'regulasi_filter', 
            ['is_active' => 0, 'updated_at' => date('Y-m-d H:i:s')], 
            'id_regulasi = ?', 
            [$id]
        );

        return Response::success(null, 'Regulasi berhasil dinonaktifkan');
    }
}