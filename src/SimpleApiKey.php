<?php

namespace Ohffs\SimpleApiKeyMiddleware;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SimpleApiKey extends Model
{
    use HasFactory;

    protected $hidden = [
        'token',
    ];

    public static function remove($token)
    {
        if (! str_contains($token, '-')) {
            return false;
        }

        [$id, $token] = explode('-', $token);

        if (! is_numeric($id)) {
            return false;
        }

        if (! is_string($token)) {
            return false;
        }

        $key = self::find(intval($id));

        if (! $key) {
            return false;
        }

        $key->delete();

        return true;
    }

    public static function checkValidToken(string $token): bool
    {
        if (! str_contains($token, '-')) {
            return false;
        }

        [$id, $token] = explode('-', $token);

        if (! is_numeric($id)) {
            return false;
        }

        if (! is_string($token)) {
            return false;
        }

        if ((new self())->cacheExistsForToken($id)) {
            return (new self())->getCachedTokenResult($id);
        }

        $key = self::find(intval($id));
        if (! $key) {
            (new self())->cacheTokenLookupResult($id, false);
            return false;
        }

        if (decrypt($key->token) !== $token) {
            (new self())->cacheTokenLookupResult($id, false);
            return false;
        }

        (new self())->cacheTokenLookupResult($id, true);

        return true;
    }

    protected function cacheTokenLookupResult($tokenId, bool $result): void
    {
        if (config('simple_api_keys.cache_enabled')) {
            cache()->put("token_cache_{$tokenId}", $result, config('simple_api_keys.cache_ttl_seconds', 60));
        }
    }

    protected function cacheExistsForToken($tokenId): bool
    {
        if (config('simple_api_keys.cache_enabled')) {
            return cache()->has("token_cache_{$tokenId}");
        }

        return false;
    }

    protected function getCachedTokenResult($tokenId): bool
    {
        if (config('simple_api_keys.cache_enabled')) {
            return cache()->get("token_cache_{$tokenId}");
        }

        throw new \Exception('Cache is not enabled on simple-api-key middleware but tried to access it');
    }

    public static function generate(?string $description = null, ?int $length = null): self
    {
        $key = new self();
        $plaintextToken = $key->makeRandomString(intval($length ?? config('simple_api_keys.token_length')) / 2);
        $key->token = encrypt($plaintextToken);
        $key->description = $description;
        $key->save();

        $key->plaintext_token = $key->id . '-' . $plaintextToken;

        return $key;
    }

    protected function makeRandomString(int $length): string
    {
        return $this->randomiseStringCase(bin2hex(random_bytes($length)));
    }

    protected function randomiseStringCase(string $string): string
    {
        for ($i = 0, $c = strlen($string); $i < $c; $i++) {
            $string[$i] = rand(0, 100) > 50 ? strtoupper($string[$i]) : strtolower($string[$i]);
        }

        return $string;
    }
}
