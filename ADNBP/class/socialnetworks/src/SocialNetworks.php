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
     * Method that initialize a social api instance to use
     * @param $social
     * @return \CloudFramework\Services\SocialNetworks\Interfaces\SocialNetworksInterface
     * @throws \Exception
     */
    public function getSocialApi($social) {
        $social = ucfirst($social);
        $socialNetworkClass = "CloudFramework\\Service\\SocialNetworks\\Connectors\\{$social}Api";
        if (class_exists($socialNetworkClass)) {
            try {
                return $connector = $socialNetworkClass::getInstance();
            } catch(\Exception $e) {
                throw $e;
                //SocialNetworks::generateErrorResponse($e->getMessage(), 500);
            }
        } else {
            throw new \Exception("Social Network Requested not exists", 501);
            //SocialNetworks::generateErrorResponse("Social Network Requested not exists", 501);
        }
    }

    /**
     * Method that set credentials for social network
     * @param $social
     * @param $clientId
     * @param $clientSecret
     * @param array $clientScope
     */
    public function setCredentials($social, $clientId, $clientSecret, $clientScope = array()) {
        $connector = $this->getSocialApi($social);
        return $connector->setCredentials($clientId, $clientSecret, $clientScope);
    }

    /**
     * Method that check if user with $userId is authorized
     * @param $social
     * @param $userId
     * @return bool
     */
    public function isAuth($social, $userId) {
        if (isset($_SESSION[$social . "_credentials_" . $userId])) {
            return true;
        }

        return false;
    }

    /**
     * Method that save auth keys of the user in session
     * @param $social
     * @param $authkeys
     * @return JSON
     */
    public function setAuth($social, $authKeys) {
        $connector = $this->getSocialApi($social);
        $profileId = $connector->getProfileId($authKeys);
        $_SESSION[$social . "_credentials_" . $profileId] = $authKeys;
        return json_encode(array("user_id" => $profileId));
    }

    /**
     * Method that get auth keys of the user from session
     * @param $social
     * @param $userId
     * @return JSON
     */
    public function getAuth($social, $userId) {
        return json_encode($_SESSION[$social . "_credentials_" . $userId]);
    }

    /**
     * Service that query to a social network api to get user profile
     * @param string $social
     * @param string $userId
     * @return JSON
     */
    public function getProfile($social, $userId)    {
        $connector = $this->getSocialApi($social);
        return $connector->getProfile($userId, $_SESSION[$social."_credentials_".$userId]);
    }

    /**
     * Service that query to a social network api to get followers
     * @param string $social
     * @param string $userId
     * @param integer $maxResultsPerPage maximum elements per page
     * @param integer $numberOfPages number of pages
     * @param string $pageToken Indicates a specific page for pagination
     * @return JSON
     */
    public function getFollowers($social, $userId, $maxResultsPerPage, $numberOfPages, $pageToken)
    {
        $connector = $this->getSocialApi($social);
        return $connector->getFollowers($userId, $maxResultsPerPage, $numberOfPages, $pageToken,
                                        $_SESSION[$social . "_credentials_" . $userId]);
    }

    /**
     * Service that query to a social network api to get followers info
     * @param string $social
     * @param string $userId
     * @param string $postId
     * @return JSON
     */
    public function getFollowersInfo($social, $userId, $postId)
    {
        $connector = $this->getSocialApi($social);
        return $connector->getFollowersInfo($postId, $_SESSION[$social . "_credentials_" . $userId]);
    }

    /**
     * Service that query to a social network api to get subscribers
     * @param string $social
     * @param string $userId
     * @param integer $maxResultsPerPage maximum elements per page
     * @param integer $numberOfPages number of pages
     * @param string $nextPageUrl Indicates a specific page for pagination
     * @return JSON
     */
    public function getSubscribers($social,  $userId, $maxResultsPerPage, $numberOfPages, $nextPageUrl)
    {
        $connector = $this->getSocialApi($social);
        return $connector->getSubscribers($userId, $maxResultsPerPage, $numberOfPages, $nextPageUrl,
            $_SESSION[$social . "_credentials_" . $userId]);
    }

    /**
     * Service that query to a social network api to get posts info
     * @param string $userId
     * @param integer $maxResultsPerPage maximum elements per page
     * @param integer $numberOfPages number of pages
     * @param string $pageToken Indicates a specific page for pagination
     * @param array $credentials
     * @return JSON
     */
    public function getPosts($social, $userId, $maxResultsPerPage, $numberOfPages, $pageToken)
    {
        $connector = $this->getSocialApi($social);
        return $connector->getPosts($userId, $maxResultsPerPage, $numberOfPages, $pageToken,
                                    $_SESSION[$social."_credentials_".$userId]);
    }

    /**
     * Service that connect to social network api and request for data for authenticated user
     * @param string $social
     * @param string $userId
     * @param integer $maxResultsPerPage maximum elements per page
     * @param integer $numberOfPages number of pages
     * @param string $pageToken Indicates a specific page for pagination
     * @return mixed
     */
    public function exportImages($social, $userId, $maxResultsPerPage, $numberOfPages, $pageToken)
    {
        $connector = $this->getSocialApi($social);
        return $connector->exportImages($userId, $maxResultsPerPage, $numberOfPages, $pageToken,
                                        $_SESSION[$social."_credentials_".$userId]);
    }

    /**
     * Service that connect to social network api and upload a media file (image/video)
     * @param string $social
     * @param string $path Path to media
     * @param string $userId
     * @return mixed
     */
    public function importMedia($social, $path, $userId)
    {
        $connector = $this->getSocialApi($social);
        return $connector->importMedia($userId, $path, $_SESSION[$social."_credentials_".$userId]);
    }

    /**
     * Service that connect to social network api and export data for authenticated user
     * @param string $social
     * @param array $parameters
     * GOOGLE
     *      "user_id"    => User whose google domain the stream will be published in
     *      "content"   => Text of the comment
     *      "access_type" => The type of entry describing to whom access to new post/activity is granted
     *              "person"          => Need a personId parameter
     *              "circle"          => Need a circleId parameter
     *              "myCircles"       => Access to members of all the person's circles
     *              "extendedCircles" => Access to members of all the person's circles, plus all of the people in their circles
     *              "domain"          => Access to members of the person's Google Apps domain
     *              "public"          => Access to anyone on the web
     *      "attachment":
     *          "0": "link" | "image" | "video"
     *          "1": url or path for a file
     *      "person_id"  => Google + user whose domain the stream will be published in (mandatory in case of access_type = "person")
     *      "circle_id"  => Google circle where the stream will be published in (mandatory in case of access_type = "circle")
     * INSTAGRAM
     *      "content"   => Text of the comment
     *      "mediaId"   => Instagram media's ID
     *
     * @return JSON
     */
    public function post($social, $parameters)
    {
        $connector = $this->getSocialApi($social);
        return $connector->post($parameters, $_SESSION[$social."_credentials_".$parameters["user_id"]]);
    }

    /**
     * Service that query to a social network api to revoke access token in order
     * to ensure the permissions granted to the application are removed
     * @param string $social
     * @param string $userId
     * @return JSON
     */
    public function revokeToken($social, $userId)
    {
        $connector = $this->getSocialApi($social);
        return $connector->revokeToken($_SESSION[$social."_credentials_".$userId]);
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
     * @param string $redirectUrl
     * @return mixed
     */
    public function getSocialLoginUrl($social, $redirectUrl) {
        $connector = $this->getSocialApi($social);
        return $connector->getAuthUrl($redirectUrl);
    }

    /**
     * Service to check authorized credentials for Social Network
     * @param string $social
     * @param string $redirectUrl
     * @return array|string
     */
    public function auth($social, $redirectUrl)
    {
        $connector = $this->getSocialApi($social);
        return $connector->getAuth($redirectUrl);
    }

    /**
     * @param string $social
     * @param string $code
     * @param string $redirectUrl
     * @param $params
     */
    public function getCredentials($social, $code, $redirectUrl)
    {
        $connector = $this->getSocialApi($social);
        $credentials = $connector->authorize($code, $redirectUrl);

        return $credentials;
    }
}
