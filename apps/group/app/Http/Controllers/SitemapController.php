<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\View;

class SitemapController extends Controller
{
    /**
     * Serve a host-aware XML sitemap.
     *
     * Each entry's <lastmod> is resolved from the filemtime() of the underlying
     * Blade view where available, so the sitemap stays accurate without
     * manual edits when content changes.
     */
    public function __invoke(Request $request): Response
    {
        $host = $request->getHost();
        $base = $this->baseUrlFor($host);

        $entries = match (true) {
            str_starts_with($host, 'motivations.') => $this->motivationsEntries(),
            str_starts_with($host, 'storage.')     => $this->storageEntries(),
            str_starts_with($host, 'arms.')        => $this->armsEntries(),
            default                                 => $this->apexEntries(),
        };

        $xml  = '<?xml version="1.0" encoding="UTF-8"?>'."\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'."\n";

        foreach ($entries as $entry) {
            $loc        = $base . $entry['path'];
            $lastmod    = $entry['lastmod'] ?? date('Y-m-d');
            $changefreq = $entry['changefreq'] ?? 'monthly';
            $priority   = $entry['priority'] ?? '0.5';

            $xml .= "  <url>\n";
            $xml .= "    <loc>".htmlspecialchars($loc, ENT_XML1)."</loc>\n";
            $xml .= "    <lastmod>{$lastmod}</lastmod>\n";
            $xml .= "    <changefreq>{$changefreq}</changefreq>\n";
            $xml .= "    <priority>{$priority}</priority>\n";
            $xml .= "  </url>\n";
        }

        $xml .= '</urlset>'."\n";

        return response($xml, 200, [
            'Content-Type'  => 'application/xml; charset=UTF-8',
            'Cache-Control' => 'public, max-age=3600',
        ]);
    }

    /**
     * Build the canonical scheme+host for the current request. Production
     * hosts are returned verbatim; local/dev hosts fall back to the request
     * scheme+host so absolute URLs still resolve correctly in development.
     */
    private function baseUrlFor(string $host): string
    {
        $known = [
            'motivations.ranyati.co.za' => 'https://motivations.ranyati.co.za',
            'storage.ranyati.co.za'     => 'https://storage.ranyati.co.za',
            'arms.ranyati.co.za'        => 'https://arms.ranyati.co.za',
            'ranyati.co.za'             => 'https://ranyati.co.za',
            'www.ranyati.co.za'         => 'https://ranyati.co.za',
        ];

        return $known[$host] ?? rtrim(request()->getSchemeAndHttpHost(), '/');
    }

    private function motivationsEntries(): array
    {
        $map = [
            ['path' => '/',                                         'view' => 'motivations',                                  'priority' => '1.0', 'changefreq' => 'weekly'],
            ['path' => '/enquire',                                  'view' => 'enquire',                                      'priority' => '0.9', 'changefreq' => 'monthly'],
            ['path' => '/firearm-motivation-letter',                'view' => 'seo.motivations.firearm-motivation-letter',    'priority' => '0.9', 'changefreq' => 'monthly'],
            ['path' => '/firearm-licence-motivation-self-defence',  'view' => 'seo.motivations.self-defence',                 'priority' => '0.85','changefreq' => 'monthly'],
            ['path' => '/firearm-licence-motivation-sport-shooting','view' => 'seo.motivations.sport-shooting',               'priority' => '0.85','changefreq' => 'monthly'],
            ['path' => '/prs-shooting-south-africa',                'view' => 'seo.motivations.prs-shooting',                 'priority' => '0.8', 'changefreq' => 'monthly'],
            ['path' => '/firearm-licence-motivation-hunting',       'view' => 'seo.motivations.hunting',                      'priority' => '0.85','changefreq' => 'monthly'],
            ['path' => '/firearm-licence-renewal-south-africa',     'view' => 'seo.motivations.renewal',                      'priority' => '0.85','changefreq' => 'monthly'],
            ['path' => '/firearm-licence-appeal-south-africa',      'view' => 'seo.motivations.appeal',                       'priority' => '0.85','changefreq' => 'monthly'],
            ['path' => '/faq',                                      'view' => 'seo.motivations.faq',                          'priority' => '0.85','changefreq' => 'weekly'],
            ['path' => '/resources',                                'view' => 'resources.motivations.index',                  'priority' => '0.9', 'changefreq' => 'weekly'],
            ['path' => '/resources/about',                          'view' => 'resources.motivations.about',                  'priority' => '0.8', 'changefreq' => 'monthly'],
            ['path' => '/resources/firearm-licence-process',        'view' => 'resources.motivations.firearm-licence-process','priority' => '0.8', 'changefreq' => 'monthly'],
            ['path' => '/resources/firearms-control-act',           'view' => 'resources.motivations.firearms-control-act',   'priority' => '0.8', 'changefreq' => 'monthly'],
            ['path' => '/resources/services',                       'view' => 'resources.motivations.services',               'priority' => '0.8', 'changefreq' => 'monthly'],
            ['path' => '/resources/faq',                            'view' => 'resources.motivations.faq',                    'priority' => '0.8', 'changefreq' => 'weekly'],
            ['path' => '/resources/documents-required',             'view' => 'resources.motivations.documents-required',     'priority' => '0.7', 'changefreq' => 'monthly'],
        ];

        return $this->resolveLastmod($map);
    }

    private function storageEntries(): array
    {
        $map = [
            ['path' => '/',                                            'view' => 'storage-landing',               'priority' => '1.0', 'changefreq' => 'monthly'],
            ['path' => '/firearm-storage-pretoria',                    'view' => 'seo.storage.pretoria',          'priority' => '0.85','changefreq' => 'monthly'],
            ['path' => '/long-term-firearm-storage-south-africa',      'view' => 'seo.storage.long-term',         'priority' => '0.85','changefreq' => 'monthly'],
            ['path' => '/temporary-firearm-storage',                   'view' => 'seo.storage.temporary',         'priority' => '0.85','changefreq' => 'monthly'],
            ['path' => '/secure-firearm-storage-faq',                  'view' => 'seo.storage.faq',               'priority' => '0.85','changefreq' => 'weekly'],
            ['path' => '/resources',                                   'view' => 'resources.storage.index',       'priority' => '0.9', 'changefreq' => 'weekly'],
            ['path' => '/resources/about',                             'view' => 'resources.storage.about',       'priority' => '0.8', 'changefreq' => 'monthly'],
            ['path' => '/resources/safe-custody',                      'view' => 'resources.storage.safe-custody','priority' => '0.8', 'changefreq' => 'monthly'],
            ['path' => '/resources/fca-requirements',                  'view' => 'resources.storage.fca-requirements','priority' => '0.8', 'changefreq' => 'monthly'],
            ['path' => '/resources/faq',                               'view' => 'resources.storage.faq',         'priority' => '0.8', 'changefreq' => 'weekly'],
        ];

        return $this->resolveLastmod($map);
    }

    private function armsEntries(): array
    {
        return $this->resolveLastmod([
            ['path' => '/', 'view' => 'arms-landing', 'priority' => '1.0', 'changefreq' => 'daily'],
        ]);
    }

    private function apexEntries(): array
    {
        $map = [
            ['path' => '/',         'view' => 'welcome',       'priority' => '1.0', 'changefreq' => 'monthly'],
            ['path' => '/about',    'view' => 'seo.about',     'priority' => '0.9', 'changefreq' => 'monthly'],
            ['path' => '/services', 'view' => 'seo.services',  'priority' => '0.9', 'changefreq' => 'monthly'],
            ['path' => '/contact',  'view' => 'seo.contact',   'priority' => '0.85','changefreq' => 'monthly'],
            ['path' => '/faq',      'view' => 'seo.faq',       'priority' => '0.85','changefreq' => 'weekly'],
            ['path' => '/guides',   'view' => 'seo.guides',    'priority' => '0.85','changefreq' => 'weekly'],
        ];

        return $this->resolveLastmod($map);
    }

    /**
     * Replace each entry's "view" with a resolved lastmod date based on the
     * underlying Blade file's mtime. Falls back to today's date when the
     * view file cannot be located (defensive — should not happen in prod).
     */
    private function resolveLastmod(array $entries): array
    {
        return array_map(function (array $entry): array {
            $lastmod = null;

            if (! empty($entry['view'])) {
                try {
                    $path = View::getFinder()->find($entry['view']);
                    if ($path && is_file($path)) {
                        $lastmod = date('Y-m-d', filemtime($path));
                    }
                } catch (\Throwable $e) {
                    // view not found — fall through
                }
            }

            return [
                'path'       => $entry['path'],
                'priority'   => $entry['priority']   ?? '0.5',
                'changefreq' => $entry['changefreq'] ?? 'monthly',
                'lastmod'    => $lastmod ?? date('Y-m-d'),
            ];
        }, $entries);
    }
}
