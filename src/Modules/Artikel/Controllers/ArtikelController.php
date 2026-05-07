<?php

declare(strict_types=1);

namespace App\Modules\Artikel\Controllers;

use App\Core\Http\{Request, Response};
use App\Core\Database\DatabaseManager;
use OpenApi\Attributes as OA;

#[OA\Tag(name: 'Artikel', description: 'Konten edukasi dan tips terkait pinjaman online')]
class ArtikelController
{
    public function __construct(private DatabaseManager $db) {}

    #[OA\Get(
        path: '/api/artikel',
        summary: 'Daftar artikel edukasi (Public)',
        tags: ['Artikel'],
        parameters: [
            new OA\Parameter(name: 'page', in: 'query', schema: new OA\Schema(type: 'integer', default: 1)),
            new OA\Parameter(name: 'per_page', in: 'query', schema: new OA\Schema(type: 'integer', default: 10)),
            new OA\Parameter(name: 'kategori', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'search', in: 'query', schema: new OA\Schema(type: 'string'))
        ],
        responses: [
            new OA\Response(response: 200, description: 'Berhasil mengambil daftar artikel')
        ]
    )]
    public function index(Request $request): Response
    {
        $page     = max(1, (int) $request->input('page', 1));
        $perPage  = min(50, max(5, (int) $request->input('per_page', 10)));
        $kategori = $request->input('kategori', '');
        $search   = $request->input('search', '');
        $offset   = ($page - 1) * $perPage;

        $where  = '1=1';
        $params = [];

        if ($kategori) {
            $where   .= ' AND a.kategori = ?';
            $params[] = $kategori;
        }

        if ($search) {
            $where   .= ' AND (a.judul LIKE ? OR a.isi_artikel LIKE ?)';
            $params[] = "%{$search}%";
            $params[] = "%{$search}%";
        }

        $total = $this->db->count('artikel_edukasi a', $where, $params);

        $data = $this->db->fetchAll(
            "SELECT a.id_artikel, a.judul, a.kategori, a.gambar, a.created_at,
                    adm.nama as nama_penulis
             FROM `artikel_edukasi` a
             LEFT JOIN `admin` adm ON a.id_admin = adm.id_admin
             WHERE {$where}
             ORDER BY a.created_at DESC 
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
        path: '/api/artikel/{id}',
        summary: 'Detail artikel berdasarkan ID',
        tags: ['Artikel'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'string'))
        ],
        responses: [
            new OA\Response(response: 200, description: 'Detail artikel ditemukan'),
            new OA\Response(response: 404, description: 'Artikel tidak ditemukan')
        ]
    )]
    public function show(Request $request): Response
    {
        $id = $request->input('id');

        $artikel = $this->db->fetchOne(
            "SELECT a.*, adm.nama as nama_penulis
             FROM `artikel_edukasi` a
             LEFT JOIN `admin` adm ON a.id_admin = adm.id_admin
             WHERE a.id_artikel = ?",
            [$id]
        );

        if (!$artikel) {
            return Response::notFound('Artikel tidak ditemukan');
        }

        return Response::success($artikel);
    }

    #[OA\Post(
        path: '/api/admin/artikel',
        summary: 'Tambah artikel baru (Admin)',
        security: [['BearerAuth' => []]],
        tags: ['Artikel'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['judul', 'kategori', 'isi_artikel'],
                properties: [
                    new OA\Property(property: 'judul', type: 'string'),
                    new OA\Property(property: 'kategori', type: 'string'),
                    new OA\Property(property: 'isi_artikel', type: 'string'),
                    new OA\Property(property: 'gambar', type: 'string', nullable: true)
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: 'Artikel berhasil dipublikasikan'),
            new OA\Response(response: 422, description: 'Validasi gagal')
        ]
    )]
    public function store(Request $request): Response
    {
        $errors = $request->validate([
            'judul'       => 'required|min:5|max:255',
            'kategori'    => 'required|max:100',
            'isi_artikel' => 'required|min:50',
        ]);

        if (!empty($errors)) {
            return Response::error($errors[0], 422);
        }

        $admin = $request->user();
        
        $id = $this->db->insert('artikel_edukasi', [
            'id_admin'    => $admin['id'] ?? null,
            'judul'       => sanitize((string)$request->input('judul')),
            'kategori'    => sanitize((string)$request->input('kategori')),
            'isi_artikel' => $request->input('isi_artikel'),
            'gambar'      => $request->input('gambar'),
            'created_at'  => date('Y-m-d H:i:s'),
            'updated_at'  => date('Y-m-d H:i:s'),
        ]);

        $newArtikel = $this->db->fetchOne('SELECT * FROM `artikel_edukasi` WHERE id_artikel = ?', [$id]);
        
        return Response::created($newArtikel, 'Artikel berhasil dipublikasikan');
    }

    #[OA\Put(
        path: '/api/admin/artikel/{id}',
        summary: 'Update artikel (Admin)',
        security: [['BearerAuth' => []]],
        tags: ['Artikel'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'string'))
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'judul', type: 'string'),
                    new OA\Property(property: 'kategori', type: 'string'),
                    new OA\Property(property: 'isi_artikel', type: 'string'),
                    new OA\Property(property: 'gambar', type: 'string')
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'Artikel berhasil diperbarui'),
            new OA\Response(response: 404, description: 'Artikel tidak ditemukan')
        ]
    )]
    public function update(Request $request): Response
    {
        $id = $request->input('id');
        
        if (!$this->db->fetchOne('SELECT id_artikel FROM `artikel_edukasi` WHERE id_artikel = ?', [$id])) {
            return Response::notFound('Artikel tidak ditemukan');
        }

        $allowed = ['judul', 'kategori', 'isi_artikel', 'gambar'];
        $data    = array_intersect_key($request->all(), array_flip($allowed));
        $data['updated_at'] = date('Y-m-d H:i:s');

        if (isset($data['judul'])) $data['judul'] = sanitize((string)$data['judul']);
        if (isset($data['kategori'])) $data['kategori'] = sanitize((string)$data['kategori']);

        $this->db->update('artikel_edukasi', $data, 'id_artikel = ?', [$id]);

        $updatedArtikel = $this->db->fetchOne('SELECT * FROM `artikel_edukasi` WHERE id_artikel = ?', [$id]);
        
        return Response::success($updatedArtikel, 'Artikel berhasil diperbarui');
    }

    #[OA\Delete(
        path: '/api/admin/artikel/{id}',
        summary: 'Hapus artikel (Admin)',
        security: [['BearerAuth' => []]],
        tags: ['Artikel'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'string'))
        ],
        responses: [
            new OA\Response(response: 200, description: 'Artikel berhasil dihapus'),
            new OA\Response(response: 404, description: 'Artikel tidak ditemukan')
        ]
    )]
    public function destroy(Request $request): Response
    {
        $id = $request->input('id');

        if (!$this->db->fetchOne('SELECT id_artikel FROM `artikel_edukasi` WHERE id_artikel = ?', [$id])) {
            return Response::notFound('Artikel tidak ditemukan');
        }

        $this->db->delete('artikel_edukasi', 'id_artikel = ?', [$id]);
        
        return Response::success(null, 'Artikel berhasil dihapus');
    }

    #[OA\Get(
        path: '/api/artikel/kategori',
        summary: 'Daftar semua kategori artikel',
        tags: ['Artikel'],
        responses: [
            new OA\Response(response: 200, description: 'Berhasil mengambil daftar kategori')
        ]
    )]
    public function kategori(Request $request): Response
    {
        $list = $this->db->fetchAll(
            'SELECT kategori, COUNT(*) as jumlah 
             FROM `artikel_edukasi` 
             GROUP BY kategori 
             ORDER BY jumlah DESC'
        );
        
        return Response::success($list);
    }
}