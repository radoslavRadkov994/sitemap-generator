<?php


namespace R3F\SitemapGenerator;


class Generator
{
    public static function generate() {
        $config = config('sitemap-generator');

        foreach ($config['groups'] as $sitemapGroup) {
            Sitemap::create($sitemapGroup)
                ->checkResponses()
                ->write();
        }
    }
}
