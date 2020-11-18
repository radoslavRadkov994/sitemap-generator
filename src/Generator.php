<?php


namespace R3F\SitemapGenerator;

use GuzzleHttp\Psr7\Uri;

class Generator
{
    /**
     * Generate sitemaps xml
     */
    public static function generate() {
        $sitemapConfig = static::config();

        // loop groups by project
        foreach ($sitemapConfig as $projectGroup) {
            SitemapRequest::create($projectGroup)
                ->checkResponses()
                ->write();
        }
    }

    public static function config() {
        return [
            'default' => [ // group by project

                'file' => 'sitemap.xml', // path in public folder - optional
                'path' => 'sitemaps/', // path in public folder
                'parameters' => [
                    'image' => null,
                ],

                'urlsGroups' => [ // group by category

                    "pages" => self::app_routes(),

                ],

            ],

//    'blog' => [ // group by project
//
//        'file' => 'sitemap.xml', // path in public folder - optional
//        'path' => 'sitemaps/blog/', // path in public folder
//        'parameters' => [
//            'image' => null,
//        ],
//
//        'urlsGroups' => [ // group by category
//
//            "posts" => [
//                'https://www.scalahosting.com/blog/guaranteed-email-deliverability-with-spanel/',
//                'https://www.scalahosting.com/sshield.html',
//                'https://www.scalahosting.com/swordpress-manager.html',
//            ],//blog_posts(),
//
////            "pages" => [
////                'https://www.scalahosting.com/blog/guaranteed-email-deliverability-with-spanel/',
////            ],//blog_pages(),
////
////            "attachment" => [
////                'https://www.scalahosting.com/blog/guaranteed-email-deliverability-with-spanel/',
////            ],//blog_attachments(),
////
////            "post_tags" => [
////                'https://www.scalahosting.com/blog/guaranteed-email-deliverability-with-spanel/',
////            ],//blog_post_tags(),
////
////            "author" => [
////                'https://www.scalahosting.com/blog/guaranteed-email-deliverability-with-spanel/',
////            ],//blog_authors(),
//
//        ],
//
//    ],

//    'kb' => [ // group by project
//
//        'file' => 'sitemap.xml', // path in public folder - optional
//        'path' => 'sitemaps/kb/', // path in public folder
//        'parameters' => [
//            'image' => null,
//        ],
//
//        'urlsGroups' => [ // group by category
//
//            "posts" => [
//                'https://www.scalahosting.com/blog/guaranteed-email-deliverability-with-spanel/',
//            ],//kb_posts(),
//
//            "pages" => [
//                'https://www.scalahosting.com/blog/guaranteed-email-deliverability-with-spanel/',
//            ],//kb_pages(),
//
//            "attachment" => [
//                'https://www.scalahosting.com/blog/guaranteed-email-deliverability-with-spanel/',
//            ],//kb_attachments(),
//
//            "post_tags" => [
//                'https://www.scalahosting.com/blog/guaranteed-email-deliverability-with-spanel/',
//            ],//kb_post_tags(),
//
//            "author" => [
//                'https://www.scalahosting.com/blog/guaranteed-email-deliverability-with-spanel/',
//            ],//kb_authors(),
//
//        ],
//
//    ],
        ];
    }

    /**
     * Create default urls for default sitemap
     *
     * @return array with values GuzzleHttp\Psr7\Uri
     */
    public static function app_routes(): array
    {
        $collection = app('router')->getRoutes();

        $routes = [];
        foreach($collection->getRoutesByName() as $routePath) {
            if(in_array('web', $routePath->action['middleware'])) {
                $routes[] = new Uri(url($routePath->uri()));
            }
        }

        return dd($routes);
    }
}
