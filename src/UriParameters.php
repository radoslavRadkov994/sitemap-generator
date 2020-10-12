<?php


namespace R3F\SitemapGenerator;


use function PHPUnit\Framework\callback;

class UriParameters
{
    public $url;
    public $parameters;

    public function __construct($url, $parameters) {
        $this->url = $url;
        $this->parameters = $parameters;
        $this->getParams();
    }

    public static function create($url, $parameters = []) {
        return new static($url, $parameters);
    }

    public function getParams() {
        $this->headers = get_headers((string) $this->url);

        if($this->headers[0] == "HTTP/1.0 200 OK") {
            $this->status = '200';

            foreach ($this->parameters as $parameter => $default) {
                $this->getPageParams($parameter, $default);
            }
        } else {
            $this->status = $this->headers[0];
        }
    }

    public function getPageParams($parameter, $default) {
        if (false !== ($this->content = @file_get_contents((string) $this->url))) {
            switch ($parameter) {
                case "image":
                    $this->getImage($default);
                    break;
            }
        }
    }

    protected function getImage($default) {
        $regex = '/<meta .*=".*:+image" content="(.*)">/m';

        preg_match($regex, $this->content, $matches, PREG_OFFSET_CAPTURE);

        $this->image['url'] = $matches[1][0] ?? (($default && is_callable($default)) ? call_user_func($default) : null);
    }
}
