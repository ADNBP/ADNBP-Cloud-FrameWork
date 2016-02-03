<?php
namespace CloudFramework\Service\SocialNetworks;

use CloudFramework\Patterns\Singleton;

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
     * Static method that hydrate credentials for social network required fields
     * @param string $socialNetwork
     * @param array $authKeysNames
     * @param array $apiKeysNames
     * @param array $data
     * @param string $redirectUrl
     * @return array
     */
    public static function hydrateCredentials($socialNetwork, $authKeysNames, $apiKeysNames, $data, $redirectUrl)
    {
        $credentials = array();
        $apiKeys = array();
        if (null !== $data) {
            foreach ($authKeysNames as $authKeyName) {
                if (array_key_exists($authKeyName, $data) && strlen($data[$authKeyName]) > 0) {
                    $credentials[$authKeyName] = $data[$authKeyName];
                }
            }

            foreach($apiKeysNames as $apiKeyName) {
                if (array_key_exists($apiKeyName, $data) && strlen($data[$apiKeyName]) > 0) {
                    $apiKeys[$apiKeyName] = $data[$apiKeyName];
                }
            }
        }

        if (count($credentials) !== count($authKeysNames)) {
            if (count($apiKeys) === count($apiKeysNames)) {
                SocialNetworks::generateErrorResponse(
                    SocialNetworks::getInstance()->getSocialLoginUrl($socialNetwork, $apiKeys, $redirectUrl),
                    401
                );
            } else {
                SocialNetworks::generateErrorResponse("API Keys aren't correct", 401);
            }
        }

        return array("api_keys" => $apiKeys, "auth_keys" => $credentials);
    }

    /**
     * Method that generate the social network login url
     * @param $social
     * @param array $apiKeys
     * @param string $redirectUrl
     * @return mixed
     */
    public function getSocialLoginUrl($social, array $apiKeys, $redirectUrl) {
        try {
            $connector = $this->getSocialApi($social);
            return $connector->getAuthUrl($apiKeys, $redirectUrl);
        } catch(\Exception $e) {
            SocialNetworks::generateErrorResponse($e->getMessage(), 500);
        }
    }

    /**
     * Method that initialize a social api instance to use
     * @param $social
     * @return \CloudFramework\Services\SocialNetworks\Interfaces\SocialNetworksInterface
     */
    public function getSocialApi($social) {
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
    public function auth($social, array $credentials = array(), $redirectUrl)
    {
        try {
            $connector = $this->getSocialApi($social);
            return $connector->getAuth($credentials, $redirectUrl);
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
            SocialNetworks::generateErrorResponse($e->getMessage(), $e->getCode());
        }
    }

    /**
     * Service that query to a social network api to get followers
     * @param string $social
     * @param string $userId
     * @param array $credentials
     * @return JSON string
     */
    public function getFollowers($social, $userId, array $credentials = array())
    {
        try {
            $connector = $this->getSocialApi($social);
            return $connector->getFollowers($userId, $credentials);
        } catch(\Exception $e) {
            SocialNetworks::generateErrorResponse($e->getMessage(), 500);
        }
    }

    /**
     * Service that query to a social network api to get followers info
     * @param string $social
     * @param string $postId
     * @param array $credentials
     * @return JSON string
     */
    public function getFollowersInfo($social, $postId, array $credentials = array())
    {
        try {
            $connector = $this->getSocialApi($social);
            return $connector->getFollowersInfo($postId, $credentials);
        } catch(\Exception $e) {
            SocialNetworks::generateErrorResponse($e->getMessage(), 500);
        }
    }

    /**
     * Service that query to a social network api to get user profile
     * @param string $social
     * @param string $userId
     * @param array $credentials
     * @return JSON string
     */
    public function getProfile($social, $userId, array $credentials = array())
    {
        try {
            $connector = $this->getSocialApi($social);
            return $connector->getProfile($userId, $credentials);
        } catch(\Exception $e) {
            SocialNetworks::generateErrorResponse($e->getMessage(), 500);
        }
    }

    /**
     * Service that connect to social network api and request for data for authenticated user
     * @param string $social
     * @param array $credentials
     * @param string $path path where files imported will be saved
     * @param integer $maxResults maximum elements per page
     * @return mixed
     */
    public function import($social, array $credentials = array(), $path, $maxResults)
    {
        try {
            $connector = $this->getSocialApi($social);
            return $connector->import($credentials, $path, $maxResults);
        } catch(\Exception $e) {
            SocialNetworks::generateErrorResponse($e->getMessage(), 500);
        }
    }

    /**
     * Service that connect to social network api and export data for authenticated user
     * @param string $social
     * @param array $credentials
     * @param array $parameters
     * GOOGLE
     *      "userId"    => User whose google domain the stream will be published in
     *      "content"   => Text of the comment
     *      "link"      => External link
     *      "logo"      => Logo
     *      "circleId"  => Google circle where the stream will be published in
     *      "personId"  => Google + user whose domain the stream will be published in
     *      ($circleId are excluding)
     * INSTAGRAM
     *      "content"   => Text of the comment
     *      "mediaId"   => Instagram media's ID
     *
     * @return ExportDTO
     */
    public function export($social, array $credentials, $parameters)
    {
        try {
            $connector = $this->getSocialApi($social);
            return $connector->export($credentials, $parameters);
        } catch(\Exception $e) {
            SocialNetworks::generateErrorResponse($e->getMessage(), 500);
        }
    }
}
