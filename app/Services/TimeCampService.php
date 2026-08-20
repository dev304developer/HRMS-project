<?php

namespace App\Services;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use RuntimeException;

/**
 * Thin client for the TimeCamp REST API.
 *
 * Docs: https://developer.timecamp.com/
 * Get your API token in TimeCamp: avatar (top-right) -> Profile Settings ->
 * scroll to the bottom -> "Your programming API token".
 */
class TimeCampService
{
    public function __construct(
        private readonly ?string $token = null,
        private readonly string $baseUrl = 'https://app.timecamp.com/third_party/api',
    ) {
    }

    /**
     * Build from config/services.php (config('services.timecamp')).
     */
    public static function fromConfig(): self
    {
        return new self(
            token: config('services.timecamp.token'),
            baseUrl: config('services.timecamp.base_url'),
        );
    }

    /**
     * Fetch every user (person) in the TimeCamp account.
     *
     * @return array<int, array<string, mixed>>  Each item has keys like
     *         user_id, email, display_name, ...
     */
    public function users(): array
    {
        $response = $this->request()->get('/users');
        $response->throw();

        return $response->json() ?? [];
    }

    /**
     * A pre-authenticated HTTP client for TimeCamp.
     *
     * TimeCamp accepts the token in the Authorization header. If your account
     * uses the older query-param style, swap withHeaders() for withQueryParameters().
     */
    private function request(): PendingRequest
    {
        if (blank($this->token)) {
            throw new RuntimeException('TIMECAMP_API_TOKEN is not set. Add it to your .env file.');
        }

        return Http::baseUrl(rtrim($this->baseUrl, '/'))
            ->acceptJson()
            ->withHeaders(['Authorization' => $this->token])
            ->timeout(30);
    }
}
