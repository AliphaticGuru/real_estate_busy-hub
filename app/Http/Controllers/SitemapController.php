<?php

namespace App\Http\Controllers;

use App\Enums\CompanyStatus;
use App\Enums\ListingStatus;
use App\Enums\ListingVerificationStatus;
use App\Models\Company;
use App\Models\Listing;
use App\Models\Professional;
use Carbon\CarbonInterface;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;

class SitemapController extends Controller
{
    private const CACHE_KEY = 'sitemap';

    private const CACHE_SECONDS = 3600;

    /**
     * The public URLs included in the sitemap.
     *
     * @return array<int, array{loc: string, lastmod: CarbonInterface}>
     */
    private function entries(): array
    {
        $entries = [
            ['loc' => url('/'), 'lastmod' => now()],
            ['loc' => url('/listings'), 'lastmod' => now()],
            ['loc' => url('/fees'), 'lastmod' => now()],
            ['loc' => url('/verification'), 'lastmod' => now()],
            ['loc' => url('/blog'), 'lastmod' => now()],
            ['loc' => url('/professionals'), 'lastmod' => now()],
        ];

        foreach ((new BlogController)->posts() as $post) {
            $entries[] = [
                'loc' => url('/blog/'.$post['slug']),
                'lastmod' => $post['published_at'],
            ];
        }

        foreach (Listing::query()
            ->where('status', ListingStatus::Published)
            ->where('verification_status', ListingVerificationStatus::Verified)
            ->get() as $listing) {
            $entries[] = [
                'loc' => url('/listings/'.$listing->slug),
                'lastmod' => $listing->verified_at ?? $listing->created_at,
            ];
        }

        foreach (Company::query()->where('status', CompanyStatus::Active)->get() as $company) {
            $entries[] = [
                'loc' => url('/companies/'.$company->slug),
                'lastmod' => now(),
            ];
        }

        foreach (Professional::query()->whereNotNull('credentials_verified_at')->get() as $professional) {
            $entries[] = [
                'loc' => url('/professionals/'.$professional->slug),
                'lastmod' => now(),
            ];
        }

        return $entries;
    }

    /**
     * The public content map of the site, cached for an hour.
     */
    public function index(): Response
    {
        /** @var array<int, array{loc: string, lastmod: CarbonInterface}> $entries */
        $entries = Cache::remember(self::CACHE_KEY, self::CACHE_SECONDS, fn (): array => $this->entries());

        $xml = '<?xml version="1.0" encoding="UTF-8"?>'.PHP_EOL;
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'.PHP_EOL;

        foreach ($entries as $entry) {
            $xml .= '    <url>'.PHP_EOL;
            $xml .= '        <loc>'.htmlspecialchars($entry['loc']).'</loc>'.PHP_EOL;
            $xml .= '        <lastmod>'.$entry['lastmod']->toIso8601String().'</lastmod>'.PHP_EOL;
            $xml .= '    </url>'.PHP_EOL;
        }

        $xml .= '</urlset>'.PHP_EOL;

        return response($xml, 200, ['Content-Type' => 'application/xml; charset=UTF-8']);
    }
}
