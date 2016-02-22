<?php
namespace CloudFramework\Service\SocialNetworks\Connectors;

use CloudFramework\Patterns\Singleton;
use CloudFramework\Service\SocialNetworks\Exceptions\ConnectorConfigException;
use CloudFramework\Service\SocialNetworks\Exceptions\ConnectorServiceException;
use CloudFramework\Service\SocialNetworks\Exceptions\MalformedUrlException;
use CloudFramework\Service\SocialNetworks\Interfaces\SocialNetworkInterface;
use Facebook\Facebook;

class FacebookApi extends Singleton implements SocialNetworkInterface
{
    const ID = 'facebook';
    const FACEBOOK_SELF_USER = "me";

    // Google client object
    private $client;

    // API keys
    private $clientId;
    private $clientSecret;
    private $clientScope = array();

    // Auth keys
    private $accessToken;

    /**
     * Set Facebook Api keys
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

        $this->client = new Facebook(array(
            "app_id" => $this->clientId,
            "app_secret" => $this->clientSecret,
            'default_graph_version' => 'v2.4',
            'cookie' => true
        ));
    }

    /**
     * Service that request authorization to Facebook making up the Facebook login URL
     * @param string $redirectUrl
     * @return array
     * @throws ConnectorConfigException
     * @throws MalformedUrlException
     */
    public function requestAuthorization($redirectUrl)
    {
        if ((null === $redirectUrl) || (empty($redirectUrl))) {
            throw new ConnectorConfigException("'redirectUrl' parameter is required", 628);
        } else {
            if (!$this->wellFormedUrl($redirectUrl)) {
                throw new MalformedUrlException("'redirectUrl' is malformed", 601);
            }
        }

        $redirect = $this->client->getRedirectLoginHelper();

        $authUrl = $redirect->getLoginUrl($redirectUrl, $this->clientScope);

        // Authentication request
        return $authUrl;
    }

    /**
     * Authentication service from Facebook sign in request
     * @param null $code
     * @param $redirectUrl
     * @return array
     * @throws ConnectorServiceException
     */
    public function authorize($code = null, $redirectUrl)
    {
        try {
            $helper = $this->client->getRedirectLoginHelper();
            $accessToken = $helper->getAccessToken();

            if (empty($accessToken)) {
                throw new ConnectorServiceException("Error taking access token from Facebook Api", 500);
            }
        } catch(\Exception $e) {
            throw new ConnectorServiceException($e->getMessage(), $e->getCode());
        }

        return array("access_token" => $accessToken->getValue());
    }

    /**
     * Method that inject the access token in connector
     * @param array $credentials
     */
    public function setAccessToken(array $credentials) {
        $this->accessToken = $credentials["access_token"];
    }

    /**
     * Service that check if credentials are valid
     * @param $credentials
     * @return null
     * @throws ConnectorConfigException
     */
    public function checkCredentials($credentials) {
        $this->checkCredentialsParameters($credentials);

        try {
            return $this->getProfile(self::FACEBOOK_SELF_USER);
        } catch(\Exception $e) {
            throw new ConnectorConfigException("Invalid credentials set'");
        }
    }

    public function revokeToken() {
        return;
    }

    /**
     * Service that query to Facebook Api a followers count
     * @return int
     */
    public function getFollowers($userId = null, $maxResultsPerPage, $numberOfPages, $pageToken)
    {
        $response = $this->client->get('/me/friends', $this->accessToken)->getDecodedBody();
        return $response["summary"]["total_count"];
    }

    public function getFollowersInfo($userId, $postId) {
        return;
    }

    public function getSubscribers($userId, $maxResultsPerPage, $numberOfPages, $nextPageUrl) {
        return;
    }

    public function getPosts($userId, $maxResultsPerPage, $numberOfPages, $pageToken) {
        return;
    }

    /**
     * Service that query to Facebook Api to get user profile
     * @param $userId
     * @return string
     * @throws ConnectorServiceException
     */
    public function getProfile($userId) {
        try {
            $response = $this->client->get("/".$userId."?fields=id,name,first_name,middle_name,last_name,email", $this->accessToken);
        } catch(\Exception $e) {
            throw new ConnectorServiceException('Error getting user profile: ' . $e->getMessage(), $e->getCode());
        }

        $profile = array(
            "user_id" => $response->getGraphUser()->getId(),
            "name" => $response->getGraphUser()->getName(),
            "first_name" => $response->getGraphUser()->getFirstName(),
            "middle_name" => $response->getGraphUser()->getMiddleName(),
            "last_name" => $response->getGraphUser()->getLastName(),
            "email" => $response->getGraphUser()->getEmail()
        );

        return json_encode($profile);
    }

    public function importMedia($userId, $mediaType, $value) {
        return;
    }

    public function exportMedia($userId, $maxResultsPerPage, $numberOfPages, $pageToken) {
        return;
    }


    /**
     * Service that create a post in Facebook user's feed
     * @param array $parameters
     * @return array
     * @throws ConnectorServiceException
     */
    public function post(array $parameters) {
        try {
            $response = $this->client->post("/me/feed", $parameters, $this->accessToken);
        } catch(\Exception $e) {
            throw new ConnectorServiceException('Error creating a post: ' . $e->getMessage(), $e->getCode());
        }

        $graphNode = $response->getGraphNode();

        $post = array("post_id" => $graphNode["id"]);

        return json_encode($post);
    }

    public function getUserRelationship($authenticatedUserId, $userId) {
        return;
    }

    public function modifyUserRelationship($authenticatedUserId, $userId, $action) {
        return;
    }

    public function searchUsers($userId, $name, $maxTotalResults, $numberOfPages, $nextPageUrl) {
        return;
    }

    /**
     * Method that check credentials are present and valid
     * @param array $credentials
     * @throws ConnectorConfigException
     */
    private function checkCredentialsParameters(array $credentials) {
        if ((null === $credentials) || (!is_array($credentials)) || (count($credentials) == 0)) {
            throw new ConnectorConfigException("Invalid credentials set'");
        }

        if ((!isset($credentials["access_token"])) || (null === $credentials["access_token"]) || ("" === $credentials["access_token"])) {
            throw new ConnectorConfigException("'access_token' parameter is required");
        }
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