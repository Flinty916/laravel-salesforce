<?php

namespace Flinty916\LaravelSalesforce\Service;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SalesforceClient
{
    protected string $instanceUrl;
    protected string $accessToken;

    public function __construct()
    {
        $this->authenticate();
    }

    protected function authenticate(): void
    {
        $cacheKey = config('salesforce.cache.key_prefix') . 'auth';

        $auth = Cache::remember($cacheKey, config('salesforce.cache.token_ttl'), function () {
            $response = Http::asForm()->post(config('salesforce.login_url') . '/services/oauth2/token', [
                'grant_type'    => 'password',
                'client_id'     => config('salesforce.client_id'),
                'client_secret' => config('salesforce.client_secret'),
                'username'      => config('salesforce.username'),
                'password'      => config('salesforce.password') . config('salesforce.security_token'),
            ]);

            throw_if(!$response->ok(), \Exception::class, 'Salesforce auth failed: ' . $response->body());

            return [
                'access_token' => $response['access_token'],
                'instance_url' => $response['instance_url'],
            ];
        });

        $this->accessToken = $auth['access_token'];
        $this->instanceUrl = $auth['instance_url'];
    }

    protected function fetchInstanceUrlFromToken(string $token): string
    {
        return config('salesforce.login_url');
    }

    protected function request(string $method, string $uri, array $options = [])
    {
        $url = rtrim($this->instanceUrl, '/') . '/services/data/v' . config('salesforce.api_version') . $uri;

        Log::debug($url);
        Log::debug($this->accessToken);

        $response = Http::withToken($this->accessToken)
            ->acceptJson()
            ->retry(1, 100)
            ->{$method}($url, $options);

        if ($response->unauthorized()) {
            Cache::forget(config('salesforce.cache.key_prefix') . 'access_token');
            $this->authenticate();
            return $this->request($method, $uri, $options);
        }

        return $response;
    }

    public function get(string $uri, array $query = [])
    {
        Log::debug(json_encode($query));
        $url = rtrim($this->instanceUrl, '/') . '/services/data/v' . config('salesforce.api_version') . $uri;
        Log::debug($url);
        $response = Http::withToken($this->accessToken)
            ->acceptJson()
            ->get($url, $query);
        throw_if(!$response->ok(), \Exception::class, 'Salesforce GET failed: ' . $response->body());
        return json_decode($response->body());
    }

    public function post(string $uri, array $data = [])
    {
        return $this->request('post', $uri, $data);
    }

    public function put(string $uri, array $data = [])
    {
        return $this->request('put', $uri, $data);
    }

    public function delete(string $uri)
    {
        return $this->request('delete', $uri);
    }
}
