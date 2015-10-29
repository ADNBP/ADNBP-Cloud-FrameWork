<?php
namespace CloudFramework\Service\Notifications;

use CloudFramework\Service\Notifications\Interfaces\Singleton;

/**
 * Class Notifier
 * @package CloudFramework\Service\Notifications
 * @author Fran López <fl@bloombees.com>
 */
class Notifier extends Singleton
{
    const APNS_MESSAGE_DELAY = 1000;
    const GCM_MESSAGE_DELAY = 100;

    /**
     * Messages queue
     * @var array
     */
    protected $messages = array();

    /**
     * Security configuration
     * @var array
     */
    protected $security = array();

    /**
     * Errors stack
     * @var array
     */
    protected $log = array();

    /**
     * Responses stack
     * @var array
     */
    protected $results = array();

    /**
     * Last error traced
     * @var string
     */
    protected $lastError = '';

    public function __construct($appName = 'CloudFramework', array $config = null)
    {
        $this->loadTs = microtime(true);
        $this->security = $this->getSecurityInfo($appName);
        $this->addTrace("Initialize Notifier");
        if (null !== $config) {
            $this->addTrace("Configure parameters");
            $this->setup($config);
        }
    }

    /**
     * @param string $app
     * @return array|bool
     */
    private function getSecurityInfo($app)
    {
        global $adnbp;
        $config = $adnbp->getConf("CLOUDFRAMEWORK-ID-" . $app);
        if (null !== $config) {
            $adnbp->loadClass('api/RESTful');
            $api = new \RESTful();
            $api->checkCloudFrameWorkSecurity(0, $app);
            return $api->getCloudFrameWorkSecurityInfo();
        } else {
            $this->addError('Empty configuration!!!');
            return false;
        }
    }

    /**
     * Setup configuration for connectors
     * @param array $config
     */
    public function setup(array $config = null)
    {
        if (null !== $config) {
            $this->addTrace("Add configurations");
            $this->security = array_merge(is_array($this->security) ? $this->security : array('config' => $this->security), $config);
        }
    }

    /**
     * Trace logs
     * @param string $logTrace
     */
    private function addTrace($logTrace)
    {
        $this->log[] = array(
            "message" => $logTrace,
            "ts" => $this->stepTs(),
        );
    }

    /**
     * Trace errors
     * @param string $message
     */
    private function addError($message)
    {
        $this->lastError = $message;
        $this->addTrace('[ERROR] ' . $message);
    }

    /**
     * Get last error ocurred
     * @return string
     */
    public function getLastError()
    {
        return $this->lastError;
    }

    /**
     * Prepare a stream context connection
     * @param string $source
     * @param array $options
     * @return resource|null
     */
    private function prepareConection($source, array $options = array())
    {
        $this->addTrace("Prepare {$source} connection resource");
        return stream_context_create($options);
    }

    /**
     * Prepare a socket to send push notifications
     * @param $resource
     * @param array $options
     * @return null|resource
     */
    private function prepareAPNSSocket($resource, array $options)
    {
        $socket = null;
        if ($this->checkSocketOptions($options)) {
            $errorCode = $errorMessage = null;
            if (@file_exists($options['certificate'])) {
                stream_context_set_option($resource, 'ssl', 'local_cert', $options['certificate']);
                stream_context_set_option($resource, 'ssl', 'passphrase', $options['certificatePassPhrase']);

                try {
                    $socket = stream_socket_client($options['url'], $errorCode, $errorMessage, 60, STREAM_CLIENT_CONNECT | STREAM_CLIENT_PERSISTENT, $resource);
                } catch (\Exception $e) {
                    $this->addError($e->getMessage());
                }
            } else {
                $this->addError('Certificate ' . $options['certificate'] . ' not valid or not exists');
            }
        } else {
            $this->addError('Needed options not configured to send push notifications with APNS');
        }
        return $socket;
    }

    /**
     * Checks options array for APNS
     * @param array $options
     * @return bool
     */
    private function checkSocketOptions(array $options)
    {
        return (array_key_exists('certificate', $options) && array_key_exists('certificatePassPhrase', $options));
    }

    /**
     * Send GCM push message
     * @param $type
     * @param $conn
     */
    protected function sendGCMMessage($type, $conn)
    {
        $sended = false;
        $result = @file_get_contents($this->security[$type]['url'], false, $conn);
        $json_result = json_decode($result, true);
        if (false === $result) {
            $this->addError('Unknow error when trying execute push notification');
        } elseif (null !== $json_result && !$json_result['success']) {
            $this->addError("Push response: " . $result);
        } else {
            $sended = true;
        }
        return $sended;
    }

    /**
     * Close a resource
     * @param resource $conn
     */
    private function closeConnectionOrSocket($conn)
    {
        if (null !== $conn) {
            @fclose($conn);
        }
    }

    /**
     * Add messages to push
     * @param string $type
     * @param string $message
     * @param string $device
     * @param int $pushType
     * @param int $badge
     * @param array $extra
     */
    public function addMessage($type, $message = '', $device = null, $pushType = 0, $badge = 0, $extra = array())
    {
        if (!array_key_exists($type, $this->messages)) {
            $this->addTrace("Create {$type} messages queue");
            $this->messages[$type] = array();
        }
        $this->messages[$type][] = array(
            "message" => $message,
            "ts" => microtime(true),
            "sended" => false,
            "device" => $device,
            "type" => $pushType,
            "badge" => $badge,
            "extra" => $extra,
        );
        $this->addTrace("Message queue for {$type}");
    }

    /**
     * Get errors
     * @return array
     */
    public function getLog()
    {
        return $this->log;
    }

    private function stepTs()
    {
        $now = microtime(true);
        $ts = round($now - $this->loadTs, 5);
        $this->loadTs = $now;
        return $ts;
    }

    /**
     * Mapper for push payloads
     * @param array $message
     * @return array
     */
    private function mapPayload($message)
    {
        return array(
            'aps' => array(
                'alert' => array(
                    'body' => $message['message'] ?: ''
                ),
                'badge' => $message['badge'] ?: 0
            ),
            'type' => $message['type'] ?: 0
        );
    }

    /**
     * Prepare GCM headers to send push notification
     * @param array $message
     * @param array $config
     * @return array
     */
    protected function prepareGCMHeaders(array &$message, array $config)
    {
        $fields = array(
            'registration_ids' => array(
                $message['device']
            ),
            "collapse_key" => $message['message'],
            'data' => array_merge($this->mapPayload($message), array("message" => $message['message']))
        );


        $headers = 'Authorization: key=' . $config['token'] . "\r\n";
        $headers .= 'Content-Type: application/json' . "\r\n";
        $headers .= 'Connection: close' . "\r\n";

        // use key 'http' even if you send the request to https://...
        return array(
            'http' => array(
                'header' => $headers,
                'method' => 'POST',
                'content' => json_encode($fields),
            ),
        );
    }

    /**
     * Process single messages
     * @param string $type
     * @param array $message
     * @return bool
     * @throws \Exception
     */
    protected function processMessage($type, array &$message)
    {
        $sended = false;
        $conn = null;
        switch (strtoupper($type)) {
            case 'APNS':
                $conn = $this->prepareConection($type);
                $socket = $this->prepareAPNSSocket($conn, $this->security[$type]);
                throw new \Exception('test');
                break;
            case 'GCM':
                $options = $this->prepareGCMHeaders($message, $this->security[$type]);
                $conn = $this->prepareConection($type, $options);
                $message['sended'] = $this->sendGCMMessage($type, $conn);
                break;
            case 'MPNS':
            default:
                throw new \Exception($type . ' messages not exists or not implemented yet');
        }
        $this->closeConnectionOrSocket($conn);
        return $sended;
    }

    /**
     * @param string $type
     * @param array $messages
     * @return int
     */
    protected function processQueue($type, array $messages)
    {
        $count = 0;
        if (count($messages) > 0) {
            foreach ($messages as &$message) {
                $this->addTrace('Processing message for ' . $type . ' with ts ', $message['ts']);
                try {
                    if ($this->processMessage($type, $message)) {
                        $count++;
                    }
                } catch(\Exception $e) {
                    $this->addError($e->getMessage());
                }
            }
        }
        return $count;
    }

    /**
     * Process all messages
     * @throws \Exception
     */
    private function processMessages()
    {
        if (count($this->messages) > 0) {
            $count = 0;
            foreach ($this->messages as $key => $messages) {
                if (!array_key_exists($key, $this->security)) {
                    throw new \Exception('Notifier not properly configured for ' . $key . ' notifications type');
                }
                $this->addTrace('Processing ' . $key . ' messages queue');
                $count += $this->processQueue($key, $messages);
            }
            $this->addTrace($count . ' messages sended');
            if($count == 0) {
                throw new \Exception('No push messages sended!!');
            }
        } else {
            $this->addError('No messages to send');
        }
    }

    /**
     * Execute push messages notifications
     * @return bool
     */
    public function notify()
    {
        $errors = false;
        try {
            $this->processMessages();
        } catch (\Exception $e) {
            $this->addError($e->getMessage());
            $errors = true;
        }
        return !$errors;
    }
}