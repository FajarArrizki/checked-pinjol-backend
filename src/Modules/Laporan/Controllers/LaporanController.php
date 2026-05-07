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

    // ... method index(), show(), statistik(), updateStatus() tetap sama seperti sebelumnya ...

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

            // 1. PROSES UPLOAD FOTO BUKTI (Jika ada file yang dikirim)
            $fotoPath = null;
            if (isset($_FILES['foto_bukti']) && $_FILES['foto_bukti']['error'] === UPLOAD_ERR_OK) {
                $fileInfo = $this->uploader->upload($_FILES['foto_bukti'], 'laporan');
                $fotoPath = $fileInfo['file_path'];
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
                'foto_bukti'      => $fotoPath, // Simpan path hasil upload ke kolom foto_bukti
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

            $laporan = $this->db->fetchOne('SELECT * FROM `laporan` WHERE id_laporan = ?', [$id]);
            return Response::created($laporan, 'Laporan berhasil dikirim dengan kode: ' . $laporan['kode_laporan']);

        } catch (\Exception $e) {
            // Jika upload gagal atau DB error, kembalikan pesan error yang rapi
            return Response::error($e->getMessage(), 400);
        }
    }
}