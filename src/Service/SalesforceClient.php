<?php

namespace Flinty916\LaravelSalesforce\Service;

use Flinty916\LaravelSalesforce\Exceptions\SalesforceValidationException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use stdClass;

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

    protected function fetchInstanceUrlFromToken(): string
    {
        return config('salesforce.login_url');
    }

    public function get(string $uri, array $query = [])
    {
        $url = rtrim($this->instanceUrl, '/') . $uri;
        $response = Http::withToken($this->accessToken)
            ->acceptJson()
            ->get($url, $query);
        throw_if(!$response->ok(), \Exception::class, 'Salesforce GET failed: ' . $response->body());
        return json_decode($response->body());
    }

    public function post(string $uri, array|stdClass $data)
    {
        $url = rtrim($this->instanceUrl, '/') . $uri;
        $response = Http::withToken($this->accessToken)
            ->contentType('application/json')
            ->acceptJson()
            ->post($url, $data);
        if ($response->status() == 400) {
            $errorResponse = json_decode($response->body(), true)[0];
            throw new SalesforceValidationException($errorResponse['message'], $errorResponse['fields'], 400);
        }
        throw_if($response->status() > 399, \Exception::class, 'Salesforce POST failed: ' . $response->body());
        return json_decode($response->body());
    }

    public function put(string $uri, array|stdClass $data)
    {
        $url = rtrim($this->instanceUrl, '/') . $uri;

        $response = Http::withToken($this->accessToken)
            ->contentType('application/json')
            ->acceptJson()
            ->patch($url, $data);
        if ($response->status() == 400) {
            $errorResponse = json_decode($response->body(), true)[0];
            throw new SalesforceValidationException($errorResponse['message'], [], 400);
        }
        throw_if($response->status() > 399, \Exception::class, 'Salesforce PUT failed: ' . $response->body());
        return json_decode($response->body());
    }

    public function delete(string $uri)
    {
        $url = rtrim($this->instanceUrl, '/') . $uri;
        $response = Http::withToken($this->accessToken)
            ->contentType('application/json')
            ->acceptJson()
            ->delete($url);
        if ($response->status() == 400) {
            $errorResponse = json_decode($response->body(), true)[0];
            throw new SalesforceValidationException($errorResponse['message'], [], 400);
        }
        throw_if($response->status() > 399, \Exception::class, 'Salesforce DELETE failed: ' . $response->body());
        return json_decode($response->body());
    }
}
