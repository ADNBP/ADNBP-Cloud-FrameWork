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
     * @throws \Exception
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
                return SocialNetworks::getInstance()->getSocialLoginUrl($socialNetwork, $apiKeys, $redirectUrl);
            } else {
                //SocialNetworks::generateErrorResponse("API Keys aren't correct", 401);
                throw new \Exception("API Keys aren't correct", 401);
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
     * @throws \Exception
     */
    public function getSocialLoginUrl($social, array $apiKeys, $redirectUrl) {
        try {
            $connector = $this->getSocialApi($social);
            return $connector->getAuthUrl($apiKeys, $redirectUrl);
        } catch(\Exception $e) {
            throw $e;
            //SocialNetworks::generateErrorResponse($e->getMessage(), 500);
        }
    }

    /**
     * Method that initialize a social api instance to use
     * @param $social
     * @return \CloudFramework\Services\SocialNetworks\Interfaces\SocialNetworksInterface
     * @throws \Exception
     */
    public function getSocialApi($social) {
        $socialNetworkClass = "CloudFramework\\Service\\SocialNetworks\\Connectors\\{$social}Api";
        if (class_exists($socialNetworkClass)) {
            try {
                return $connector = $socialNetworkClass::getInstance();
            } catch(\Exception $e) {
                throw $e;
                //SocialNetworks::generateErrorResponse($e->getMessage(), 500);
            }
        } else {
            throw new \Exception(501, "Social Network Requested not exists");
            //SocialNetworks::generateErrorResponse("Social Network Requested not exists", 501);
        }
    }

    /**
     * Service to check authorized credentials for Social Network
     * @param string $social
     * @param array $credentials
     * @return array|string
     * @throws \Exception
     */
    public function auth($social, array $credentials = array(), $redirectUrl)
    {
        try {
            $connector = $this->getSocialApi($social);
            return $connector->getAuth($credentials, $redirectUrl);
        } catch(\Exception $e) {
            throw $e;
            //SocialNetworks::generateErrorResponse($e->getMessage(), 500);
        }
    }

    /**
     * @param string $social
     * @param $params
     * @throws \Exception
     */
    public function saveInSession($social, $params)
    {
        try {
            $connector = $this->getSocialApi($social);
            $credentials = $connector->authorize($params);
            $_SESSION[strtolower($social) . "_credentials"] = $credentials;
        } catch(\Exception $e) {
            throw $e;
            //SocialNetworks::generateErrorResponse($e->getMessage(), $e->getCode());
        }
    }

    /**
     * Service that query to a social network api to get followers
     * @param string $social
     * @param string $userId
     * @param array $credentials
     * @return JSON string
     * @throws \Exception
     */
    public function getFollowers($social, $userId, array $credentials = array())
    {
        try {
            $connector = $this->getSocialApi($social);
            return $connector->getFollowers($userId, $credentials);
        } catch(\Exception $e) {
            throw $e;
            //SocialNetworks::generateErrorResponse($e->getMessage(), 500);
        }
    }

    /**
     * Service that query to a social network api to get followers info
     * @param string $social
     * @param string $postId
     * @param array $credentials
     * @return JSON string
     * @throws \Exception
     */
    public function getFollowersInfo($social, $postId, array $credentials = array())
    {
        try {
            $connector = $this->getSocialApi($social);
            return $connector->getFollowersInfo($postId, $credentials);
        } catch(\Exception $e) {
            throw $e;
            //SocialNetworks::generateErrorResponse($e->getMessage(), 500);
        }
    }

    /**
     * Service that query to a social network api to get subscribers
     * @param string $social
     * @param string $userId
     * @param array $credentials
     * @return JSON string
     * @throws \Exception
     */
    public function getSubscribers($social, $userId, array $credentials = array())
    {
        try {
            $connector = $this->getSocialApi($social);
            return $connector->getSubscribers($userId, $credentials);
        } catch(\Exception $e) {
            throw $e;
            //SocialNetworks::generateErrorResponse($e->getMessage(), 500);
        }
    }

    /**
     * Service that query to a social network api to get posts info
     * @param string $social
     * @param string $userId
     * @param array $credentials
     * @return JSON string
     * @throws \Exception
     */
    public function getPosts($social, $userId, array $credentials = array())
    {
        try {
            $connector = $this->getSocialApi($social);
            return $connector->getPosts($userId, $credentials);
        } catch(\Exception $e) {
            throw $e;
            //SocialNetworks::generateErrorResponse($e->getMessage(), 500);
        }
    }

    /**
     * Service that query to a social network api to get user profile
     * @param string $social
     * @param string $userId
     * @param array $credentials
     * @return JSON string
     * @throws \Exception
     */
    public function getProfile($social, $userId, array $credentials = array())
    {
        try {
            $connector = $this->getSocialApi($social);
            return $connector->getProfile($userId, $credentials);
        } catch(\Exception $e) {
            throw $e;
            //SocialNetworks::generateErrorResponse($e->getMessage(), 500);
        }
    }

    /**
     * Service that connect to social network api and request for data for authenticated user
     * @param string $social
     * @param array $credentials
     * @param integer $maxResults maximum elements per page
     * @param string $userId
     * @return mixed
     * @throws \Exception
     */
    public function import($social, array $credentials = array(), $maxResults = 0, $userId = null)
    {
        try {
            $connector = $this->getSocialApi($social);
            return $connector->import($credentials, $maxResults, $userId);
        } catch(\Exception $e) {
            throw $e;
            //SocialNetworks::generateErrorResponse($e->getMessage(), 500);
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
     * @throws \Exception
     */
    public function export($social, array $credentials, $parameters)
    {
        try {
            $connector = $this->getSocialApi($social);
            return $connector->export($credentials, $parameters);
        } catch(\Exception $e) {
            throw $e;
            //SocialNetworks::generateErrorResponse($e->getMessage(), 500);
        }
    }

    /**
     * Service that query to a social network api to revoke access token in order
     * to ensure the permissions granted to the application are removed
     * @param string $social
     * @param array $credentials
     * @return JSON string
     * @throws \Exception
     */
    public function revokeToken($social, array $credentials)
    {
        try {
            $connector = $this->getSocialApi($social);
            return $connector->revokeToken($credentials);
        } catch(\Exception $e) {
            throw $e;
            //SocialNetworks::generateErrorResponse($e->getMessage(), 500);
        }
    }
}
