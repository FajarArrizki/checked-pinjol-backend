<?php

declare(strict_types=1);

namespace App\Modules\Laporan\Controllers;

use App\Core\Http\{Request, Response};
use App\Core\Database\DatabaseManager;
use App\Core\Config\ConfigRepository;
use App\Support\FileUploader;
use OpenApi\Attributes as OA;

#[OA\Tag(name: 'Laporan', description: 'Manajemen pengaduan masyarakat terkait pinjaman online')]
class LaporanController
{
    private const STATUS_VALID = ['menunggu', 'diproses', 'selesai', 'ditolak'];
    private FileUploader $uploader;

    /**
     * SATU CONSTRUCTOR UNTUK SEMUA
     * Kita inject DatabaseManager dan ConfigRepository sekaligus di sini.
     */
    public function __construct(
        private DatabaseManager $db,
        private ConfigRepository $config 
    ) {
        // Inisialisasi uploader menggunakan config yang di-inject
        $this->uploader = new FileUploader($this->config);
    }

    private function ensureReplyColumns(): void
    {
        $columns = $this->db->fetchAll(
            "SELECT COLUMN_NAME
             FROM INFORMATION_SCHEMA.COLUMNS
             WHERE TABLE_SCHEMA = DATABASE()
               AND TABLE_NAME = 'laporan'
               AND COLUMN_NAME IN ('tanggapan_ojk', 'tanggal_tanggapan')"
        );

        $existing = array_map(static fn(array $column): string => (string) $column['COLUMN_NAME'], $columns);

        if (!in_array('tanggapan_ojk', $existing, true)) {
            $this->db->query(
                "ALTER TABLE `laporan`
                 ADD COLUMN `tanggapan_ojk` TEXT NULL AFTER `id_admin_penanggung_jawab`"
            );
        }

        if (!in_array('tanggal_tanggapan', $existing, true)) {
            $this->db->query(
                "ALTER TABLE `laporan`
                 ADD COLUMN `tanggal_tanggapan` DATETIME NULL AFTER `tanggapan_ojk`"
            );
        }
    }

    public function index(Request $request): Response
    {
        $auth = $request->user();

        if (!$auth) {
            return Response::error('Unauthorized', 401);
        }

        $where = '1=1';
        $params = [];

        if (($auth['type'] ?? '') !== 'admin') {
            $where = 'l.id_user = ?';
            $params[] = $auth['id'];
        }

        $data = $this->db->fetchAll(
            "SELECT l.id_laporan, l.kode_laporan, l.judul_laporan, l.isi_laporan, l.nama_pelapor,
                    l.kontak_pelapor, l.email_pelapor, l.tautan_aplikasi, l.foto_bukti,
                    l.status_laporan, l.tanggal_lapor, l.id_pinjol, p.nama_pinjol
             FROM `laporan` l
             LEFT JOIN `pinjol` p ON p.id_pinjol = l.id_pinjol
             WHERE {$where}
             ORDER BY l.created_at DESC",
            $params
        );

        return Response::success($data);
    }

    public function show(Request $request, string $id): Response
    {
        $auth = $request->user();
        if (!$auth) {
            return Response::error('Unauthorized', 401);
        }

        $laporan = $this->db->fetchOne(
            "SELECT l.id_laporan, l.id_user, l.kode_laporan, l.judul_laporan, l.isi_laporan, l.nama_pelapor,
                    l.kontak_pelapor, l.email_pelapor, l.tautan_aplikasi, l.foto_bukti,
                    l.status_laporan, l.tanggal_lapor, l.id_pinjol, l.id_admin_penanggung_jawab,
                    p.nama_pinjol
               FROM `laporan` l
               LEFT JOIN `pinjol` p ON p.id_pinjol = l.id_pinjol
               WHERE l.id_laporan = ?",
            [$id]
        );

        if (!$laporan) {
            return Response::notFound('Laporan tidak ditemukan');
        }

        if (($auth['type'] ?? '') !== 'admin' && (int) $laporan['id_user'] !== (int) $auth['id']) {
            return Response::error('Forbidden', 403);
        }

        $regulasi = $this->db->fetchAll(
            "SELECT r.id_regulasi, r.nama_kriteria, r.deskripsi, lr.catatan
             FROM `laporan_regulasi` lr
             INNER JOIN `regulasi_filter` r ON r.id_regulasi = lr.id_regulasi
             WHERE lr.id_laporan = ?",
            [$id]
        );

        $laporan['regulasi'] = $regulasi;

        try {
            $replyColumns = $this->db->fetchOne(
                "SELECT COLUMN_NAME
                 FROM INFORMATION_SCHEMA.COLUMNS
                 WHERE TABLE_SCHEMA = DATABASE()
                   AND TABLE_NAME = 'laporan'
                   AND COLUMN_NAME IN ('tanggapan_ojk', 'tanggal_tanggapan')
                 ORDER BY COLUMN_NAME ASC"
            );

            if ($replyColumns) {
                $reply = $this->db->fetchOne(
                    'SELECT tanggapan_ojk, tanggal_tanggapan FROM `laporan` WHERE id_laporan = ?',
                    [$id]
                );

                if ($reply) {
                    $laporan['tanggapan_ojk'] = $reply['tanggapan_ojk'] ?? null;
                    $laporan['tanggal_tanggapan'] = $reply['tanggal_tanggapan'] ?? null;
                }
            }
        } catch (
            \Throwable $e
        ) {
            // Abaikan jika kolom reply belum ada di database aktif.
        }

        $laporan['lampiran'] = $this->db->fetchAll(
            "SELECT id_lampiran, id_laporan, nama_file, file_path, tipe_file, ukuran_file, uploaded_at
             FROM `lampiran_laporan`
             WHERE id_laporan = ?
             ORDER BY id_lampiran ASC",
            [$id]
        );

        return Response::success($laporan);
    }

    public function cekStatus(Request $request): Response
    {
        $kode = trim((string) $request->input('kode', ''));

        if ($kode === '') {
            return Response::error('Kode laporan wajib diisi', 422);
        }

        $row = $this->db->fetchOne(
            "SELECT id_laporan, kode_laporan, judul_laporan, status_laporan, tanggal_lapor
             FROM `laporan`
             WHERE kode_laporan = ?",
            [$kode]
        );

        if (!$row) {
            return Response::notFound('Laporan tidak ditemukan');
        }

        return Response::success($row);
    }

    public function statistik(Request $request): Response
    {
        $auth = $request->user();
        if (!$auth) {
            return Response::error('Unauthorized', 401);
        }

        $where = '';
        $params = [];
        if (($auth['type'] ?? '') !== 'admin') {
            $where = 'WHERE id_user = ?';
            $params[] = $auth['id'];
        }

        $counts = [
            'menunggu' => $this->db->count('laporan', trim($where . ' AND status_laporan = ?'), array_merge($params, ['menunggu'])),
            'diproses'  => $this->db->count('laporan', trim($where . ' AND status_laporan = ?'), array_merge($params, ['diproses'])),
            'selesai'   => $this->db->count('laporan', trim($where . ' AND status_laporan = ?'), array_merge($params, ['selesai'])),
            'ditolak'   => $this->db->count('laporan', trim($where . ' AND status_laporan = ?'), array_merge($params, ['ditolak'])),
        ];

        return Response::success($counts);
    }

    public function updateStatus(Request $request, string $id): Response
    {
        $auth = $request->user();
        if (!$auth || ($auth['type'] ?? '') !== 'admin') {
            return Response::error('Forbidden', 403);
        }

        $status = (string) $request->input('status_laporan', '');

        if (!in_array($status, self::STATUS_VALID, true)) {
            return Response::error('Status laporan tidak valid', 422);
        }

        if (!$this->db->fetchOne('SELECT id_laporan FROM `laporan` WHERE id_laporan = ?', [$id])) {
            return Response::notFound('Laporan tidak ditemukan');
        }

        $this->db->update('laporan', [
            'status_laporan' => $status,
            'updated_at' => date('Y-m-d H:i:s'),
        ], 'id_laporan = ?', [$id]);

        return Response::success($this->db->fetchOne('SELECT * FROM `laporan` WHERE id_laporan = ?', [$id]), 'Status laporan diperbarui');
    }

    #[OA\Patch(
        path: '/api/admin/laporan/{id}/reply',
        summary: 'Menambahkan balasan admin untuk laporan',
        tags: ['Laporan'],
        security: [['BearerAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['status_laporan', 'tanggapan_ojk'],
                properties: [
                    new OA\Property(property: 'status_laporan', type: 'string', example: 'diproses'),
                    new OA\Property(property: 'tanggapan_ojk', type: 'string', example: '<p>Laporan sedang diproses.</p>')
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'Balasan laporan berhasil disimpan'),
            new OA\Response(response: 422, description: 'Validasi gagal'),
            new OA\Response(response: 404, description: 'Laporan tidak ditemukan')
        ]
    )]
    public function reply(Request $request, string $id): Response
    {
        $this->ensureReplyColumns();

        $auth = $request->user();
        if (!$auth || ($auth['type'] ?? '') !== 'admin') {
            return Response::error('Forbidden', 403);
        }

        $status = (string) $request->input('status_laporan', '');
        $tanggapan = trim((string) $request->input('tanggapan_ojk', ''));

        if (!in_array($status, self::STATUS_VALID, true)) {
            return Response::error('Status laporan tidak valid', 422);
        }

        if ($tanggapan === '') {
            return Response::error('Balasan admin wajib diisi', 422);
        }

        $laporan = $this->db->fetchOne('SELECT id_laporan FROM `laporan` WHERE id_laporan = ?', [$id]);
        if (!$laporan) {
            return Response::notFound('Laporan tidak ditemukan');
        }

        $this->db->update('laporan', [
            'status_laporan' => $status,
            'tanggapan_ojk' => $tanggapan,
            'tanggal_tanggapan' => date('Y-m-d H:i:s'),
            'id_admin_penanggung_jawab' => $auth['id'],
            'updated_at' => date('Y-m-d H:i:s'),
        ], 'id_laporan = ?', [$id]);

        return Response::success(
            $this->db->fetchOne('SELECT * FROM `laporan` WHERE id_laporan = ?', [$id]),
            'Balasan laporan berhasil disimpan'
        );
    }

    #[OA\Post(
        path: '/api/laporan',
        summary: 'Kirim laporan pengaduan baru dengan upload file',
        tags: ['Laporan'],
        // ... (OA Metadata lainnya tetap sama)
    )]
    public function store(Request $request): Response
    {
        $errors = $request->validate([
            'judul_laporan'  => 'required|min:5|max:255',
            'isi_laporan'    => 'required|min:20',
            'nama_pelapor'   => 'required',
            'kontak_pelapor' => 'required',
        ]);

        if (!empty($errors)) return Response::error($errors[0], 422);

        try {
            $auth   = $request->user();
            $userId = ($auth && ($auth['type'] ?? '') === 'user') ? $auth['id'] : null;

            // 1. PROSES UPLOAD FOTO BUKTI (bisa banyak file)
            $fotoPaths = [];
            if (isset($_FILES['foto_bukti'])) {
                $files = $_FILES['foto_bukti'];
                $isMultiple = is_array($files['name'] ?? null);

                if ($isMultiple) {
                    $count = count($files['name']);
                    for ($i = 0; $i < $count; $i++) {
                        if (($files['error'][$i] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
                            continue;
                        }

                        $file = [
                            'name' => $files['name'][$i],
                            'type' => $files['type'][$i],
                            'tmp_name' => $files['tmp_name'][$i],
                            'error' => $files['error'][$i],
                            'size' => $files['size'][$i],
                        ];

                        $fileInfo = $this->uploader->upload($file, 'laporan');
                        $fotoPaths[] = $fileInfo['file_path'];
                    }
                } elseif (($files['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_OK) {
                    $fileInfo = $this->uploader->upload($files, 'laporan');
                    $fotoPaths[] = $fileInfo['file_path'];
                }
            }

            // 2. INSERT DATA KE DATABASE
            $id = $this->db->insert('laporan', [
                'id_user'         => $userId,
                'kode_laporan'    => "LAP-" . strtoupper(bin2hex(random_bytes(4))), 
                'judul_laporan'   => sanitize((string)$request->input('judul_laporan')),
                'isi_laporan'     => $request->input('isi_laporan'),
                'nama_pelapor'    => sanitize((string)$request->input('nama_pelapor')),
                'kontak_pelapor'  => $request->input('kontak_pelapor'),
                'email_pelapor'   => $request->input('email_pelapor'),
                'tautan_aplikasi' => $request->input('tautan_aplikasi'),
                'id_pinjol'       => $request->input('id_pinjol'),
                'foto_bukti'      => $fotoPaths[0] ?? null, // Simpan file utama ke kolom foto_bukti
                'status_laporan'  => 'menunggu',
                'tanggal_lapor'   => date('Y-m-d H:i:s'),
                'created_at'      => date('Y-m-d H:i:s'),
                'updated_at'      => date('Y-m-d H:i:s'),
            ]);

            // 3. SIMPAN RELASI REGULASI
            $regulasiIds = $request->input('regulasi_ids', []);
            if (is_array($regulasiIds)) {
                foreach ($regulasiIds as $rId) {
                    $this->db->insert('laporan_regulasi', [
                        'id_laporan'  => $id,
                        'id_regulasi' => (int)$rId,
                        'catatan'     => null,
                    ]);
                }
            }

            foreach ($fotoPaths as $path) {
                $this->db->insert('lampiran_laporan', [
                    'id_laporan'  => $id,
                    'nama_file'   => basename($path),
                    'file_path'   => $path,
                    'tipe_file'   => 'image',
                    'ukuran_file' => null,
                    'uploaded_at' => date('Y-m-d H:i:s'),
                ]);
            }

            $laporan = $this->db->fetchOne('SELECT * FROM `laporan` WHERE id_laporan = ?', [$id]);
            return Response::created($laporan, 'Laporan berhasil dikirim dengan kode: ' . $laporan['kode_laporan']);

        } catch (\Exception $e) {
            // Jika upload gagal atau DB error, kembalikan pesan error yang rapi
            return Response::error($e->getMessage(), 400);
        }
    }
}
