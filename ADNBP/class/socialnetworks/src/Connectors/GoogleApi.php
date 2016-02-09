<?php
namespace CloudFramework\Service\SocialNetworks\Connectors;

use CloudFramework\Patterns\Singleton;
use CloudFramework\Service\SocialNetworks\Exceptions\AuthenticationException;
use CloudFramework\Service\SocialNetworks\Exceptions\ConnectorConfigException;
use CloudFramework\Service\SocialNetworks\Exceptions\ExportException;
use CloudFramework\Service\SocialNetworks\Exceptions\ImportException;
use CloudFramework\Service\SocialNetworks\Exceptions\MalformedUrlException;
use CloudFramework\Service\SocialNetworks\Exceptions\ProfileInfoException;
use CloudFramework\Service\SocialNetworks\Interfaces\SocialNetworkInterface;
use CloudFramework\Service\SocialNetworks\SocialNetworks;
use CloudFramework\Service\SocialNetworks\Dtos\ExportDTO;
use CloudFramework\Service\SocialNetworks\Dtos\ProfileDTO;

/**
 * Class GoogleApi
 * @package CloudFramework\Service\SocialNetworks\Connectors
 * @author Salvador Castro <sc@bloombees.com>
 */
class GoogleApi extends Singleton implements SocialNetworkInterface {

    const ID = 'google';

    // API keys
    private $clientId;
    private $clientSecret;
    private $clientScope = array();

    /**
     * Set Google Api credentials
     * @param $clientId
     * @param $clientSecret
     * @param $clientScope
     */
    public function setCredentials($clientId, $clientSecret, $clientScope) {
        $this->clientId = $clientId;
        $this->clientSecret = $clientSecret;
        $this->clientScope = $clientScope;
    }

    /**
     * Service that query to Google Api for people in user circles
     * @param string $userId
     * @param array $credentials
     * @return JSON string
     * @throws AuthenticationException
     * @throws ConnectorConfigException
     */
    public function getFollowers($userId, array $credentials)
    {
        $client = new \Google_Client();
        $client->setClassConfig("Google_Http_Request", "disable_gzip", true);
        $client->setClientId($this->clientId);
        $client->setClientSecret($this->clientSecret);
        $client->setAccessToken(json_encode($credentials));

        if ($client->isAccessTokenExpired()) {
            try {
                $client->setClientId($this->clientId);
                $client->setClientSecret($this->clientSecret);
                $client->refreshToken($credentials["refresh_token"]);
            } catch(\Exception $e) {
                throw new AuthenticationException("Error refreshing token: " . $e->getMessage(), 602);
            }
        }

        $plusDomainService = new \Google_Service_PlusDomains($client);
        $peopleList = $plusDomainService->people->listPeople($userId, "circled");

        return json_encode($peopleList->getItems());
    }

    /**
     * Service that query to Google Api for followers info (likes and shares) of a post
     * @param string $postId
     * @param array $credentials
     * @return JSON string
     * @throws AuthenticationException
     * @throws ConnectorConfigException
     */
    public function getFollowersInfo($postId, array $credentials)
    {
        $client = new \Google_Client();
        $client->setClassConfig("Google_Http_Request", "disable_gzip", true);
        $client->setClientId($this->clientId);
        $client->setClientSecret($this->clientSecret);
        $client->setAccessToken(json_encode($credentials));

        if ($client->isAccessTokenExpired()) {
            try {
                $client->setClientId($this->clientId);
                $client->setClientSecret($this->clientSecret);
                $client->refreshToken($credentials["refresh_token"]);
            } catch(\Exception $e) {
                throw new AuthenticationException("Error refreshing token: " . $e->getMessage(), 602);
            }
        }

        $people = array();
        $plusService = new \Google_Service_Plus($client);
        $plusoners = $plusService->people->listByActivity($postId, "plusoners")->getItems();
        $resharers = $plusService->people->listByActivity($postId, "resharers")->getItems();

        $people["plusoners"] = $plusoners;
        $people["resharers"] = $resharers;

        return json_encode($people);
    }

    public function getSubscribers($userId, array $credentials = array()) {
        return;
    }

    /**
     * Service that query to Google Api for posts/activities of a user
     * @param string $userId
     * @param array $credentials
     * @return JSON string
     * @throws AuthenticationException
     * @throws ConnectorConfigException
     */
    public function getPosts($userId, array $credentials)
    {
        $client = new \Google_Client();
        $client->setClassConfig("Google_Http_Request", "disable_gzip", true);
        $client->setClientId($this->clientId);
        $client->setClientSecret($this->clientSecret);
        $client->setAccessToken(json_encode($credentials));

        if ($client->isAccessTokenExpired()) {
            try {
                $client->setClientId($this->clientId);
                $client->setClientSecret($this->clientSecret);
                $client->refreshToken($credentials["refresh_token"]);
            } catch(\Exception $e) {
                throw new AuthenticationException("Error refreshing token: " . $e->getMessage(), 602);
            }
        }

        $plusService = new \Google_Service_Plus($client);
        $activities = $plusService->activities->listActivities($userId, "public");

        return json_encode($activities->getItems());
    }

    /**
     * Service that query to Google Oauth Api to get user profile
     * @param string $userId
     * @param array $credentials
     * @return JSON string
     * @throws AuthenticationException
     * @throws ConnectorConfigException
     * @throws ProfileInfoException
     */
    public function getProfile($userId, array $credentials)
    {
        $client = new \Google_Client();
        $client->setClassConfig("Google_Http_Request", "disable_gzip", true);
        $client->setClientId($this->clientId);
        $client->setClientSecret($this->clientSecret);
        $client->setAccessToken(json_encode($credentials));

        if ($client->isAccessTokenExpired()) {
            try {
                $client->setClientId($this->clientId);
                $client->setClientSecret($this->clientSecret);
                $client->refreshToken($credentials["refresh_token"]);
            } catch(\Exception $e) {
                throw new AuthenticationException("Error refreshing token: " . $e->getMessage(), 602);
            }
        }

        try {
            $plusService = new \Google_Service_Plus($client);
            $profile = $plusService->people->get($userId);
        } catch(\Exception $e) {
            throw new ProfileInfoException("Error fetching user profile info: " . $e->getMessage(), 601);
        }

        return json_encode($profile);
    }

    /**
     * Service that query to Google Oauth Api to get user profile
     * @param array $credentials
     * @return string
     * @throws AuthenticationException
     * @throws ProfileInfoException
     */
    public function getSelfProfile(array $credentials)
    {
        $client = new \Google_Client();
        $client->setClassConfig("Google_Http_Request", "disable_gzip", true);
        $client->setClientId($this->clientId);
        $client->setClientSecret($this->clientSecret);
        $client->setAccessToken(json_encode($credentials));

        if ($client->isAccessTokenExpired()) {
            try {
                $client->setClientId($this->clientId);
                $client->setClientSecret($this->clientSecret);
                $client->refreshToken($credentials["refresh_token"]);
            } catch(\Exception $e) {
                throw new AuthenticationException("Error refreshing token: " . $e->getMessage(), 602);
            }
        }

        try {
            $oauthService = new \Google_Service_Oauth2($client);
            $profile = $oauthService->userinfo_v2_me->get();
        } catch(\Exception $e) {
            throw new ProfileInfoException("Error fetching user profile info: " . $e->getMessage(), 601);
        }

        return $profile->getId();
    }

    /**
     * Service that query to Google Api Drive service for images
     * @param integer $maxResults maximum elements per page
     * @param string $userId
     * @param array $credentials
     * @return JSON string
     * @throws AuthenticationException
     * @throws ConnectorConfigException
     * @throws ImportException
     */
    public function exportImages($userId, $maxResults, array $credentials)
    {
        $client = new \Google_Client();
        $client->setClassConfig("Google_Http_Request", "disable_gzip", true);
        $client->setClientId($this->clientId);
        $client->setClientSecret($this->clientSecret);
        $client->setAccessToken(json_encode($credentials));

        if ($client->isAccessTokenExpired()) {
            try {
                $client->setClientId($this->clientId);
                $client->setClientSecret($this->clientSecret);
                $client->refreshToken($credentials["refresh_token"]);
            } catch(\Exception $e) {
                throw new AuthenticationException("Error refreshing token: " . $e->getMessage(), 602);
            }
        }

        $pageToken = null;
        $files = array();
        $count = 0;

        do {
            try {
                $driveService = new \Google_Service_Drive($client);
                $parameters = array();
                $parameters["q"] = "(mimeType contains 'image')";
                $parameters["maxResults"] = $maxResults;

                if ($pageToken) {
                    $parameters["pageToken"] = $pageToken;
                }

                $filesList = $driveService->files->listFiles($parameters);
                $files[$count] = $filesList->getItems();
                $count++;

                $pageToken = $filesList->getNextPageToken();
            } catch (Exception $e) {
                throw new ImportException("Error exporting files: " . $e->getMessage(), 601);
                $pageToken = null;
            }
        } while ($pageToken);

        return json_encode($files);
    }

    /**
     * Service that make a http request call in order to get a file from Google Drive
     * @param Google_Service_Drive $service
     * @param Google_Service_Drive_DriveFile $file
     * @return string
     */
    private function downloadFile($service, $file) {
        $downloadUrl = $file["downloadUrl"];
        if ($downloadUrl) {
            $request = new \Google_Http_Request($downloadUrl, 'GET', null, null);
            $httpRequest = $service->getClient()->getAuth()->authenticatedRequest($request);
            if ($httpRequest->getResponseHttpCode() == 200) {
                return $httpRequest->getResponseBody();
            } else {
                // An error occurred.
                return null;
            }
        } else {
            // The file doesn't have any content stored on Drive.
            return null;
        }
    }

    /**
     * Service that publish in Google +
     * @param array $credentials
     * @param array $parameters
     *      "userId"    => User whose google domain the stream will be published in
     *      "content"   => Text of the comment
     *      "link"      => External link
     *      "logo"      => Logo
     *      "circleId"  => Google circle where the stream will be published in
     *      "personId"  => Google + user whose domain the stream will be published in
     *      ($circleId are excluding)
     *
     * @return ExportDTO
     * @throws AuthenticationException
     * @throws ConnectorConfigException
     * @throws ExportException
     *
     */
    public function importPost(array $parameters, array $credentials) {
        $client = new \Google_Client();
        $client->setClassConfig("Google_Http_Request", "disable_gzip", true);
        $client->setClientId($this->clientId);
        $client->setClientSecret($this->clientSecret);
        $client->setAccessToken(json_encode($credentials));

        if ($client->isAccessTokenExpired()) {
            try {
                $client->setClientId($this->clientId);
                $client->setClientSecret($this->clientSecret);
                $client->refreshToken($credentials["refresh_token"]);
            } catch(\Exception $e) {
                throw new AuthenticationException("Error refreshing token: " . $e->getMessage(), 602);
            }
        }

        // Activity
        $postBody = new \Google_Service_PlusDomains_Activity();

        // Activity object
        $object = new \Google_Service_PlusDomains_ActivityObject();
        $object->setOriginalContent($parameters["content"]);

        // Activity attachments
        $attachments = array();

        if (isset($parameters["link"])) {
            $linkAttachment = new \Google_Service_Plus_ActivityObjectAttachments();
            $linkAttachment->setObjectType("article");
            $linkAttachment->setUrl($parameters["link"]);
            $postBody->setUrl($parameters["link"]);

            $attachments[] = $linkAttachment;
        }

        if (isset($parameters["logo"])) {
            $logoAttachment = new \Google_Service_Plus_ActivityObjectAttachments();
            $logoAttachment->setObjectType("photo");
            $logoAttachment->setUrl($parameters["logo"]);

            $attachments[] = $logoAttachment;
        }

        if (count($attachments) > 0) {
            $object->setAttachments($attachments);
        }

        $postBody->setObject($object);

        // Activity access
        $access = new \Google_Service_PlusDomains_Acl();
        $access->setDomainRestricted(true);

        $resource = new \Google_Service_PlusDomains_PlusDomainsAclentryResource();

        if ((!isset($parameters["circleId"])) && (!isset($parameters["personId"]))) {
            $resource->setType("domain");
        } else if (isset($parameters["circleId"])) {
            $resource->setType("circle");
            $resource->setId($parameters["circleId"]);
        } else if (isset($parameters["personId"])) {
            $resource->setType("person");
            $resource->setId($parameters["personId"]);
        }

        $resources = array();
        $resources[] = $resource;

        $access->setItems($resources);

        $postBody->setAccess($access);

        //$exportDto = new ExportDTO();

        try {
            $plusDomainService = new \Google_Service_PlusDomains($client);
            $activity = $plusDomainService->activities->insert($parameters["userId"], $postBody);
        } catch(\Exception $e) {
            throw new ExportException("Error exporting files: " . $e->getMessage(), 601);
        }

        return json_encode($activity);
    }

    /*public function checkAccessTokenExpiry($userId) {
        $client = new \Google_Client();
        $client->setClassConfig("Google_Http_Request", "disable_gzip", true);
        $client->setClientId($this->clientId);
        $client->setClientSecret($this->clientSecret);
        $client->setAccessToken(json_encode($credentials));

        if ($client->isAccessTokenExpired()) {
            try {
                $client->setClientId($this->clientId);
                $client->setClientSecret($this->clientSecret);
                $client->refreshToken($credentials["refresh_token"]);
            } catch(\Exception $e) {
                throw new AuthenticationException("Error refreshing token: " . $e->getMessage(), 602);
            }
        }

        $googleCredentials = json_decode($client->getAccessToken(), true);

        return $googleCredentials["expires_in"];
    }*/
    /**
     * Authentication service from google sign in request
     * @param string $code
     * @param string $redirectUrl
     * @return array
     * @throws AuthenticationException
     * @throws ConnectorException
     * @throws MalformedException
     * @throws \Exception
     *
     */
    public function authorize($code, $redirectUrl)
    {
        if ((null === $redirectUrl) || (empty($redirectUrl))) {
            throw new ConnectorConfigException("'redirectUrl' parameter is required", 618);
        } else {
            if (!$this->wellFormedUrl($redirectUrl)) {
                throw new MalformedUrlException("'redirectUrl' is malformed", 600);
            }
        }

        $client = new \Google_Client();
        $client->setClassConfig("Google_Http_Request", "disable_gzip", true);
        $client->setClientId($this->clientId);
        $client->setClientSecret($this->clientSecret);
        $client->setRedirectUri($redirectUrl);

        try {
            $client->authenticate($code);

            $googleCredentials = $client->getAccessToken();
            $googleCredentials = json_decode($client->getAccessToken(), true);
        } catch(\Exception $e) {
            if (401 === $e->getCode()) {
                throw new AuthenticationException("Error fetching OAuth2 access token, client is invalid", 601);
            } else {
                throw new \Exception($e->getMessage(), $e->getCode());
            }
        }

        return $googleCredentials;
    }

    /**
     * Service that query to Google api to revoke access token in order
     * to ensure the permissions granted to the application are removed
     * @param array $credentials
     * @throws ConnectorConfigException
     * @return JSON string
     * @throws AuthenticationException
     */
    public function revokeToken(array $credentials)
    {
        $client = new \Google_Client();
        $client->setClassConfig("Google_Http_Request", "disable_gzip", true);
        $client->setClientId($this->clientId);
        $client->setClientSecret($this->clientSecret);
        $client->setAccessToken(json_encode($credentials));

        if ($client->isAccessTokenExpired()) {
            try {
                $client->setClientId($this->clientId);
                $client->setClientSecret($this->clientSecret);
                $client->refreshToken($credentials["refresh_token"]);
            } catch(\Exception $e) {
                throw new AuthenticationException("Error refreshing token: " . $e->getMessage(), 602);
            }
        }

        $client->revokeToken();

        return json_encode(array(
            "status" => "success",
            "note" => "Following a successful revocation response, it might take some time before the revocation has full effect"
        ));
    }

    /**
     * Compose Google Api credentials array from session data
     * @param string $redirectUrl
     * @return array
     * @throws ConnectorConfigException
     * @throws MalformedUrlException
     */
    public function getAuth($redirectUrl)
    {
        if ((null === $redirectUrl) || (empty($redirectUrl))) {
            throw new ConnectorConfigException("'redirectUrl' parameter is required", 618);
        } else {
            if (!$this->wellFormedUrl($redirectUrl)) {
                throw new MalformedUrlException("'redirectUrl' is malformed", 600);
            }
        }

        return SocialNetworks::getInstance()->getSocialLoginUrl(GoogleApi::ID, $redirectUrl);
    }

    /**
     * Service that compose url to authorize google api
     * @param string $redirectUrl
     * @return string
     * @throws ConnectorConfigException
     * @throws MalformedUrlException
     */
    public function getAuthUrl($redirectUrl)
    {
        if ((null === $redirectUrl) || (empty($redirectUrl))) {
            throw new ConnectorConfigException("'redirectUrl' parameter is required", 618);
        } else {
            if (!$this->wellFormedUrl($redirectUrl)) {
                throw new MalformedUrlException("'redirectUrl' is malformed", 600);
            }
        }

        $client = new \Google_Client();
        $client->setClassConfig("Google_Http_Request", "disable_gzip", true);
        $client->setAccessType("offline");
        $client->setClientId($this->clientId);
        $client->setClientSecret($this->clientSecret);
        $client->setRedirectUri($redirectUrl);
        foreach($this->clientScope as $scope) {
            $client->addScope($scope);
        }

        $authUrl = $client->createAuthUrl();

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