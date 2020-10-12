<?php


namespace R3F\SitemapGenerator;


use GuzzleHttp\Psr7\Uri;
use phpDocumentor\Reflection\Types\Object_;

class Sitemap
{
    protected $group;

    public function __construct($group) {
        $this->group = $group;
        $this->urlsGroups = $group['urlsGroups'];
        $this->path = $this->public_path();
        $this->fileName = $this->fileName();
        $this->parameters = $group['parameters'];
    }

    public static function create($group) {
        return new static($group);
    }

    protected function public_path() {
        return public_path($this->group['path']);
    }

    protected function fileName() {
        return $this->group['name'] ?? 'sitemap.xml';
    }

    public function checkResponses() {
        $this->setIni();

        foreach ($this->urlsGroups as $urlsGroupKey => $urlsGroup) {
            foreach ($urlsGroup as $urlKey => $url) {
                $this->urlsGroups[$urlsGroupKey][$urlKey] = $this->getSitemapParameters($url, $this->parameters);
            }

            sort($this->urlsGroups[$urlsGroupKey]);
            $this->urlsGroups[$urlsGroupKey] = collect($this->urlsGroups[$urlsGroupKey])->unique('url');
        }

        return $this;
    }

    public function write() {
        $this->createFolder();
        $this->create_sitemap();
    }

    protected function create_sitemap() {
        $createdSitemaps = [

        ];

        foreach ($this->urlsGroups as $groupName => $urls) {
            $sitemapContent = view('SitemapGenerator::sitemap')
                ->with(['tags' => $urls])
                ->render();

            $createdSitemaps[] = $this->generateFile($groupName, $sitemapContent);
        }

        if(count($createdSitemaps) > 1) {
            foreach ($createdSitemaps as $key => $createdSitemap) {
                $createdSitemaps[$key] = UriParameters::create(new Uri($createdSitemap));
            }

            $sitemapContent = view('SitemapGenerator::sitemap')
                ->with(['tags' => $createdSitemaps])
                ->render();

            $mainSitemapAddress = $this->generateFile(null, $sitemapContent);
        } else {
            $mainSitemapAddress = reset($createdSitemaps);
        }

        $this->send_to_searchEngines($mainSitemapAddress);
    }

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

    public function generateFile($groupName, $content) {
        $newPath = ($this->path . (($groupName) ? $groupName . '-' : '') . $this->fileName);

        $this->create_new_sitemap($newPath, $content);

        return env('APP_URL') . (str_replace(public_path() . '\\', '', $newPath));
    }

    protected function createFolder() {
        $array = explode('/', $this->path);
        $path = array_shift($array);

        foreach ($array as $value) {
            $path .= $value . '/';

            if(!file_exists($path)) {
                mkdir($path, 0777);
            }
        }
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
            chmod($this->path, 0777);
            chmod($newPath, 0777);
            rename($newPath, $oldPath);
        }
    }

    protected function getSitemapParameters($url, $getParams) {
        return UriParameters::create($url, $getParams);
    }

    protected function setIni() {
        //setting memory limit so command won't fail
        ini_set("memory_limit", "-1");
        set_time_limit(0);
        ini_set('max_execution_time', 0);
        ignore_user_abort(true);
    }
}
