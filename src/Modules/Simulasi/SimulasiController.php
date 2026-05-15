<?php

declare(strict_types=1);

namespace App\Modules\Simulasi;

use App\Core\Http\{Request, Response};
use App\Core\Database\DatabaseManager;
use OpenApi\Attributes as OA;

#[OA\Tag(name: 'Simulasi', description: 'Fitur kalkulator simulasi pinjaman dan riwayat perhitungan')]
class SimulasiController
{
    public function __construct(private DatabaseManager $db) {}

    #[OA\Post(
        path: '/api/simulasi',
        summary: 'Hitung simulasi pinjaman',
        description: 'Melakukan kalkulasi bunga, total bayar, cicilan bulanan, dan APR tahunan. Jika User login, hasil akan disimpan ke riwayat.',
        tags: ['Simulasi'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['jumlah_pinjaman', 'tenor_hari', 'bunga_per_hari'],
                properties: [
                    new OA\Property(property: 'jumlah_pinjaman', type: 'number', format: 'float', example: 1000000),
                    new OA\Property(property: 'tenor_hari', type: 'integer', example: 30),
                    new OA\Property(property: 'bunga_per_hari', type: 'number', format: 'float', description: 'Persentase bunga harian', example: 0.4),
                    new OA\Property(property: 'biaya_admin', type: 'number', format: 'float', example: 50000)
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Hasil kalkulasi simulasi',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean'),
                        new OA\Property(property: 'data', type: 'object', properties: [
                            new OA\Property(property: 'total_bunga', type: 'number'),
                            new OA\Property(property: 'total_bayar', type: 'number'),
                            new OA\Property(property: 'cicilan_per_bulan', type: 'number'),
                            new OA\Property(property: 'apr_tahunan', type: 'number'),
                            new OA\Property(property: 'peringatan', type: 'string', nullable: true)
                        ])
                    ]
                )
            ),
            new OA\Response(response: 422, description: 'Input tidak valid')
        ]
    )]
    public function hitung(Request $request): Response
    {
        $errors = $request->validate([
            'jumlah_pinjaman' => 'required',
            'tenor_hari'      => 'required',
            'bunga_per_hari'  => 'required',
        ]);

        if (!empty($errors)) {
            return Response::error($errors[0], 422);
        }

        $pokok         = (float) $request->input('jumlah_pinjaman');
        $tenorHari     = (int)   $request->input('tenor_hari');
        $bungaPerHari  = (float) $request->input('bunga_per_hari'); 
        $biayaAdmin    = (float) $request->input('biaya_admin', 0);

        if ($pokok <= 0 || $tenorHari <= 0 || $bungaPerHari < 0) {
            return Response::error('Nilai input tidak valid', 422);
        }

        // Kalkulasi Keuangan
        $totalBunga      = $pokok * ($bungaPerHari / 100) * $tenorHari;
        $totalBayar      = $pokok + $totalBunga + $biayaAdmin;
        $tenorBulan      = max(1, (int) ceil($tenorHari / 30));
        $cicilanPerBulan = $totalBayar / $tenorBulan;
        $aprTahunan      = ($bungaPerHari / 100) * 365 * 100;

        $result = [
            'jumlah_pinjaman'   => $pokok,
            'tenor_hari'        => $tenorHari,
            'tenor_bulan'       => $tenorBulan,
            'bunga_per_hari'    => $bungaPerHari,
            'biaya_admin'       => $biayaAdmin,
            'total_bunga'       => round($totalBunga, 2),
            'total_bayar'       => round($totalBayar, 2),
            'cicilan_per_bulan' => round($cicilanPerBulan, 2),
            'apr_tahunan'       => round($aprTahunan, 2),
            'peringatan'        => $aprTahunan > 100
                ? 'PERINGATAN: APR tahunan sangat tinggi (' . round($aprTahunan, 1) . '%). Pertimbangkan kembali pinjaman ini.'
                : null,
        ];

        $auth = $request->user();
        if ($auth && ($auth['type'] ?? '') === 'user') {
            $id = $this->db->insert('simulasi_pinjaman', [
                'id_user'           => $auth['id'],
                'jumlah_pinjaman'   => $pokok,
                'tenor_hari'        => $tenorHari,
                'bunga_per_hari'    => $bungaPerHari,
                'biaya_admin'       => $biayaAdmin,
                'cicilan_per_bulan' => round($cicilanPerBulan, 2),
                'total_bayar'       => round($totalBayar, 2),
                'apr_tahunan'       => round($aprTahunan, 2),
                'created_at'        => date('Y-m-d H:i:s'),
            ]);
            $result['id_simulasi'] = $id;
            $result['disimpan']    = true;
        }

        return Response::success($result, 'Simulasi berhasil dihitung');
    }

    #[OA\Get(
        path: '/api/simulasi/riwayat',
        summary: 'Riwayat simulasi user',
        security: [['BearerAuth' => []]],
        tags: ['Simulasi'],
        parameters: [
            new OA\Parameter(name: 'page', in: 'query', schema: new OA\Schema(type: 'integer', default: 1)),
            new OA\Parameter(name: 'per_page', in: 'query', schema: new OA\Schema(type: 'integer', default: 10))
        ],
        responses: [
            new OA\Response(response: 200, description: 'Daftar riwayat simulasi'),
            new OA\Response(response: 401, description: 'Unauthorized')
        ]
    )]
    public function riwayat(Request $request): Response
    {
        $auth = $request->user();
        if (!$auth) return Response::error('Unauthorized', 401);

        $page    = max(1, (int) $request->input('page', 1));
        $perPage = min(50, max(5, (int) $request->input('per_page', 10)));
        $offset  = ($page - 1) * $perPage;

        $total = $this->db->count('simulasi_pinjaman', 'id_user = ?', [$auth['id']]);
        
        $data  = $this->db->fetchAll(
            "SELECT * FROM `simulasi_pinjaman` 
             WHERE id_user = ? 
             ORDER BY created_at DESC 
             LIMIT $perPage OFFSET $offset",
            [$auth['id']]
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

    #[OA\Delete(
        path: '/api/simulasi/{id}',
        summary: 'Hapus riwayat simulasi',
        security: [['BearerAuth' => []]],
        tags: ['Simulasi'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))
        ],
        responses: [
            new OA\Response(response: 200, description: 'Riwayat berhasil dihapus'),
            new OA\Response(response: 403, description: 'Akses ditolak'),
            new OA\Response(response: 404, description: 'Data tidak ditemukan')
        ]
    )]
    public function destroy(Request $request): Response
    {
        $id   = $request->input('id');
        $auth = $request->user();

        if (!$auth) return Response::error('Unauthorized', 401);

        $sim = $this->db->fetchOne('SELECT * FROM `simulasi_pinjaman` WHERE id_simulasi = ?', [$id]);
        
        if (!$sim) {
            return Response::notFound('Riwayat simulasi tidak ditemukan');
        }

        if ((int)$sim['id_user'] !== (int)$auth['id']) {
            return Response::error('Anda tidak memiliki akses untuk menghapus data ini', 403);
        }

        $this->db->delete('simulasi_pinjaman', 'id_simulasi = ?', [$id]);
        
        return Response::success(null, 'Riwayat simulasi berhasil dihapus');
    }
}
