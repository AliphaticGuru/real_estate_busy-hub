<?php

namespace Tests\Feature;

use App\Models\Listing;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Tests\TestCase;

class TrustedProxiesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->app['router']->get('/__probe_ip', fn (Request $request): string => $request->ip());
    }

    public function test_trusted_proxies_are_never_wildcard_or_empty(): void
    {
        $config = (string) config('app.trusted_proxies');

        $this->assertNotSame('*', $config);
        $this->assertNotSame('', trim($config));
    }

    public function test_spoofed_forwarded_header_from_an_untrusted_client_is_ignored(): void
    {
        $response = $this->call('GET', '/__probe_ip', [], [], [], [
            'REMOTE_ADDR' => '203.0.113.99',
            'HTTP_X_FORWARDED_FOR' => '198.51.100.25',
        ]);

        $response->assertOk();

        $this->assertSame('203.0.113.99', $response->getContent());
    }

    public function test_trusted_proxy_forwarded_header_selects_the_real_client_ip(): void
    {
        $response = $this->call('GET', '/__probe_ip', [], [], [], [
            'REMOTE_ADDR' => $this->addressInFirstTrustedRange(),
            'HTTP_X_FORWARDED_FOR' => '198.51.100.25',
        ]);

        $response->assertOk();

        $this->assertSame('198.51.100.25', $response->getContent());
    }

    public function test_inquiry_throttle_buckets_by_forwarded_client_ip(): void
    {
        $listing = Listing::factory()->published()->create();
        $uri = '/'.route('inquiries.store', $listing->slug, false);

        $payload = [
            'type' => 'consultation',
            'name' => 'Test Client',
            'phone' => '+2348000000000',
            'email' => 'client@example.com',
            'message' => 'Please contact me.',
        ];

        $request = fn (string $clientIp) => $this->call('POST', $uri, $payload, [], [], [
            'REMOTE_ADDR' => $this->addressInFirstTrustedRange(),
            'HTTP_X_FORWARDED_FOR' => $clientIp,
        ]);

        for ($attempt = 1; $attempt <= 5; $attempt++) {
            $request('203.0.113.10')->assertRedirect();
        }

        $request('203.0.113.10')->assertStatus(429);

        // A different client IP behind the same proxy gets its own bucket.
        $request('203.0.113.11')->assertRedirect();
    }

    /**
     * Pick a usable address from the first configured trusted range so the
     * test follows whatever minimal list the deployment actually uses.
     */
    private function addressInFirstTrustedRange(): string
    {
        $range = explode(',', (string) config('app.trusted_proxies'))[0];
        [$network, $prefix] = array_pad(explode('/', trim($range)), 2, '32');

        $address = ip2long($network);
        $hostBits = 32 - (int) $prefix;
        $hostMask = $hostBits === 0 ? 0 : (1 << $hostBits) - 1;

        return long2ip(($address & ~$hostMask) | ($hostBits > 1 ? 2 : 0));
    }
}
