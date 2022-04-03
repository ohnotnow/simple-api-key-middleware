<?php

namespace Ohffs\SimpleApiKeyMiddleware\Tests;

use Illuminate\Http\Request;
use Ohffs\SimpleApiKeyMiddleware\SimpleApiKey;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Ohffs\SimpleApiKeyMiddleware\ApiKeyMiddleware;

class MiddlewareTest extends \Orchestra\Testbench\TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();

        config(['simple_api_keys.token_length' => 64]);
        config(['simple_api_keys.cache_enabled' => false]);
        config(['simple_api_keys.cache_ttl_seconds' => 60]);
    }

    /** @test */
    public function we_can_generate_an_api_key()
    {
        $key = SimpleApiKey::generate('a new api key');

        $this->assertNotNull($key->token);
        $this->assertNotNull($key->plaintext_token);
        [$id, $plaintextToken] = explode('-', $key->plaintext_token);
        $this->assertEquals($id, $key->id);
        $this->assertEquals(64, strlen($plaintextToken));
        $this->assertEquals('a new api key', $key->description);
    }

    /** @test */
    public function we_can_specify_a_length_for_the_api_key()
    {
        $key = SimpleApiKey::generate('a new api key', 42);

        [$id, $plaintextToken] = explode('-', $key->plaintext_token);
        $this->assertEquals(42, strlen($plaintextToken));
    }

    /** @test */
    public function api_key_tokens_should_be_random()
    {
        $keys = [];
        foreach (range(1, 100) as $_) {
            $keys[] = SimpleApiKey::generate()->plaintext_token;
        }

        $this->assertEquals(
            count($keys),
            count(array_unique($keys))
        );
    }

    /** @test */
    public function the_middleware_passed_for_a_valid_api_key()
    {
        $key = SimpleApiKey::generate();
        $request = new Request();

        $request->headers->add([
            'Authorization' => "Bearer {$key->plaintext_token}",
        ]);

        $middleware = new ApiKeyMiddleware();

        $middleware->handle($request, function ($request) {
            $this->assertTrue(true);
        });
    }

    /** @test */
    public function the_middleware_fails_for_an_invalid_api_key()
    {
        $request = new Request();

        $request->headers->add([
            'Authorization' => "Bearer not-a-valid-token",
        ]);

        $middleware = new ApiKeyMiddleware();

        try {
            $middleware->handle($request, function ($request) {
                $this->assertTrue(true);
            });
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
            $this->assertTrue(true);
            return;
        }

        $this->fail('Expected HttpException was not thrown.');
    }

    /** @test */
    public function the_middleware_fails_for_a_missing_api_key()
    {
        $request = new Request();

        $middleware = new ApiKeyMiddleware();

        try {
            $middleware->handle($request, function ($request) {
                $this->assertTrue(true);
            });
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
            $this->assertTrue(true);
            return;
        }

        $this->fail('Expected HttpException was not thrown.');
    }

    /** @test */
    public function the_service_provider_registers_an_alias_for_the_middleware()
    {
        $this->assertTrue(array_key_exists('simple-api-key', app('router')->getMiddleware()));
    }

    /** @test */
    public function we_can_enable_caching_of_key_lookups()
    {
        config(['simple_api_keys.cache_enabled' => true]);

        $key = SimpleApiKey::generate();
        $request = new Request();

        Cache::shouldReceive('has')->once()->andReturn(false);
        Cache::shouldReceive('put')->once()->with("token_cache_{$key->id}", true, config('simple_api_keys.cache_ttl_seconds'));

        $request->headers->add([
            'Authorization' => "Bearer {$key->plaintext_token}",
        ]);

        $middleware = new ApiKeyMiddleware();

        $middleware->handle($request, function ($request) {
            $this->assertTrue(true);
        });
    }

    /** @test */
    public function there_is_an_artisan_command_to_generate_a_key()
    {
        $this->artisan('simple-api-key:generate', ['description' => 'a new api key'])
            ->expectsOutput('The new api key is :')
            ->assertExitCode(0);

        $this->assertDatabaseHas('simple_api_keys', [
            'description' => 'a new api key',
        ]);
    }

    /** @test */
    public function there_is_an_artisan_command_to_remove_a_key()
    {
        $key = SimpleApiKey::generate('amazing key');
        $this->artisan('simple-api-key:remove', ['token' => $key->plaintext_token])
            ->expectsOutput('The token was removed from the database.')
            ->assertExitCode(0);

        $this->assertDatabaseMissing('simple_api_keys', [
            'description' => 'amazing key',
        ]);
    }

    /** @test */
    public function trying_to_remove_a_key_that_doesnt_exist_displays_an_error()
    {
        $key = SimpleApiKey::generate('amazing key');
        $this->artisan('simple-api-key:remove', ['token' => 'not the token'])
            ->expectsOutput('The token was not found in the database.')
            ->assertExitCode(1);

        $this->assertDatabaseHas('simple_api_keys', [
            'description' => 'amazing key',
        ]);
    }

    protected function getPackageProviders($app)
    {
        return [
            'Ohffs\SimpleApiKeyMiddleware\ApiKeyProvider',
        ];
    }
}
