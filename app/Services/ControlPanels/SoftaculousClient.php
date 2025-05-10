<?php

namespace App\Services\ControlPanels;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Log;

class SoftaculousClient
{
    protected $client;
    protected $apiUrl;
    protected $apiToken;

    public function __construct()
    {
        $this->client = new Client();
        $this->apiUrl = config('services.softaculous.api_url');
        $this->apiToken = config('services.softaculous.api_token');
    }

    public function installScript($domain, $scriptId, $options = [])
    {
        $endpoint = '/install';
        $params = [
            'domain' => $domain,
            'script' => $scriptId,
            'options' => json_encode($options),
        ];

        return $this->makeApiCall($endpoint, $params);
    }

    protected function makeApiCall($endpoint, $params)
    {
        try {
            $response = $this->client->request('POST', $this->apiUrl . $endpoint, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->apiToken,
                ],
                'form_params' => $params,
            ]);

            $result = json_decode($response->getBody(), true);

            if (isset($result['success']) && $result['success'] === true) {
                Log::info("Softaculous API call successful", ['endpoint' => $endpoint, 'params' => $params]);
                return true;
            } else {
                Log::error("Softaculous API call failed", ['endpoint' => $endpoint, 'params' => $params, 'response' => $result]);
                return false;
            }
        } catch (GuzzleException $e) {
            Log::error("Softaculous API call error", ['endpoint' => $endpoint, 'params' => $params, 'error' => $e->getMessage()]);
            return false;
        }
    }
}