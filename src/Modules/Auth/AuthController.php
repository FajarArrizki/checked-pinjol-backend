<?php

declare(strict_types=1);

namespace App\Modules\Auth;

use App\Core\Http\Request;
use App\Core\Http\Response;
use App\Core\Database\DatabaseManager;
use App\Core\Auth\JWT;
use OpenApi\Attributes as OA;

#[OA\Info(title: "Auth API", version: "1.0.0")]
class AuthController
{
    public function __construct(private DatabaseManager $db)
    {
    }

    private function ensureTwoFactorColumns(): void
    {
        $columns = $this->db->fetchAll(
            "SELECT COLUMN_NAME
             FROM INFORMATION_SCHEMA.COLUMNS
             WHERE TABLE_SCHEMA = DATABASE()
               AND TABLE_NAME = 'pengaturan_admin'
               AND COLUMN_NAME IN ('two_factor_secret', 'two_factor_confirmed_at', 'last_login_ip', 'last_login_at')"
        );

        $existing = array_map(static fn(array $column): string => (string) $column['COLUMN_NAME'], $columns);

        if (!in_array('two_factor_secret', $existing, true)) {
            $this->db->query(
                "ALTER TABLE `pengaturan_admin`
                 ADD COLUMN `two_factor_secret` VARCHAR(255) NULL AFTER `two_factor_enabled`"
            );
        }

        if (!in_array('two_factor_confirmed_at', $existing, true)) {
            $this->db->query(
                "ALTER TABLE `pengaturan_admin`
                 ADD COLUMN `two_factor_confirmed_at` DATETIME NULL AFTER `two_factor_secret`"
            );
        }

        if (!in_array('last_login_ip', $existing, true)) {
            $this->db->query(
                "ALTER TABLE `pengaturan_admin`
                 ADD COLUMN `last_login_ip` VARCHAR(64) NULL AFTER `two_factor_confirmed_at`"
            );
        }

        if (!in_array('last_login_at', $existing, true)) {
            $this->db->query(
                "ALTER TABLE `pengaturan_admin`
                 ADD COLUMN `last_login_at` DATETIME NULL AFTER `last_login_ip`"
            );
        }
    }

    private function getRequestIp(): string
    {
        return (string) ($_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'] ?? 'unknown');
    }

    private function buildLocationAlert(?array $settings, array $admin): ?array
    {
        $currentIp = $this->getRequestIp();
        $lastIp = (string) ($settings['last_login_ip'] ?? '');

        if (!($settings['email_alert_darurat'] ?? 0) || $lastIp === '' || $lastIp === $currentIp) {
            return null;
        }

        return [
            'type' => 'email_alert_darurat',
            'message' => 'Login terdeteksi dari lokasi/IP berbeda.',
            'current_ip' => $currentIp,
            'previous_ip' => $lastIp,
            'email' => $admin['email'] ?? null,
            'occurred_at' => date('Y-m-d H:i:s'),
        ];
    }

    private function touchAdminLogin(int $adminId): void
    {
        $this->db->query(
            "INSERT INTO `pengaturan_admin` (`id_admin`, `email_alert_darurat`, `ringkasan_laporan`, `two_factor_enabled`, `last_login_ip`, `last_login_at`, `updated_at`)
             VALUES (?, 1, 1, 0, ?, ?, ?)
             ON DUPLICATE KEY UPDATE `last_login_ip` = VALUES(`last_login_ip`), `last_login_at` = VALUES(`last_login_at`), `updated_at` = VALUES(`updated_at`)",
            [$adminId, $this->getRequestIp(), date('Y-m-d H:i:s'), date('Y-m-d H:i:s')]
        );
    }

    private function generateBase32Secret(int $length = 32): string
    {
        $alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
        $secret = '';

        for ($i = 0; $i < $length; $i++) {
            $secret .= $alphabet[random_int(0, strlen($alphabet) - 1)];
        }

        return $secret;
    }

    private function base32Decode(string $secret): string
    {
        $secret = strtoupper($secret);
        $alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
        $bits = '';

        foreach (str_split($secret) as $char) {
            $position = strpos($alphabet, $char);
            if ($position === false) {
                continue;
            }

            $bits .= str_pad(decbin($position), 5, '0', STR_PAD_LEFT);
        }

        $binary = '';
        foreach (str_split($bits, 8) as $chunk) {
            if (strlen($chunk) === 8) {
                $binary .= chr(bindec($chunk));
            }
        }

        return $binary;
    }

    private function generateTotpCode(string $secret, ?int $timestamp = null): string
    {
        $counter = floor(($timestamp ?? time()) / 30);
        $binaryCounter = pack('N*', 0) . pack('N*', $counter);
        $hash = hash_hmac('sha1', $binaryCounter, $this->base32Decode($secret), true);
        $offset = ord(substr($hash, -1)) & 0x0F;
        $truncated = substr($hash, $offset, 4);
        $value = unpack('N', $truncated)[1] & 0x7FFFFFFF;

        return str_pad((string) ($value % 1000000), 6, '0', STR_PAD_LEFT);
    }

    private function verifyTotpCode(string $secret, string $otp, ?string $context = null): bool
    {
        $otp = trim($otp);

        if ($this->isLocalDemoOtp($otp, $context)) {
            return true;
        }

        for ($step = -1; $step <= 1; $step++) {
            if (hash_equals($this->generateTotpCode($secret, time() + ($step * 30)), $otp)) {
                return true;
            }
        }

        return false;
    }

    private function isLocalDemoOtp(string $otp, ?string $context = null): bool
    {
        $appEnv = strtolower((string) env('APP_ENV', 'production'));
        if ($appEnv !== 'local') {
            return false;
        }

        $envKey = match ($context) {
            'setup' => 'DEMO_2FA_SETUP_OTP',
            'login' => 'DEMO_2FA_LOGIN_OTP',
            'password' => 'DEMO_2FA_PASSWORD_OTP',
            default => 'DEMO_2FA_LOGIN_OTP',
        };

        $fallback = match ($context) {
            'setup' => '482913',
            'login' => '731824',
            'password' => '564298',
            default => '731824',
        };

        $demoOtp = trim((string) env($envKey, $fallback));
        return $demoOtp !== '' && hash_equals($demoOtp, $otp);
    }

    private function issueAdminToken(array $admin): string
    {
        return JWT::encode([
            'id'    => $admin['id_admin'],
            'email' => $admin['email'],
            'nama'  => $admin['nama'],
            'role'  => $admin['role'],
            'type'  => 'admin',
        ]);
    }

    #[OA\Post(
        path: '/api/auth/register',
        summary: 'Registrasi user baru',
        tags: ['Auth'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['nama', 'email', 'no_hp', 'password'],
                properties: [
                    new OA\Property(property: 'nama', type: 'string', example: 'Budi Santoso'),
                    new OA\Property(property: 'email', type: 'string', format: 'email', example: 'budi@example.com'),
                    new OA\Property(property: 'no_hp', type: 'string', example: '08123456789'),
                    new OA\Property(property: 'password', type: 'string', format: 'password', example: 'password123')
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: 'Registrasi berhasil'),
            new OA\Response(response: 422, description: 'Validasi gagal'),
            new OA\Response(response: 409, description: 'Email sudah terdaftar')
        ]
    )]
    public function register(Request $request): Response
    {
        $errors = $request->validate([
            'nama'     => 'required|min:3|max:255',
            'email'    => 'required|email',
            'no_hp'    => 'required|min:9|max:20',
            'password' => 'required|min:6',
        ]);

        if (!empty($errors)) {
            return Response::error($errors[0], 422);
        }

        if ($this->db->fetchOne('SELECT id_user FROM `user` WHERE email = ?', [$request->input('email')])) {
            return Response::error('Email sudah terdaftar', 409);
        }

        $id = $this->db->insert('user', [
            'nama'          => sanitize($request->input('nama')),
            'email'         => $request->input('email'),
            'no_hp'         => $request->input('no_hp'),
            'password_hash' => bcryptHash($request->input('password')),
            'created_at'    => date('Y-m-d H:i:s'),
            'updated_at'    => date('Y-m-d H:i:s'),
        ]);

        $user = $this->db->fetchOne(
            'SELECT id_user, nama, email, no_hp, created_at FROM `user` WHERE id_user = ?', 
            [$id]
        );

        return Response::created($user, 'Registrasi berhasil');
    }

    #[OA\Post(
        path: '/api/auth/login',
        summary: 'Login user untuk mendapatkan token',
        tags: ['Auth'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['email', 'password'],
                properties: [
                    new OA\Property(property: 'email', type: 'string', format: 'email', example: 'user@example.com'),
                    new OA\Property(property: 'password', type: 'string', format: 'password', example: 'rahasia123')
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'Login Berhasil'),
            new OA\Response(response: 401, description: 'Email atau password salah')
        ]
    )]
    public function login(Request $request): Response
    {
        $user = $this->db->fetchOne('SELECT * FROM `user` WHERE email = ?', [$request->input('email')]);

        if (!$user || !bcryptVerify($request->input('password'), (string) $user['password_hash'])) {
            return Response::error('Email atau password salah', 401);
        }

        $token = JWT::encode([
            'id'    => $user['id_user'],
            'email' => $user['email'],
            'nama'  => $user['nama'],
            'type'  => 'user',
            'role'  => 'user',
        ]);

        unset($user['password_hash']);
        return Response::success(['token' => $token, 'user' => $user], 'Login berhasil');
    }

    #[OA\Post(
        path: '/api/auth/admin-login',
        summary: 'Login khusus admin',
        tags: ['Auth'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['username', 'password'],
                properties: [
                    new OA\Property(property: 'username', type: 'string', example: 'admin_ganteng'),
                    new OA\Property(property: 'password', type: 'string', example: 'admin123')
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'Login admin berhasil'),
            new OA\Response(response: 401, description: 'Username atau password salah')
        ]
    )]
    public function adminLogin(Request $request): Response
    {
        $this->ensureTwoFactorColumns();

        $uname = $request->input('username');
        $admin = $this->db->fetchOne(
            'SELECT * FROM `admin` WHERE (username = ? OR email = ?) AND is_active = 1',
            [$uname, $uname]
        );

        if (!$admin || !bcryptVerify($request->input('password'), (string) $admin['password_hash'])) {
            return Response::error('Username atau password salah', 401);
        }

        $settings = $this->db->fetchOne(
            'SELECT email_alert_darurat, two_factor_enabled, two_factor_secret, last_login_ip FROM `pengaturan_admin` WHERE id_admin = ?',
            [$admin['id_admin']]
        );
        $locationAlert = $this->buildLocationAlert($settings, $admin);

        if (($settings['two_factor_enabled'] ?? 0) && !empty($settings['two_factor_secret'])) {
            $challengeToken = JWT::encode([
                'id' => $admin['id_admin'],
                'type' => 'admin',
                'role' => $admin['role'],
                'purpose' => 'admin_2fa',
            ]);

            return Response::success([
                'requires_two_factor' => true,
                'challenge_token' => $challengeToken,
                'user' => [
                    'id_admin' => $admin['id_admin'],
                    'nama' => $admin['nama'],
                    'email' => $admin['email'],
                    'username' => $admin['username'],
                    'role' => $admin['role'],
                ],
                'notifications' => $locationAlert ? [$locationAlert] : [],
            ], 'OTP diperlukan');
        }

        $token = $this->issueAdminToken($admin);
        $this->touchAdminLogin((int) $admin['id_admin']);

        unset($admin['password_hash']);
        return Response::success(['token' => $token, 'user' => $admin, 'type' => 'admin', 'notifications' => $locationAlert ? [$locationAlert] : []], 'Login admin berhasil');
    }

    public function verifyAdminTwoFactor(Request $request): Response
    {
        $this->ensureTwoFactorColumns();

        $challengeToken = trim((string) $request->input('challenge_token', ''));
        $otp = trim((string) $request->input('otp', ''));

        if ($challengeToken === '' || $otp === '') {
            return Response::error('Challenge token dan OTP wajib diisi', 422);
        }

        $payload = JWT::decode($challengeToken);
        if (!$payload || ($payload['purpose'] ?? '') !== 'admin_2fa' || ($payload['type'] ?? '') !== 'admin') {
            return Response::error('Challenge 2FA tidak valid', 401);
        }

        $admin = $this->db->fetchOne('SELECT * FROM `admin` WHERE id_admin = ? AND is_active = 1', [$payload['id']]);
        if (!$admin) {
            return Response::error('Admin tidak ditemukan', 401);
        }

        $settings = $this->db->fetchOne(
            'SELECT email_alert_darurat, two_factor_enabled, two_factor_secret, last_login_ip FROM `pengaturan_admin` WHERE id_admin = ?',
            [$admin['id_admin']]
        );

        if (!($settings['two_factor_enabled'] ?? 0) || empty($settings['two_factor_secret'])) {
            return Response::error('2FA tidak aktif', 400);
        }

        if (!$this->verifyTotpCode((string) $settings['two_factor_secret'], $otp, 'login')) {
            return Response::error('OTP tidak valid', 400);
        }

        $token = $this->issueAdminToken($admin);
        $locationAlert = $this->buildLocationAlert($settings, $admin);
        $this->touchAdminLogin((int) $admin['id_admin']);
        unset($admin['password_hash']);

        return Response::success(['token' => $token, 'user' => $admin, 'type' => 'admin', 'notifications' => $locationAlert ? [$locationAlert] : []], 'Verifikasi 2FA berhasil');
    }

    #[OA\Get(
        path: '/api/auth/me',
        summary: 'Mendapatkan profil pengguna saat ini',
        tags: ['Auth'],
        security: [['bearerAuth' => []]],
        responses: [
            new OA\Response(response: 200, description: 'Data profil berhasil diambil'),
            new OA\Response(response: 401, description: 'Unauthorized'),
            new OA\Response(response: 404, description: 'User tidak ditemukan')
        ]
    )]
    public function me(Request $request): Response
    {
        $auth = $request->user(); 
        
        if (!$auth) return Response::error('Unauthorized', 401);

        $table = ($auth['type'] === 'admin') ? 'admin' : 'user';
        $pk    = ($auth['type'] === 'admin') ? 'id_admin' : 'id_user';
        $fields = ($auth['type'] === 'admin') 
            ? 'id_admin, nama, email, username, role, no_hp' 
            : 'id_user, nama, email, no_hp, created_at';

        $data = $this->db->fetchOne("SELECT $fields FROM $table WHERE $pk = ?", [$auth['id']]);

        return $data ? Response::success($data) : Response::notFound('User tidak ditemukan');
    }

    public function updateProfile(Request $request): Response
    {
        $auth = $request->user();

        if (!$auth) {
            return Response::error('Unauthorized', 401);
        }

        if (($auth['type'] ?? '') !== 'admin') {
            return Response::error('Forbidden', 403);
        }

        $nama = trim((string) $request->input('nama', ''));
        if ($nama === '') {
            return Response::error('Nama lengkap wajib diisi', 422);
        }

        $this->db->update('admin', [
            'nama' => sanitize($nama),
            'updated_at' => date('Y-m-d H:i:s'),
        ], 'id_admin = ?', [$auth['id']]);

        $data = $this->db->fetchOne(
            'SELECT id_admin, nama, email, username, role, no_hp FROM `admin` WHERE id_admin = ?',
            [$auth['id']]
        );

        return $data ? Response::success($data, 'Profil berhasil diperbarui') : Response::notFound('User tidak ditemukan');
    }

    public function twoFactorSetup(Request $request): Response
    {
        $this->ensureTwoFactorColumns();

        $auth = $request->user();
        if (!$auth || ($auth['type'] ?? '') !== 'admin') {
            return Response::error('Forbidden', 403);
        }

        $admin = $this->db->fetchOne('SELECT email FROM `admin` WHERE id_admin = ?', [$auth['id']]);
        $settings = $this->db->fetchOne('SELECT two_factor_secret FROM `pengaturan_admin` WHERE id_admin = ?', [$auth['id']]);
        $secret = $settings['two_factor_secret'] ?? $this->generateBase32Secret();

        if (!$settings) {
            $this->db->insert('pengaturan_admin', [
                'id_admin' => $auth['id'],
                'email_alert_darurat' => 1,
                'ringkasan_laporan' => 1,
                'two_factor_enabled' => 0,
                'two_factor_secret' => $secret,
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
        } else {
            $this->db->update('pengaturan_admin', [
                'two_factor_secret' => $secret,
                'updated_at' => date('Y-m-d H:i:s'),
            ], 'id_admin = ?', [$auth['id']]);
        }

        $label = rawurlencode('Checked Pinjol:' . ($admin['email'] ?? 'admin'));
        $issuer = rawurlencode('Checked Pinjol');
        $otpauth = "otpauth://totp/{$label}?secret={$secret}&issuer={$issuer}";

        return Response::success([
            'secret' => $secret,
            'otpauth_url' => $otpauth,
            'qr_url' => 'https://api.qrserver.com/v1/create-qr-code/?size=220x220&data=' . rawurlencode($otpauth),
            'demo_otp' => strtolower((string) env('APP_ENV', 'production')) === 'local'
                ? trim((string) env('DEMO_2FA_SETUP_OTP', '482913'))
                : null,
        ]);
    }

    public function confirmTwoFactor(Request $request): Response
    {
        $this->ensureTwoFactorColumns();

        $auth = $request->user();
        if (!$auth || ($auth['type'] ?? '') !== 'admin') {
            return Response::error('Forbidden', 403);
        }

        $otp = trim((string) $request->input('otp', ''));
        if ($otp === '') {
            return Response::error('OTP wajib diisi', 422);
        }

        $settings = $this->db->fetchOne('SELECT two_factor_secret FROM `pengaturan_admin` WHERE id_admin = ?', [$auth['id']]);
        if (!$settings || empty($settings['two_factor_secret'])) {
            return Response::error('Setup 2FA belum tersedia', 400);
        }

        if (!$this->verifyTotpCode((string) $settings['two_factor_secret'], $otp, 'setup')) {
            return Response::error('OTP tidak valid', 400);
        }

        $this->db->update('pengaturan_admin', [
            'two_factor_enabled' => 1,
            'two_factor_confirmed_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ], 'id_admin = ?', [$auth['id']]);

        return Response::success(null, '2FA berhasil diaktifkan');
    }

    public function disableTwoFactor(Request $request): Response
    {
        $this->ensureTwoFactorColumns();

        $auth = $request->user();
        if (!$auth || ($auth['type'] ?? '') !== 'admin') {
            return Response::error('Forbidden', 403);
        }

        $this->db->update('pengaturan_admin', [
            'two_factor_enabled' => 0,
            'two_factor_secret' => null,
            'two_factor_confirmed_at' => null,
            'updated_at' => date('Y-m-d H:i:s'),
        ], 'id_admin = ?', [$auth['id']]);

        return Response::success(null, '2FA berhasil dinonaktifkan');
    }

    #[OA\Put(
        path: '/api/auth/change-password',
        summary: 'Mengubah password user/admin',
        tags: ['Auth'],
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['password_lama', 'password_baru'],
                properties: [
                    new OA\Property(property: 'password_lama', type: 'string'),
                    new OA\Property(property: 'password_baru', type: 'string')
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'Password berhasil diubah'),
            new OA\Response(response: 400, description: 'Password lama tidak cocok'),
            new OA\Response(response: 401, description: 'Unauthorized')
        ]
    )]
    public function changePassword(Request $request): Response
    {
        $this->ensureTwoFactorColumns();

        $auth = $request->user();
        if (!$auth) return Response::error('Unauthorized', 401);

        $isAdmin = $auth['type'] === 'admin';
        $table = $isAdmin ? 'admin' : 'user';
        $pk    = $isAdmin ? 'id_admin' : 'id_user';

        $record = $this->db->fetchOne("SELECT password_hash FROM $table WHERE $pk = ?", [$auth['id']]);

        if (!bcryptVerify($request->input('password_lama'), (string) $record['password_hash'])) {
            return Response::error('Password lama tidak cocok', 400);
        }

        if ($isAdmin) {
            $settings = $this->db->fetchOne(
                'SELECT two_factor_enabled, two_factor_secret FROM `pengaturan_admin` WHERE id_admin = ?',
                [$auth['id']]
            );

            if (($settings['two_factor_enabled'] ?? 0) && !empty($settings['two_factor_secret'])) {
                $otp = trim((string) $request->input('otp', ''));

                if ($otp === '') {
                    return Response::error('OTP wajib diisi karena 2FA aktif', 422);
                }

                if (!$this->verifyTotpCode((string) $settings['two_factor_secret'], $otp, 'password')) {
                    return Response::error('OTP tidak valid', 400);
                }
            }
        }

        $this->db->update($table, [
            'password_hash' => bcryptHash($request->input('password_baru')),
            'updated_at'    => date('Y-m-d H:i:s'),
        ], "$pk = ?", [$auth['id']]);

        return Response::success(null, 'Password berhasil diubah');
    }
}
