<?php
namespace CloudFramework\Service\SocialNetworks;

require_once __DIR__ . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . "vendor" . DIRECTORY_SEPARATOR . "autoload.php";
use CloudFramework\Service\SocialNetworks\Interfaces\Singleton;

/**
 * Class SocialNetworks
 * @author Fran López <fl@bloombees.com>
 */
class SocialNetworks extends Singleton
{
    public static function generateRequestUrl()
    {
        $protocol = (array_key_exists("HTTPS", $_SERVER) && $_SERVER["HTTPS"] === 'on') ? 'https' : 'http';
        $domain = $_SERVER['SERVER_NAME'];
        $port = $_SERVER['SERVER_PORT'];
        return "$protocol://$domain:$port/";
    }

    /**
     * Static method that generate an error response from SocialNetwork Service
     * @param string $message
     * @param int $code
     */
    public static function generateErrorResponse($message, $code = 500)
    {
        ob_start();
        header("HTTP/1.0 $code $message");
        ob_end_clean();
        exit;
    }

    /**
     * Service that make a JSON response
     * @param mixed $result
     */
    public static function jsonResponse($result = null)
    {
        $data = json_encode($result, JSON_PRETTY_PRINT);
        ob_start();
        header("Content-type: application/json");
        header("Content-length: " . strlen($data));
        echo $data;
        ob_flush();
        ob_end_clean();
        exit;
    }

    /**
     * Statis method that hydrate credentials for social network required fields
     * @param string $socialNetwork
     * @param array $keys
     * @param array $data
     * @return array
     */
    public static function hydrateCredentials($socialNetwork, $keys, $data)
    {
        $credentials = array();
        if (null !== $data) {
            foreach ($keys as $key) {
                if (array_key_exists($key, $data) && strlen($data[$key]) > 0) {
                    $credentials[$key] = $data[$key];
                }
            }
        }
        if (count($credentials) !== count($keys)) {
            switch (strtoupper($socialNetwork)) {
                case "GOOGLE":
                    SocialNetworks::generateErrorResponse(SocialNetworks::getAuthGoogleApiUrl(), 401);
                    break;
                case "TWITTER":
                    SocialNetworks::generateErrorResponse(SocialNetworks::getAuthTwitterApiUrl(), 401);
                    break;
                case "FACEBOOK":
                case "LINKEDIN":
                case "INSTAGRAM":
                case "PINTEREST":
                default:
                    SocialNetworks::generateErrorResponse(SocialNetworks::generateRequestUrl() . "socialnetworks", 302);
                    break;
            }

        }
        return $credentials;
    }

    /**
     * Method that initialize a social api instance to use
     * @param $social
     * @return \CloudFramework\Services\SocialNetworks\Interfaces\SocialNetworksInterface
     */
    protected function getSocialApi($social) {
        $socialNetworkClass = "CloudFramework\\Service\\SocialNetworks\\Connectors\\{$social}Api";
        if (class_exists($socialNetworkClass)) {
            try {
                return $connector = $socialNetworkClass::getInstance();
            } catch(\Exception $e) {
                SocialNetworks::generateErrorResponse($e->getMessage(), 500);
            }
        } else {
            SocialNetworks::generateErrorResponse("Social Network Requested not exists", 501);
        }
    }

    /**
     * Service to check authorized credentials for Social Network
     * @param string $social
     * @param array $credentials
     * @return array|string
     */
    public function auth($social, array $credentials = array())
    {
        try {
            $connector = $this->getSocialApi($social);
            return $connector->getAuth($credentials);
        } catch(\Exception $e) {
            SocialNetworks::generateErrorResponse($e->getMessage(), 500);
        }
    }

    /**
     * @param string $social
     * @param $params
     * @return mixed
     */
    public function saveInSession($social, $params)
    {
        try {
            $connector = $this->getSocialApi($social);
            $credentials = $connector->authorize($params);
            $_SESSION[strtolower($social) . "_form_credentials"] = $credentials;
            header("Location: " . SocialNetworks::generateRequestUrl() . "socialnetworks");
            exit;
        } catch(\Exception $e) {
            SocialNetworks::generateErrorResponse($e->getMessage(), 500);
        }
    }

    /**
     * Service that connect to social network api and request a followers count for authenticated user
     * @param string $social
     * @param array $credentials
     * @return mixed
     */
    public function getFollowers($social, array $credentials = array())
    {
        try {
            $connector = $this->getSocialApi($social);
            return $connector->getFollowers($credentials);
        } catch(\Exception $e) {
            SocialNetworks::generateErrorResponse($e->getMessage(), 500);
        }
    }

}
