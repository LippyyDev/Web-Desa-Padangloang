<?php

namespace App\Libraries;

use Firebase\JWT\JWT;
use Firebase\JWT\JWK;
use Firebase\JWT\Key;

/**
 * Firebase ID Token Verifier
 * 
 * Verifies Firebase ID tokens using Google's public keys (JWKS).
 * This replaces the previous insecure approach of just decoding the JWT payload.
 */
class FirebaseTokenVerifier
{
    /**
     * URL for Google's public keys used to verify Firebase ID tokens
     */
    private const GOOGLE_CERTS_URL = 'https://www.googleapis.com/robot/v1/metadata/x509/securetoken@system.gserviceaccount.com';

    /**
     * Firebase project ID — must match the 'aud' claim in the token
     */
    private string $projectId;

    /**
     * Cache duration for Google public keys (seconds)
     */
    private int $cacheTtl = 3600;

    public function __construct(string $projectId = 'webpadangloang')
    {
        $this->projectId = $projectId;
    }

    /**
     * Verify and decode a Firebase ID token.
     *
     * @param string $idToken The Firebase ID token (JWT) to verify
     * @return object Decoded token payload
     * @throws \Exception If verification fails
     */
    public function verify(string $idToken): object
    {
        // Fetch Google's public keys
        $publicKeys = $this->getGooglePublicKeys();

        // Decode and verify the JWT using Google's public keys
        // firebase/php-jwt handles signature verification, exp, iat checks
        $decoded = JWT::decode($idToken, $publicKeys);

        // Additional claim validations
        $this->validateClaims($decoded);

        return $decoded;
    }

    /**
     * Fetch Google's public keys for Firebase token verification.
     * Uses CI4 cache to avoid fetching on every request.
     *
     * @return array<string, Key> Array of Key objects keyed by kid
     * @throws \Exception If keys cannot be fetched
     */
    private function getGooglePublicKeys(): array
    {
        $cache = \Config\Services::cache();
        $cacheKey = 'firebase_google_public_keys';

        // Try cache first
        $cachedKeys = $cache->get($cacheKey);
        if ($cachedKeys !== null) {
            return $cachedKeys;
        }

        // Fetch fresh keys from Google
        $client = \Config\Services::curlrequest();
        
        try {
            $response = $client->get(self::GOOGLE_CERTS_URL, [
                'timeout' => 10,
            ]);
        } catch (\Exception $e) {
            throw new \Exception('Failed to fetch Google public keys: ' . $e->getMessage());
        }

        if ($response->getStatusCode() !== 200) {
            throw new \Exception('Failed to fetch Google public keys. HTTP status: ' . $response->getStatusCode());
        }

        $certs = json_decode($response->getBody(), true);
        if (!$certs || !is_array($certs)) {
            throw new \Exception('Invalid Google public keys response.');
        }

        // Convert X.509 certificates to Key objects
        $keys = [];
        foreach ($certs as $kid => $cert) {
            $keys[$kid] = new Key($cert, 'RS256');
        }

        if (empty($keys)) {
            throw new \Exception('No valid keys found in Google public keys.');
        }

        // Cache the keys (respect Cache-Control header if available, default 1 hour)
        $cache->save($cacheKey, $keys, $this->cacheTtl);

        return $keys;
    }

    /**
     * Validate additional claims in the decoded token.
     *
     * @param object $decoded Decoded JWT payload
     * @throws \Exception If claims are invalid
     */
    private function validateClaims(object $decoded): void
    {
        // Verify issuer
        $expectedIssuer = 'https://securetoken.google.com/' . $this->projectId;
        if (!isset($decoded->iss) || $decoded->iss !== $expectedIssuer) {
            throw new \Exception('Invalid token issuer.');
        }

        // Verify audience
        if (!isset($decoded->aud) || $decoded->aud !== $this->projectId) {
            throw new \Exception('Invalid token audience.');
        }

        // Verify subject (user ID) exists and is non-empty
        if (empty($decoded->sub)) {
            throw new \Exception('Missing or empty subject claim.');
        }
    }
}
