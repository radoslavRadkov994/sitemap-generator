<?php

use GuzzleHttp\Psr7\Uri;

return [

    /*
    |--------------------------------------------------------------------------
    | Groups
    |--------------------------------------------------------------------------
    |
    | Here you may set many urls groups to make map for them.
    | Default is a first. That default group is main and get all route paths
    | and make GuzzleHttp\Psr7\Uri for every one.
    | Here you can set manual url paths or use function for get, set, generate them
    | or does not matter.
    */
    'groups' => [

        /*
        |--------------------------------------------------------------------------
        | Group
        |--------------------------------------------------------------------------
        |
        | That group contain urls. This urls are from one project
        | in this case that is main site.
        | Your site may have different sub directories like blog, kb and so on,
        | then make other groups.
        */
        'default' => [ // group by project

            'file' => 'sitemap.xml', // path in public folder - optional
            'path' => 'sitemaps/', // path in public folder
            'parameters' => [
                'image' => null,
            ],

            'urlsGroups' => [ // group by category

                "page" => app_routes(),

            ],

        ],

    ],

];

/**
 * Create default urls for default sitemap
 *
 * @return array with values GuzzleHttp\Psr7\Uri
 */
function app_routes(): array
{
    $collection = app('router')->getRoutes();
    $collection = (array) $collection;

    $routes = [];
    foreach($collection as $routesGroup) {
        if(isset($routesGroup['GET']))
            foreach($routesGroup['GET'] as $routePath) {
                $routes[] = new Uri(env('APP_URL') . $routePath);
            }

        break;
    }

    return $routes;
}
