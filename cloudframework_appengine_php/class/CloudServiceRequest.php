<?php
include_once __DIR__.'/Request.php';

class CloudServiceRequest extends Request
{
    function __construct(Core $core)
    {
        parent::__construct($core);
        if (!$this->core->config->get("CloudServiceUrl"))
            $this->core->config->set("CloudServiceUrl", 'https://cloud.adnbp.com/api');

        $this->setServiceUrl($this->core->config->get("CloudServiceUrl"));
    }

    function setServiceUrl($url) {
        if(!strlen($url)) return false;
        if (strpos($url, 'http') !== 0)
            $url = (($_SERVER['HTTPS'] == 'off') ? 'http' : 'https') . $_SERVER['HTTP_HOST'] . "/$url";
        $this->http = $url;
    }

    function getServiceUrl($extra = '')
    {
        if (strpos($extra, 'http') ===0) return $extra;
        if (strlen($extra) && $extra[0]!='/')
            $extra = '/' . $extra;
        return ($this->http . $extra);
    }

    function getCache($rute, $data = null, $verb = 'GET', $extraheaders = null, $raw = false)
    {
        $_qHash = hash('md5', $rute . json_encode($data) . $verb);
        $ret = $this->core->cache->get($_qHash);
        if (isset($_GET['refreshCache']) ||  $ret === false || $ret === null) {
            $ret = $this->get($rute, $data, $verb, $extraheaders, $raw);
            // Only cache successful responses.
            if(is_array($this->responseHeaders) && isset($headers[0]) && strpos($headers[0],'OK')) {
                $this->core->cache->set($_qHash, $ret);
            }
        }
        return ($ret);
    }

    function get($rute, $data = null, $verb = 'GET', $extra_headers = null, $raw = false)
    {
        $rute = $this->getServiceUrl($rute);
        return parent::get($rute,$data,$verb,$extra_headers,$raw);
    }

}