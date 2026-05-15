<?php

declare(strict_types=1);

namespace App\Modules\Ulasan;

use App\Core\Http\{Request, Response};
use App\Core\Database\DatabaseManager;
use OpenApi\Attributes as OA;

#[OA\Tag(name: 'Ulasan', description: 'Manajemen ulasan dan rating pengguna terhadap pinjol')]
class UlasanController
{
    public function __construct(private DatabaseManager $db) {}

    #[OA\Get(
        path: '/api/ulasan',
        summary: 'Daftar ulasan pengguna',
        description: 'Mengambil daftar ulasan publik. Bisa difilter berdasarkan ID Pinjol tertentu.',
        tags: ['Ulasan'],
        parameters: [
            new OA\Parameter(name: 'page', in: 'query', schema: new OA\Schema(type: 'integer', default: 1)),
            new OA\Parameter(name: 'per_page', in: 'query', schema: new OA\Schema(type: 'integer', default: 10)),
            new OA\Parameter(name: 'id_pinjol', in: 'query', description: 'Filter berdasarkan ID Pinjol', schema: new OA\Schema(type: 'integer'))
        ],
        responses: [
            new OA\Response(response: 200, description: 'Berhasil mengambil daftar ulasan')
        ]
    )]
    public function index(Request $request): Response
    {
        $page     = max(1, (int) $request->input('page', 1));
        $perPage  = min(50, max(5, (int) $request->input('per_page', 10)));
        $pinjolId = $request->input('id_pinjol');
        $offset   = ($page - 1) * $perPage;

        $where  = '1=1';
        $params = [];
        
        if ($pinjolId) {
            $where   .= ' AND u.id_pinjol = ?';
            $params[] = $pinjolId;
        }

        $total = $this->db->count('ulasan u', $where, $params);

        $data = $this->db->fetchAll(
            "SELECT u.*, p.nama_pinjol 
             FROM `ulasan` u
             LEFT JOIN `pinjol` p ON u.id_pinjol = p.id_pinjol
             WHERE {$where} 
             ORDER BY u.created_at DESC 
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

    #[OA\Post(
        path: '/api/ulasan',
        summary: 'Kirim ulasan baru',
        description: 'Menambahkan ulasan dan rating (1-5) untuk pinjol tertentu.',
        tags: ['Ulasan'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['id_pinjol', 'nama_pengulas', 'rating', 'komentar'],
                properties: [
                    new OA\Property(property: 'id_pinjol', type: 'integer', example: 1),
                    new OA\Property(property: 'nama_pengulas', type: 'string', example: 'Budi Santoso'),
                    new OA\Property(property: 'rating', type: 'integer', minimum: 1, maximum: 5, example: 5),
                    new OA\Property(property: 'komentar', type: 'string', example: 'Layanan sangat cepat dan transparan.')
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: 'Ulasan berhasil dikirim'),
            new OA\Response(response: 422, description: 'Validasi gagal atau rating tidak sesuai'),
            new OA\Response(response: 404, description: 'Pinjol tidak ditemukan')
        ]
    )]
    public function store(Request $request): Response
    {
        $errors = $request->validate([
            'id_pinjol'     => 'required',
            'nama_pengulas' => 'required|max:255',
            'rating'        => 'required',
            'komentar'      => 'required|min:10',
        ]);

        if (!empty($errors)) {
            return Response::error($errors[0], 422);
        }

        $rating = (int) $request->input('rating');
        if ($rating < 1 || $rating > 5) {
            return Response::error('Rating harus antara 1 sampai 5', 422);
        }

        if (!$this->db->fetchOne('SELECT id_pinjol FROM `pinjol` WHERE id_pinjol = ?', [$request->input('id_pinjol')])) {
            return Response::notFound('Pinjol tidak ditemukan');
        }

        $auth = $request->user();
        
        $id = $this->db->insert('ulasan', [
            'id_user'       => $auth['id'] ?? null,
            'id_pinjol'     => $request->input('id_pinjol'),
            'nama_pengulas' => sanitize((string)$request->input('nama_pengulas')),
            'rating'        => $rating,
            'komentar'      => $request->input('komentar'),
            'created_at'    => date('Y-m-d H:i:s'),
        ]);

        $newUlasan = $this->db->fetchOne('SELECT * FROM `ulasan` WHERE id_ulasan = ?', [$id]);
        
        return Response::created($newUlasan, 'Ulasan berhasil dikirim');
    }

    #[OA\Delete(
        path: '/api/admin/ulasan/{id}',
        summary: 'Hapus ulasan (Admin)',
        security: [['BearerAuth' => []]],
        tags: ['Ulasan'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))
        ],
        responses: [
            new OA\Response(response: 200, description: 'Ulasan berhasil dihapus'),
            new OA\Response(response: 401, description: 'Unauthorized'),
            new OA\Response(response: 404, description: 'Ulasan tidak ditemukan')
        ]
    )]
    public function destroy(Request $request): Response
    {
        $id = $request->input('id');

        if (!$this->db->fetchOne('SELECT id_ulasan FROM `ulasan` WHERE id_ulasan = ?', [$id])) {
            return Response::notFound('Ulasan tidak ditemukan');
        }

        $this->db->delete('ulasan', 'id_ulasan = ?', [$id]);
        
        return Response::success(null, 'Ulasan berhasil dihapus');
    }
}
