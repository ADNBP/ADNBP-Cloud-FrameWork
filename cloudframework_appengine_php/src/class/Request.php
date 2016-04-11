<?php

Class Request
{
    protected $core;
    protected $http;
    public $responseHeaders;
    public $error = false;
    public $errorMsg = [];

    function __construct(Core &$core)
    {
        $this->core = $core;

    }

    /**
     * Call External Cloud Service Caching the result
     */
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
        $this->responseHeaders = null;

        $this->core->__p->add('Request->get: ', "$rute " . (($data === null) ? '{no params}' : '{with params}'), 'note');
        // Performance for connections
        $options = array('ssl'=>array('verify_peer' => false));
        $options['http']['ignore_errors'] ='1';
        $options['http']['header'] = 'Connection: close' . "\r\n";


        // Automatic send header for X-CLOUDFRAMEWORK-SECURITY if it is defined in config
        if (strlen($this->core->config->get("CloudServiceId")) && strlen($this->core->config->get("CloudServiceSecret")))
            $options['http']['header'] .= 'X-CLOUDFRAMEWORK-SECURITY: ' . $this->generateCloudFrameWorkSecurityString($this->core->config->get("CloudServiceId"), microtime(true), $this->core->config->get("CloudServiceSecret")) . "\r\n";

        // Extra Headers
        if ($extra_headers !== null && is_array($extra_headers)) {
            foreach ($extra_headers as $key => $value) {
                $options['http']['header'] .= $key . ': ' . $value . "\r\n";
            }
        }

        // Method
        $options['http']['method'] = $verb;

        // Content-type
        if ($verb != 'GET')
            if (stripos($options['http']['header'], 'Content-type') === false) {
                if ($raw) {
                    $options['http']['header'] .= 'Content-type: application/json' . "\r\n";
                } else {
                    $options['http']['header'] .= 'Content-type: application/x-www-form-urlencoded' . "\r\n";
                }
            }


        // Build contents received in $data as an array
        if (is_array($data))
            if ($verb == 'GET') {
                if (is_array($data)) {
                    if (strpos($rute, '?') === false) $rute .= '?';
                    else $rute .= '&';
                    foreach ($data as $key => $value) $rute .= $key . '=' . rawurlencode($value) . '&';
                }
            } else {
                if ($raw) {
                    if (stripos($options['http']['header'], 'application/json') !== false)
                        $build_data = json_encode($data);
                    else
                        $build_data = $data;
                } else {
                    $build_data = http_build_query($data);
                }
                $options['http']['content'] = $build_data;

                // You have to calculate the Content-Length to run as script
                $options['http']['header'] .= sprintf('Content-Length: %d', strlen($build_data)) . "\r\n";
            }

        // Context creation
        $context = stream_context_create($options);


        try {
            $ret = @file_get_contents($rute, false, $context);
            $this->responseHeaders = $http_response_header;
            if ($ret === false) $this->addError(error_get_last());
        } catch (Exception $e) {
            $this->addError(error_get_last());
            $this->addError($e->getMessage());
        }


        $this->core->__p->add('Request->get: ', '', 'endnote');
        return ($ret);
    }

    // time, has to to be microtime().
    function generateCloudFrameWorkSecurityString($id, $time = '', $secret = '')
    {
        $ret = null;
        if (!strlen($secret)) {
            $secArr = $this->core->config->get('CLOUDFRAMEWORK-ID-' . $id);
            if (isset($secArr['secret'])) $secret = $secArr['secret'];
        }
        if (!strlen($secret)) {
            $this->core->logs->add('conf-var CLOUDFRAMEWORK-ID-' . $id . ' missing.');
        } else {
            if (!strlen($time)) $time = microtime(true);
            $date = new \DateTime(null, new \DateTimeZone('UTC'));
            $time += $date->getOffset();
            $ret = $id . '__UTC__' . $time;
            $ret .= '__' . hash_hmac('sha1', $ret, $secret);
        }
        return $ret;
    }

    function addError($value)
    {
        $this->error = true;
        $this->core->errors->add($value);
        $this->errorMsg[] = $value;
    }
}