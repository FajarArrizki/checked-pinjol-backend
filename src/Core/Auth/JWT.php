<?php

declare(strict_types=1);

namespace App\Core\Auth;

/**
 * Class JWT sederhana untuk menangani otentikasi API Cek Pinjol.
 */
final class JWT
{
    /**
     * Mengambil secret key dari environment.
     */
    private static function secret(): string
    {
        return (string) env('JWT_SECRET', 'pinjol_secret_key_ganti_di_production');
    }

    /**
     * Mengambil durasi expiry (default 24 jam).
     */
    private static function expiry(): int
    {
        return (int) env('JWT_EXPIRY', 86400);
    }

    /**
     * Encode payload menjadi token JWT.
     */
    public static function encode(array $payload): string
    {
        $header = self::base64UrlEncode(json_encode([
            'typ' => 'JWT',
            'alg' => 'HS256'
        ]));

        $payloadData = array_merge($payload, [
            'iat' => time(),
            'exp' => time() + self::expiry()
        ]);

        $payloadEncoded = self::base64UrlEncode(json_encode($payloadData));

        $signature = hash_hmac('sha256', "{$header}.{$payloadEncoded}", self::secret(), true);
        $signatureEncoded = self::base64UrlEncode($signature);

        return "{$header}.{$payloadEncoded}.{$signatureEncoded}";
    }

    /**
     * Decode dan validasi token JWT.
     */
    public static function decode(string $token): ?array
    {
        $parts = explode('.', $token);
        if (count($parts) !== 3) {
            return null;
        }

        [$header, $payload, $signature] = $parts;

        $expectedSignature = hash_hmac('sha256', "{$header}.{$payload}", self::secret(), true);
        $expectedSignatureEncoded = self::base64UrlEncode($expectedSignature);

        if (!hash_equals($expectedSignatureEncoded, $signature)) {
            return null;
        }

        $data = json_decode(self::base64UrlDecode($payload), true);

        if (!is_array($data)) {
            return null;
        }

        if (isset($data['exp']) && $data['exp'] < time()) {
            return null;
        }

        return $data;
    }

    private static function base64UrlEncode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    private static function base64UrlDecode(string $data): string
    {
        return base64_decode(strtr($data, '-_', '+/'));
    }
}
