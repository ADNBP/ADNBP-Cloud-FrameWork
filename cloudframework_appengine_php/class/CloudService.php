<?php
Class CloudService
{
    private $core;
    private $http = 'https://cloud.adnbp.com/h/api';
    function __construct(&$core,$http='https://cloud.adnbp.com/h/api')
    {
        $this->core = $core;
        $this->http = $http;
    }

    function url($add = '')
    {
        // analyze Default Country
        if (!$this->getConf("CloudServiceUrl"))
            $this->setConf("CloudServiceUrl", $this->_defaultCFURL);

        if (strpos($this->getConf("CloudServiceUrl"), "http") === false)
            $_url = "http://" . $_SERVER['HTTP_HOST'] . $this->getConf("CloudServiceUrl");
        else
            $_url = $this->getConf("CloudServiceUrl");

        if (strlen($add))
            $add = '/' . $add;
        return ($_url . $add);
    }

    /**
     * Call External Cloud Service Caching the result
     */
    function getCloudServiceResponseCache($rute, $data = null, $verb = 'GET', $extraheaders = null, $raw = false)
    {
        $_qHash = hash('md5', $rute . json_encode($data) . $verb);
        $ret = $this->getCache($_qHash);
        if (isset($_GET['reload']) || isset($_REQUEST['CF_cleanCache']) || $ret === false || $ret === null) {
            $ret = $this->getCloudServiceResponse($rute, $data, $verb, $extraheaders, $raw);

            // Only cache successful responses.
            $headers = $this->getCloudServiceResponseHeaders();
            if(is_array($headers) && isset($headers[0]) && strpos($headers[0],'OK')) {
                $this->setCache($_qHash, $ret);
            }
        }
        return ($ret);
    }


    function getCloudServiceResponseHeaders() {
        return $this->_responseHeaders;
    }
}