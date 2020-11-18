<?php


namespace R3F\SitemapGenerator;


class Generator
{
    /**
     * Generate sitemaps xml
     */
    public static function generate() {
        $sitemapConfig = config('sitemap-generator');

        // loop groups by project
        foreach ($sitemapConfig as $projectGroup) {
            SitemapRequest::create($projectGroup)
                ->checkResponses()
                ->write();
        }
    }
}
