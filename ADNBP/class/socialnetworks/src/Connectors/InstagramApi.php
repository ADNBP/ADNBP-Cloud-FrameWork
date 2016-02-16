<?php
namespace CloudFramework\Service\SocialNetworks\Connectors;

use CloudFramework\Patterns\Singleton;
use CloudFramework\Service\SocialNetworks\Exceptions\AuthenticationException;
use CloudFramework\Service\SocialNetworks\Exceptions\ConnectorConfigException;
use CloudFramework\Service\SocialNetworks\Exceptions\ConnectorServiceException;
use CloudFramework\Service\SocialNetworks\Exceptions\MalformedUrlException;
use CloudFramework\Service\SocialNetworks\Interfaces\SocialNetworkInterface;

/**
 * Class InstagramApi
 * @package CloudFramework\Service\SocialNetworks\Connectors
 * @author Salvador Castro <sc@bloombees.com>
 */
class InstagramApi extends Singleton implements SocialNetworkInterface {

    const ID = 'instagram';
    const INSTAGRAM_OAUTH_URL = "https://api.instagram.com/oauth/authorize/";
    const INSTAGRAM_OAUTH_ACCESS_TOKEN_URL = "https://api.instagram.com/oauth/access_token";
    const INSTAGRAM_API_USERS_URL = "https://api.instagram.com/v1/users/";
    const INSTAGRAM_API_MEDIA_URL = "https://api.instagram.com/v1/media/";

    // API keys
    private $clientId;
    private $clientSecret;
    private $clientScope = array();

    // Auth keys
    private $accessToken;

    /**
     * Set Instagram Api keys
     * @param $clientId
     * @param $clientSecret
     * @param $clientScope
     * @throws ConnectorConfigException
     */
    public function setApiKeys($clientId, $clientSecret, $clientScope) {
        if ((null === $clientId) || ("" === $clientId)) {
            throw new ConnectorConfigException("'clientId' parameter is required", 601);
        }

        if ((null === $clientSecret) || ("" === $clientSecret)) {
            throw new ConnectorConfigException("'clientSecret' parameter is required", 602);
        }

        if ((null === $clientScope) || (!is_array($clientScope)) || (count($clientScope) == 0)) {
            throw new ConnectorConfigException("'clientScope' parameter is required", 603);
        }

        $this->clientId = $clientId;
        $this->clientSecret = $clientSecret;
        $this->clientScope = $clientScope;
    }

    /**
     * Compose Instagram Api credentials array from session data
     * @param string $redirectUrl
     * @throws ConnectorConfigException
     * @throws MalformedUrlException
     * @return array
     */
    public function requestAuthorization($redirectUrl)
    {
        if ((null === $redirectUrl) || (empty($redirectUrl))) {
            throw new ConnectorConfigException("'redirectUrl' parameter is required", 624);
        } else {
            if (!$this->wellFormedUrl($redirectUrl)) {
                throw new MalformedUrlException("'redirectUrl' is malformed", 600);
            }
        }

        $scopes = implode("+", $this->clientScope);
        $authUrl = self::INSTAGRAM_OAUTH_URL.
            "?client_id=".$this->clientId.
            "&redirect_uri=".$redirectUrl.
            "&response_type=code".
            "&scope=".$scopes;
        if ((null === $authUrl) || (empty($authUrl))) {
            throw new ConnectorConfigException("'authUrl' parameter is required", 624);
        } else {
            if (!$this->wellFormedUrl($authUrl)) {
                throw new MalformedUrlException("'authUrl' is malformed", 600);
            }
        }
        // Authentication request
        return $authUrl;
    }

    /**
     * @param string $code
     * @param string $redirectUrl
     * @return array
     * @throws AuthenticationException
     * @throws ConnectorConfigException
     * @throws ConnectorServiceException
     * @throws MalformedUrlException
     */
    public function authorize($code, $redirectUrl)
    {
        if ((null === $code) || ("" === $code)) {
            throw new ConnectorConfigException("'code' parameter is required", 627);
        }

        if ((null === $redirectUrl) || (empty($redirectUrl))) {
            throw new ConnectorConfigException("'redirectUrl' parameter is required", 628);
        } else {
            if (!$this->wellFormedUrl($redirectUrl)) {
                throw new MalformedUrlException("'redirectUrl' is malformed", 601);
            }
        }

        $fields = "client_id=".$this->clientId.
            "&client_secret=".$this->clientSecret.
            "&grant_type=authorization_code".
            "&code=".$code.
            "&redirect_uri=".$redirectUrl;

        $instagramCredentials = $this->curlPost(self::INSTAGRAM_OAUTH_ACCESS_TOKEN_URL, $fields);

        /**
         * Returned data format instance
         *  {
        "access_token": "fb2e77d.47a0479900504cb3ab4a1f626d174d2d",
        "user": {
        "id": "1574083",
        "username": "snoopdogg",
        "full_name": "Snoop Dogg",
        "profile_picture": "...",
        "bio": "...",
        "website": "..."
        }
        }
         **/

        if (!isset($instagramCredentials["access_token"])) {
            throw new AuthenticationException("Error fetching OAuth2 access token, client is invalid", 601);
        } else if ((!isset($instagramCredentials["user"])) || (!isset($instagramCredentials["user"]["id"])) ||
            (!isset($instagramCredentials["user"]["full_name"])) ||
            (!isset($instagramCredentials["user"]["profile_picture"]))) {
            throw new ConnectorServiceException("Error fetching user profile info", 601);
        }

        // Instagram doesn't return the user's e-mail
        return $instagramCredentials;
    }

    /**
     * Method that inject the access token in connector
     * @param array $credentials
     */
    public function setAccessToken(array $credentials) {
        $this->accessToken = $credentials["access_token"];
    }

    /**
     * @param $credentials
     * @return null
     */
    public function checkCredentials($credentials) {
        return;
    }

    /**
     * Service that query to Instagram Api for users the user is followed by
     * @param string $userId
     * @param integer $maxResultsPerPage.
     * @param integer $numberOfPages
     * @param string $nextPageUrl
     * @return JSON
     * @throws ConnectorServiceException
     */
    function getFollowers($userId, $maxResultsPerPage, $numberOfPages, $nextPageUrl) {
        $this->checkUser($userId);

        $this->checkPagination($numberOfPages);

        if (!$nextPageUrl) {
            $nextPageUrl = self::INSTAGRAM_API_USERS_URL . $userId .
                "/followed-by?access_token=" . $this->accessToken;
        }

        $pagination = true;
        $followers = array();
        $count = 0;

        while ($pagination) {
            $data = $this->curlGet($nextPageUrl);

            if (null === $data["data"]) {
                throw new ConnectorServiceException("Error getting followers", 601);
            }

            $followers[$count] = array();

            foreach ($data["data"] as $key => $follower) {
                $followers[$count][] = $follower;
            }

            // If number of pages is zero, then all elements are returned
            if ((($numberOfPages > 0) && ($count == $numberOfPages)) || (!isset($data->pagination->next_url))) {
                $pagination = false;
                if (!isset($data->pagination->next_url)) {
                    $nextPageUrl = null;
                }
            } else {
                $nextPageUrl = $data->pagination->next_url;
                $count++;
            }
        }

        $followers["nextPageUrl"] = $nextPageUrl;

        return json_encode($followers);
    }

    function getFollowersInfo($userId, $postId) {
        return;
    }

    /**
     * Service that query to Instagram Api for users the user is following
     * @param string $userId
     * @param integer $maxResultsPerPage.
     * @param integer $numberOfPages
     * @param string $nextPageUrl
     * @return JSON
     * @throws ConnectorServiceException
     */
    function getSubscribers($userId, $maxResultsPerPage, $numberOfPages, $nextPageUrl) {
        $this->checkUser($userId);
        $this->checkPagination($numberOfPages);

        if (!$nextPageUrl) {
            $nextPageUrl = self::INSTAGRAM_API_USERS_URL . $userId .
                "/follows?access_token=" . $this->accessToken;
        }

        $pagination = true;
        $subscribers = array();
        $count = 0;

        while ($pagination) {
            $data = $this->curlGet($nextPageUrl);

            if (null === $data["data"]) {
                throw new ConnectorServiceException("Error getting subscribers", 602);
            }

            $subscribers[$count] = array();

            foreach ($data["data"] as $key => $subscriber) {
                $subscribers[$count][] = $subscriber;
            }

            // If number of pages is zero, then all elements are returned
            if ((($numberOfPages > 0) && ($count == $numberOfPages)) || (!isset($data->pagination->next_url))) {
                $pagination = false;
                if (!isset($data->pagination->next_url)) {
                    $nextPageUrl = null;
                }
            } else {
                $nextPageUrl = $data->pagination->next_url;
                $count++;
            }
        }

        $subscribers["nextPageUrl"] = $nextPageUrl;

        return json_encode($subscribers);
    }

    function getPosts($userId, $maxResultsPerPage, $numberOfPages, $pageToken) {
        return;
    }

    /**
     * Service that query to Instagram Api to get user profile
     * @param string $userId
     * @return JSON
     */
    public function getProfile($userId)
    {
        $this->checkUser($userId);

        $url = self::INSTAGRAM_API_USERS_URL . $userId . "/?access_token=" . $this->accessToken;

        $data = $this->curlGet($url);

        // Instagram API doesn't return the user's e-mail
        return json_encode($data["data"]);
    }

    /**
     * Service that query to Instagram Api Drive service for images
     * @param string $userId
     * @param integer $maxTotalResults.
     * @param integer $numberOfPages
     * @param string $nextPageUrl
     * @return JSON
     * @throws ConnectorServiceException
     */
    public function exportImages($userId, $maxTotalResults, $numberOfPages, $nextPageUrl)
    {
        $this->checkUser($userId);
        $this->checkPagination($numberOfPages, $maxTotalResults);

        if (!$nextPageUrl) {
            $nextPageUrl = self::INSTAGRAM_API_USERS_URL . $userId .
                        "/media/recent/?access_token=" . $this->accessToken;
                        //"&count=".$maxTotalResults;
        }

        $pagination = true;
        $files = array();
        $count = 0;

        while ($pagination) {
            $data = $this->curlGet($nextPageUrl);

            if (null === $data["data"]) {
                throw new ConnectorServiceException("Error exporting files", 603);
            }

            $files[$count] = array();

            foreach ($data["data"] as $key => $media) {
                if ("image" === $media["type"]) {
                    $files[$count][] = $media;
                }
            }

            // If number of pages is zero, then all elements are returned
            if ((($numberOfPages > 0) && ($count == $numberOfPages)) || (!isset($data->pagination->next_url))) {
                $pagination = false;
                if (!isset($data->pagination->next_url)) {
                    $nextPageUrl = null;
                }
            } else {
                $nextPageUrl = $data->pagination->next_url;
                $count++;
            }
        }

        $files["nextPageUrl"] = $nextPageUrl;

        return json_encode($files);
    }

    public function importMedia($userId, $path) {
        return;
    }

    /**
     * Service that publish a comment in an Instagram media
     * @param array $parameters
     *      "content" => Text of the comment
     *      "mediaId" => Instagram media's ID
     * @return JSON
     * @throws ConnectorConfigException
     * @throws ConnectorServiceException
     */
    public function post(array $parameters) {
        if ((null === $parameters) || (!is_array($parameters)) || (count($parameters) == 0)) {
            throw new ConnectorConfigException("Invalid post parameters'", 617);
        }

        if ((!array_key_exists('content', $parameters)) ||
            (null === $parameters["content"]) || (empty($parameters["content"]))) {
            throw new ConnectorConfigException("'content' parameter is required", 631);
        }

        if ((!array_key_exists('mediaId', $parameters)) ||
            (null === $parameters["mediaId"]) || (empty($parameters["mediaId"]))) {
            throw new ConnectorConfigException("'mediaId' parameter is required", 630);
        }

        $url = self::INSTAGRAM_API_MEDIA_URL.$parameters["mediaId"]."/comments";

        $fields = "access_token=".$this->accessToken.
                    "&text=".$parameters["content"];

        $data = $this->curlPost($url, $fields);

        if ($data["meta"]["code"] != 200) {
            throw new ConnectorServiceException("Error making comments on an Instagram media", $data["meta"]["code"]);
        }

        return json_encode($data);
    }

    function revokeToken() {
        return;
    }

    /**
     * Method that check userId is ok
     * @param $userId
     * @throws ConnectorConfigException
     */
    private function checkUser($userId) {
        if ((null === $userId) || ("" === $userId)) {
            throw new ConnectorConfigException("'userId' parameter is required", 607);
        }
    }

    /**
     * Method that check pagination parameters are ok
     * @param integer $maxTotalResults
     * @param integer $numberOfPages
     * @throws ConnectorConfigException
     */
    private function checkPagination($numberOfPages, $maxTotalResults = 0) {
        if (null === $maxTotalResults) {
            throw new ConnectorConfigException("'maxTotalResults' parameter is required", 608);
        } else if (!is_numeric($maxTotalResults)) {
            throw new ConnectorConfigException("'maxTotalResults' parameter is not numeric", 609);
        }

        if (null === $numberOfPages) {
            throw new ConnectorConfigException("'numberOfPages' parameter is required", 610);
        } else if (!is_numeric($numberOfPages)) {
            throw new ConnectorConfigException("'numberOfPages' parameter is not numeric", 611);
        }
    }

    /**
     * Method that calls url with GET method
     * @param $url
     * @return array
     * @throws \Exception
     */
    private function curlGet($url) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $data = curl_exec($ch);
        curl_close($ch);

        if (!$data) {
            throw \Exception("Error calling curl: ".curl_error($ch), curl_errno($ch));
        }
        return json_decode($data, true);
    }

    /**
     * Method that calls url with POST method
     * @param $url
     * @param $fields
     * @return array
     * @throws \Exception
     */
    private function curlPost($url, $fields) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $data = curl_exec($ch);
        curl_close($ch);

        if (!$data) {
            throw \Exception("Error calling curl: ".curl_error($ch), curl_errno($ch));
        }
        return json_decode($data, true);
    }

    /**
     * Private function to check url format
     * @param $redirectUrl
     * @return bool
     */
    private function wellFormedUrl($redirectUrl) {
        if (!filter_var($redirectUrl, FILTER_VALIDATE_URL) === false) {
            return true;
        } else {
            return false;
        }
    }
}