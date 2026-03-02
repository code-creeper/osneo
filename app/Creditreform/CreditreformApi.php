<?php

namespace App\Creditreform;

use Exception;
use Http;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Client\Response;
use Log;

class CreditreformApi
{
    private string $baseUrl;
    private string $authServiceUrl;
    private string $clientId;
    public string $accessToken = '';

    public function __construct()
    {
        $this->baseUrl = config('services.creditreform.base_url');
        $this->authServiceUrl = config('services.creditreform.auth_service_url');
        $this->clientId = config('services.creditreform.client_id');
    }

    /**
     * @throws Exception
     */
    public function authenticate(): string
    {
        $response = Http::asForm()
            ->post("$this->authServiceUrl/protocol/openid-connect/token", [
                'scope' => 'openid',
                'grant_type' => 'client_credentials',
                'client_id' => config('services.creditreform.oauth_client_id'),
                'client_secret' => config('services.creditreform.client_secret'),
            ]);

        if ($response->failed()){
            throw new Exception('Access token could not be retrieved');
        }

        $this->accessToken = $response['access_token'];

        return $this->accessToken;
    }

    public function login(): void
    {
        $loginResponse = $this->sendRequest('/api/v1/account/login', 'get');

        if ($loginResponse->successful()) {
            $this->clientId = $loginResponse->json('records.0.clientId');
        } else {
            throw new \Exception('Login failed');
        }
    }

    public function getAccessToken(): string
    {
        if ($this->accessToken){
            return $this->accessToken;
        }

        return $this->authenticate();
    }

    public function refreshAccessToken(): string
    {
        return $this->authenticate();
    }

    /**
     * @throws RequestException
     * @throws Exception
     */
    public function sendRequest(string $endpoint, string $method = 'post', array $data = []): Response
    {
        $method = strtoupper($method);

        if ( ! in_array(strtolower($method), ['get', 'put', 'post', 'delete'])) {
            throw new Exception("Method $method is not supported");
        }

        try {
            $response = Http::withToken($this->getAccessToken())
                ->withHeaders([
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                ])
                ->baseUrl($this->baseUrl)
                ->retry(2, 0, function (Exception $exception, PendingRequest $request) {
                    if ( ! $exception instanceof RequestException || $exception->response->status() !== 401) {
                        return false;
                    }

                    $request->withToken($this->refreshAccessToken());
                    return true;
                })
                ->{$method}($endpoint, $data);
        } catch (RequestException $exception){
            $response = $exception->response;
        }

        if (isset($data['documentBytes'])) {
            $data['documentBytes'] = str($data['documentBytes'])->limit()->value();
        }

        Log::channel('creditreform')->debug(
            $response->status(). " $method " . $response->effectiveUri()->getPath(). " \n".
            "Response: \n".
            json_encode($response->json()) . " \n".
            "Request: \n".
            json_encode($data)
        );

        $response->throwIfClientError();

        return $response;
    }

    /**
     * @throws Exception
     */
    public function createStructuredInvoice(array $data = []): string
    {
        $data['clientId'] = $this->clientId;
        $response = $this->sendRequest('/api/v1/invoices/structured', 'post', $data);

        return $response->json();
    }

    /**
     * @throws Exception
     */
    public function createPayment(array $data = []): string
    {
        $data['clientId'] = $this->clientId;
        $response = $this->sendRequest('/api/v1/payments', 'post', $data);

        return $response->json();
    }

    /**
     * @throws Exception
     */
    public function getInvoice(string $invoiceId): mixed
    {
        $response = $this->sendRequest("/api/v1/invoices/$invoiceId", 'get');

        if ($response->successful()) {
            return $response->json();
        }

        return null;
    }

    /**
     * @throws Exception
     */
    public function cancelInvoice(string $invoiceId): mixed
    {
        $response = $this->sendRequest("/api/v1/invoices/$invoiceId/cancel", 'post');

        if ($response->successful()) {
            return $response->json();
        }

        return null;
    }

    /**
     * @throws Exception
     */
    public function writeOffInvoice(string $invoiceId): mixed
    {
        $response = $this->sendRequest("/api/v1/invoices/$invoiceId/writeOff", 'post');

        if ($response->successful()) {
            return $response->json();
        }

        return null;
    }

    /**
     * @throws Exception
     */
    public function deleteDunningStop(string $invoiceId): mixed
    {
        $response = $this->sendRequest("/api/v1/invoices/$invoiceId/dunningStop", 'delete');

        return $response->json();
    }

    /**
     * @throws Exception
     */
    public function createDunningStop(string $invoiceId, string $date): mixed
    {
        $response = $this->sendRequest("/api/v1/invoices/$invoiceId/dunningStop", 'post', [
            'untilDate' => $date
        ]);

        if ($response->successful()) {
            return $response->json();
        }

        return null;
    }

    /**
     * @throws Exception
     */
    public function refundPayment(array $data): mixed
    {
        $data['clientId'] = $this->clientId;

        $response = $this->sendRequest("/api/v1/payments/refund", 'post', $data);

        if ($response->successful()) {
            return $response->json();
        }

        return null;
    }
}
