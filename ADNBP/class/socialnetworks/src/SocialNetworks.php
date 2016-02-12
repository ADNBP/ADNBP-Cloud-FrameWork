<?php
namespace CloudFramework\Service\SocialNetworks;

use CloudFramework\Patterns\Singleton;

/**
 * Class SocialNetworks
 * @author Fran López <fl@bloombees.com>
 */
class SocialNetworks extends Singleton
{
    /**
     * @return string
     */
    public static function generateRequestUrl()
    {
        $protocol = (array_key_exists("HTTPS", $_SERVER) && $_SERVER["HTTPS"] === 'on') ? 'https' : 'http';
        $domain = $_SERVER['SERVER_NAME'];
        $port = $_SERVER['SERVER_PORT'];
        return "$protocol://$domain:$port/";
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
            }
        } else {
            throw new \Exception("Social Network Requested not exists", 501);
        }
    }

    /**
     * Service that set the api keys for social network
     * @param $social
     * @param $clientId
     * @param $clientSecret
     * @param array $clientScope
     * @return mixed
     * @throws \Exception
     */
    public function setApiKeys($social, $clientId, $clientSecret, $clientScope = array()) {
        $connector = $this->getSocialApi($social);
        return $connector->setApiKeys($clientId, $clientSecret, $clientScope);
    }

    /**
     * Service to request authorization to the social network
     * @param string $social
     * @param string $redirectUrl
     * @return mixed
     * @throws \Exception
     */
    public function requestAuthorization($social, $redirectUrl)
    {
        $connector = $this->getSocialApi($social);
        return $connector->requestAuthorization($redirectUrl);
    }

    /**
     * Service that authorize a user in the social network.
     * (This method receives the callback from the social network after login)
     * @param string $social
     * @param string $code
     * @param string $redirectUrl
     * @return mixed
     * @throws \Exception
     */
    public function confirmAuthorization($social, $code, $redirectUrl)
    {
        $connector = $this->getSocialApi($social);
        $credentials = $connector->authorize($code, $redirectUrl);

        return $credentials;
    }

    /**
     * Service that save the user's credentials in session
     * @param $social
     * @param array $credentials
     * @return string
     * @throws \Exception
     */
    public function setCredentials($social, $credentials) {
        $connector = $this->getSocialApi($social);
        $profileId = $connector->getProfileId($credentials);
        $_SESSION[$social . "_credentials_" . $profileId] = $credentials;
        return json_encode(array("user_id" => $profileId));
    }

    /**
     * Service that check if session user's credentials are authorized in two steps:
     *      1 .- Check if user's credentials are in session
     *      2 .- If user's credentials are in session, check if access_token in session is authorized
     * @param $social
     * @throws \Exception
     */
    public function checkCredentials($social, $userId) {
        if (isset($_SESSION[$social . "_credentials_" . $userId])) {
            $credentials = $_SESSION[$social . "_credentials_" . $userId];
            $connector = $this->getSocialApi($social);
            if ($expiresIn = $connector->checkCredentials($userId, $credentials)) {
                $credentials["expires_in"] = $expiresIn;
                return $credentials;
            }
        } else {
            throw new \Exception("User '".$userId."' is not authorized in social network " . $social);
        }
    }

    /**
     * Service that refresh credentials of the user and refresh the session
     * @param $social
     * @return mixed
     * @throws \Exception
     */
    public function refreshCredentials($social, $userId) {
        $credentials = $_SESSION[$social . "_credentials_" . $userId];
        $connector = $this->getSocialApi($social);
        $newCredentials = $connector->refreshCredentials($credentials);
        $_SESSION[$social . "_credentials_" . $userId]["access_token"] = $newCredentials["access_token"];
        $_SESSION[$social . "_credentials_" . $userId]["id_token"] = $newCredentials["id_token"];

        return $_SESSION[$social . "_credentials_" . $userId];
    }


    /**
     * Service that query to a social network api to get user profile
     * @param string $social
     * @param string $userId
     * @return mixed
     * @throws \Exception
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
     * @return mixed
     * @throws \Exception
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
     * @return mixed
     * @throws \Exception
     */
    public function getFollowersInfo($social, $userId, $postId)
    {
        $connector = $this->getSocialApi($social);
        return $connector->getFollowersInfo($userId, $postId, $_SESSION[$social . "_credentials_" . $userId]);
    }

    /**
     * Service that query to a social network api to get subscribers
     * @param string $social
     * @param string $userId
     * @param integer $maxResultsPerPage maximum elements per page
     * @param integer $numberOfPages number of pages
     * @param string $nextPageUrl Indicates a specific page for pagination
     * @return mixed
     * @throws \Exception
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
     * @return mixed
     * @throws \Exception
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
     * @throws \Exception
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
     * @throws \Exception
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
     * @return mixed
     * @throws \Exception
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
     * @return mixed
     * @throws \Exception
     */
    public function revokeToken($social, $userId)
    {
        $connector = $this->getSocialApi($social);
        return $connector->revokeToken($_SESSION[$social."_credentials_".$userId]);
    }
}
