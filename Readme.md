# Simple API key middleware for Laravel

This is a very basic middleware to check a bearer token on an incoming request.

## Installation
```sh
composer require ohffs/simple-api-key-middleware
php artisan migrate
```
The migration will create a new database table called `simple_api_keys`.  You can have a look in the packages `migrations/` directory to see the details.

## Usage

The package registers a middleware alias `simple-api-key` (mapped to `\Ohffs\SimpleApiKeyMiddleware\ApiKeyMiddleware`).  To protect a given route you could do :
```php
Route::get('/secret-stuff', [SecretStuffController::class, 'show'])->middleware('simple-api-key');
```
See the Laravel docs for more examples such as route groups or default middlewares.

To generate an API key from the CLI you can use the artisan command :
```sh
php artisan api-key:generate "Key for secret stuff"
```
Which will create the key with that description and show you the resulting token.  You can remove a key by doing :
```sh
php artisan api-key:remove 14-2398adshh2349addasd7213402
```
(where the 14-2398... is the token you generated).  You can also programmatically create a token :
```php
$apikey = \Ohffs\SimpleApiKeyMiddleware\SimpleApiKey::generate('My new Token');
echo $apikey->plaintext_token;
```
The `plaintext_token` is only available on freshly created tokens.

To make a request using the token, you pass it as a regular bearer-token.  Eg, with `curl` :
```sh
curl -H "Authorization: Bearer 12-sd8623hsdfi9823nsdf9sdf" https://example.com/secret-stuff
```
Or using Laravel's HTTP client :
```php
$response = Http::withToken('12-sd8623hsdfi9823nsdf9sdf')->get('https://example.com/secret-stuff');
```

### Configuration
The default configuration is as follows :
```php
    // default length of generated api keys
    'token_length' => env('API_KEY_LENGTH', 64),
    // enable caching of token lookups
    'cache_enabled' => env('API_KEY_CACHE_ENABLED', true),
    // time to cache token lookup results
    'cache_ttl_seconds' => env('API_KEY_CACHE_TTL', 60),
```
You can set those environment variables, or if you run `php artisan vendor:publish` and pick the config from this package you can edit the resulting `config/simple_api_keys.php` file.
By default the middleware will cache token lookups to save hitting the database for every one.  If you are not using a cache such as Redis then you might want to disable the caching.
