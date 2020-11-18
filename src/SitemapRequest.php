<?php


namespace R3F\SitemapGenerator;


use GuzzleHttp\Psr7\Uri;
use phpDocumentor\Reflection\Types\Object_;

class SitemapRequest
{
    protected $group;

    public function __construct($group) {
        $this->group = $group;
        $this->urlsGroups = $group['urlsGroups'];
        $this->storechPath = $this->public_path();
        $this->fileName = $this->fileName();
        $this->parameters = $group['parameters'];
    }

    /**
     * Create new sitemap request
     *
     * @param string see config/sitemap-generator.php
     *
     * @return SitemapRequest
     */
    public static function create($group) {
        return new static($group);
    }

    /**
     * Check urls responses
     *
     * @return $this
     */
    public function checkResponses() {
        // set ini to not fail request
        $this->setIni();

        // loop as category groups
        foreach ($this->urlsGroups as $urlsGroupKey => $urlsGroup) {
            // loop all urls in groups
            foreach ($urlsGroup as $urlKey => $url) {
                $this->urlsGroups[$urlsGroupKey][$urlKey] = $this->getSitemapParameters($url, $this->parameters);
            }

            // sort current group
            sort($this->urlsGroups[$urlsGroupKey]);
            // and remove results with same values
            $this->urlsGroups[$urlsGroupKey] = collect($this->urlsGroups[$urlsGroupKey])->unique('url');
        }

        return $this;
    }

    /**
     * Get all parameters from Uri
     *
     * @param $url
     * @param $getParams
     *
     * @return UriParameters
     */
    protected function getSitemapParameters($url, $getParams) {
        return UriParameters::create($url, $getParams);
    }

    /**
     * Write checked content
     */
    public function write() {
        $this->create_sitemap();
    }

    /**
     * Create folder
     */
    protected function createFolder($newPath = null) {
        $array = explode('/', ($newPath ?? $this->storechPath));

        $path = array_shift($array);

        do {
            if(!file_exists($path)) {
                mkdir($path, 0777);
            }

            $segment = array_shift($array);
            $path .= '/' . $segment;
        } while ($segment);
    }

    /**
     * Create sitemap
     */
    protected function create_sitemap() {
        // list of all created sitemaps in this request
        $createdSitemaps = [
            // ...
        ];

        // loop groups and render sitemap content
        foreach ($this->urlsGroups as $groupName => $urls) {
            $items = (array)$urls;
            $items = reset($items);
            $urlsArr = array_chunk($items, 1, true);

            foreach ($urlsArr as $key => $urls) {
                $sitemapContent = view('SitemapGenerator::sitemap')
                    ->with(['tags' => $urls])
                    ->render();

                // save content in file
                $generatedFile = $this->generateFile($groupName, $sitemapContent, $key+1);
                $createdSitemaps[$groupName][] = $this->getSitemapParameters($generatedFile, []);
            }

            if(count($createdSitemaps[$groupName]) > 0) {
                $sitemapContent = $sitemapContent = view('SitemapGenerator::sitemap')
                    ->with(['tags' => $createdSitemaps[$groupName]])
                    ->render();

                $createdSitemaps[$groupName] = $this->generateFile($groupName, $sitemapContent);
            }
        }

        // if created sitemaps is more that one group them
        if(count($createdSitemaps) > 1) {
            foreach ($createdSitemaps as $key => $createdSitemap) {
                $createdSitemaps[$key] = UriParameters::create(new Uri($createdSitemap));
            }

            $sitemapContent = view('SitemapGenerator::sitemap')
                ->with(['tags' => $createdSitemaps])
                ->render();

            // save content in file
            $mainSitemapAddress = $this->generateFile(null, $sitemapContent);
        } else {
            $mainSitemapAddress = reset($createdSitemaps);
        }

        // after complete sent new files to search engines
        $this->send_to_searchEngines($mainSitemapAddress);
    }

    // get public path of folder or file
    protected function public_path() {
        return public_path($this->group['path']);
    }

    // create xml file name
    protected function fileName() {
        return $this->group['name'] ?? 'sitemap.xml';
    }

    // sent sitemap url top search engines
    protected function send_to_searchEngines($sitemapUrl) {
        /*
        * Ping Script Sitemap
        * Submit your site maps automatically to Google, Bing.MSN and Ask
        */

//            Location To Sitemap File

//            // cUrl handler to ping the Sitemap submission
//            function myCurl($url) {
//                $ch = curl_init($url);
//                curl_setopt($ch, CURLOPT_HEADER, 0);
//                curl_exec($ch);
//                $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
//                curl_close($ch);
//                return $httpCode;
//            }
//
//            //Sitemap For  Google
//            $url = "http://www.google.com/webmasters/sitemaps/ping?sitemap=" . $sitemapUrl;
//            $returnCode = myCurl($url);
//
//            //Sitemap For  Bing / MSN
//            $url = "http://www.bing.com/ping?siteMap=" . $sitemapUrl;
//            $returnCode = myCurl($url);
//
//            //Sitemap For ASK
//            $url = "http://submissions.ask.com/ping?sitemap=" . $sitemapUrl;
//            $returnCode = myCurl($url);
//
//            //Sitemap For Yahoo
//            $url =  "http://search.yahooapis.com/SiteExplorerService/V1/updateNotification?appid=YahooDemo&url=". $sitemapUrl;
//            $returnCode = myCurl($url);
    }

    public function generateFile($groupName, $content, $currentPage = null) {
        $newPath = $this->storechPath . (($groupName) ? $groupName . '/' : '');

        $fileName = pathinfo($this->fileName);

        if($currentPage != null) {
            $fileName['filename'] .= '-' . $currentPage;
            $newPath .= 'pages/';
        }

        $this->createFolder($newPath);
        $newPath .= $groupName . '-' . $fileName['filename'] . '.' . $fileName['extension'];

        $this->create_new_sitemap($newPath, $content);

        return url(str_replace(public_path() . '\\', '', $newPath));
    }

    protected function create_new_sitemap($newPath, $content) {
        $this->create_old_sitemap($newPath);

        file_put_contents($newPath, $content);

        return $newPath;
    }

    protected function create_old_sitemap($newPath) {
        list($pathAndFileName, $extension) = explode('.', $newPath);
        $oldPath = ($pathAndFileName . '-old-' . date('D-d-M-Y h-s') . '.' . $extension);

        if (file_exists($newPath)) {
            chmod($this->storechPath, 0777);
            chmod($newPath, 0777);
            rename($newPath, $oldPath);
        }
    }

    protected function setIni() {
        //setting memory limit so command won't fail
        ini_set("memory_limit", "-1");
        set_time_limit(0);
        ini_set('max_execution_time', 0);
        ignore_user_abort(true);
    }
}
