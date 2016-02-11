<?php
namespace CloudFramework\Service\SocialNetworks\Connectors;

use CloudFramework\Patterns\Singleton;
use CloudFramework\Service\SocialNetworks\Exceptions\AuthenticationException;
use CloudFramework\Service\SocialNetworks\Exceptions\ConnectorConfigException;
use CloudFramework\Service\SocialNetworks\Exceptions\ConnectorServiceException;
use CloudFramework\Service\SocialNetworks\Exceptions\MalformedUrlException;
use CloudFramework\Service\SocialNetworks\Interfaces\SocialNetworkInterface;
use CloudFramework\Service\SocialNetworks\SocialNetworks;

/**
 * Class GoogleApi
 * @package CloudFramework\Service\SocialNetworks\Connectors
 * @author Salvador Castro <sc@bloombees.com>
 */
class GoogleApi extends Singleton implements SocialNetworkInterface {

    const ID = 'google';
    const MAX_IMPORT_FILE_SIZE = 37748736; // 36MB
    const MAX_IMPORT_FILE_SIZE_MB = 36;
    const BLOCK_SIZE_BYTES = 1048576; // Blocks of 1MB

    // Google client object
    private $client;

    // API keys
    private $clientId;
    private $clientSecret;
    private $clientScope = array();

    /**
     * Set Google Api credentials
     * @param $clientId
     * @param $clientSecret
     * @param $clientScope
     * @throws ConnectorConfigException
     */
    public function setCredentials($clientId, $clientSecret, $clientScope) {
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

        $this->client = new \Google_Client();
        $this->client->setClassConfig("Google_Http_Request", "disable_gzip", true);
        $this->client->setClientId($this->clientId);
        $this->client->setClientSecret($this->clientSecret);
    }

    /**
     * Service that query to Google Api for people in user circles
     * @param string $userId
     * @param integer $maxResultsPerPage maximum elements per page
     * @param integer $numberOfPages number of pages
     * @param string $pageToken Indicates a specific page for pagination
     * @param array $credentials
     * @return JSON
     * @throws AuthenticationException
     * @throws ConnectorConfigException
     * @throws ConnectorServiceException
     */
    public function getFollowers($userId, $maxResultsPerPage, $numberOfPages, $pageToken, array $credentials)
    {
        $this->checkCredentials($credentials);

        $this->checkUser($userId);

        $this->checkPagination($maxResultsPerPage, $numberOfPages);

        $this->setAccessToken($credentials);

        $people = array();
        $count = 0;

        do {
            try {
                $plusDomainService = new \Google_Service_PlusDomains($this->client);
                $parameters = array();
                $parameters["maxResults"] = $maxResultsPerPage;

                if ($pageToken) {
                    $parameters["pageToken"] = $pageToken;
                }

                $peopleList = $plusDomainService->people->listPeople($userId, "circled", $parameters);

                $people[$count] = array();
                foreach($peopleList->getItems() as $person) {
                    $people[$count][] = $person->toSimpleObject();
                }
                $count++;

                $pageToken = $peopleList->getNextPageToken();

                // If number of pages is zero, then all elements are returned
                if (($numberOfPages > 0) && ($count == $numberOfPages)) {
                    break;
                }
            } catch (Exception $e) {
                throw new ConnectorServiceException("Error exporting people: " . $e->getMessage(), $e->getCode());
                $pageToken = null;
            }
        } while ($pageToken);

        $people["pageToken"] = $pageToken;

        return json_encode($people);
    }

    /**
     * Service that query to Google Api for followers info (likes and shares) of a post
     * @param string $postId
     * @param array $credentials
     * @return JSON
     * @throws AuthenticationException
     * @throws ConnectorConfigException
     * @throws ConnectorServiceException
     */
    public function getFollowersInfo($postId, array $credentials)
    {
        $this->checkCredentials($credentials);

        if ((null === $postId) || ("" === $postId)) {
            throw new ConnectorConfigException("'postId' parameter is required", 612);
        }

        $this->setAccessToken($credentials);

        try {
            $people = array();
            $plusDomainsService = new \Google_Service_PlusDomains($this->client);
            $plusoners = $plusDomainsService->people->listByActivity($postId, "plusoners")->getItems();
            $resharers = $plusDomainsService->people->listByActivity($postId, "resharers")->getItems();
            $sharedto = $plusDomainsService->people->listByActivity($postId, "sharedto")->getItems();

            $people["plusoners"] = array();
            $people["resharers"] = array();
            $people["sharedto"] = array();

            foreach($plusoners as $plusoner) {
                $people["plusoners"][] = $plusoner->toSimpleObject();
            }

            foreach($resharers as $resharer) {
                $people["resharers"][] = $resharer->toSimpleObject();
            }

            foreach($sharedto as $shared) {
                $people["sharedto"][] = $shared->toSimpleObject();
            }
        } catch(\Exception $e) {
            throw new ConnectorServiceException("Error getting people in Google+ post: " . $e->getMessage(), $e->getCode());
        }

        return json_encode($people);
    }

    public function getSubscribers($userId, $maxResultsPerPage, $numberOfPages, $nextPageUrl, array $credentials = array()) {
        return;
    }

    /**
     * Service that query to Google Api for posts/activities of a user
     * @param string $userId
     * @param integer $maxResultsPerPage maximum elements per page
     * @param integer $numberOfPages number of pages
     * @param string $pageToken Indicates a specific page for pagination
     * @param array $credentials
     * @return JSON
     * @throws AuthenticationException
     * @throws ConnectorConfigException
     * @throws ConnectorServiceException
     */
    public function getPosts($userId, $maxResultsPerPage, $numberOfPages, $pageToken, array $credentials)
    {
        $this->checkCredentials($credentials);

        $this->checkUser($userId);

        $this->checkPagination($maxResultsPerPage, $numberOfPages);

        $this->setAccessToken($credentials);

        $activities = array();
        $count = 0;

        do {
            try {
                $plusDomainsService = new \Google_Service_PlusDomains($this->client);
                $parameters = array();
                $parameters["maxResults"] = $maxResultsPerPage;

                if ($pageToken) {
                    $parameters["pageToken"] = $pageToken;
                }

                $activitiesList = $plusDomainsService->activities->listActivities($userId, "user", $parameters);

                $activities[$count] = array();
                foreach($activitiesList->getItems() as $activity) {
                    $activities[$count][] = $activity->toSimpleObject();
                }
                $count++;

                $pageToken = $activitiesList->getNextPageToken();

                if ($count == $numberOfPages) {
                    break;
                }
            } catch (Exception $e) {
                throw new ConnectorServiceException("Error exporting posts: " . $e->getMessage(), $e->getCode());
                $pageToken = null;
            }
        } while ($pageToken);

        $activities["pageToken"] = $pageToken;

        return json_encode($activities);
    }

    /**
     * Service that query to Google Oauth Api to get user profile
     * @param string $userId
     * @param array $credentials
     * @return JSON
     * @throws AuthenticationException
     * @throws ConnectorConfigException
     * @throws ConnectorServiceException
     */
    public function getProfile($userId, array $credentials)
    {
        $this->checkCredentials($credentials);

        $this->checkUser($userId);

        $this->setAccessToken($credentials);

        try {
            $plusService = new \Google_Service_Plus($this->client);
            $profile = $plusService->people->get($userId);
        } catch(\Exception $e) {
            throw new ConnectorServiceException("Error fetching user profile info: " . $e->getMessage(), $e->getCode());
        }

        return json_encode($profile->toSimpleObject());
    }

    /**
     * Service that query to Google Oauth Api to get user profile id
     * @param array $credentials
     * @return string
     * @throws AuthenticationException
     * @throws ConnectorConfigException
     * @throws ConnectorServiceException
     */
    public function getProfileId(array $credentials)
    {
        $this->checkCredentials($credentials);

        $this->setAccessToken($credentials);

        try {
            $oauthService = new \Google_Service_Oauth2($this->client);
            $profile = $oauthService->userinfo_v2_me->get();
        } catch(\Exception $e) {
            throw new ConnectorServiceException("Error fetching user profile info: " . $e->getMessage(), $e->getCode());
        }

        return $profile->getId();
    }

    /**
     * Service that query to Google Api Drive service for images
     * @param string $userId
     * @param integer $maxResultsPerPage maximum elements per page
     * @param integer $numberOfPages number of pages
     * @param string $pageToken Indicates a specific page
     * @param array $credentials
     * @return JSON
     * @throws AuthenticationException
     * @throws ConnectorConfigException
     * @throws ConnectorServiceException
     */
    public function exportImages($userId, $maxResultsPerPage, $numberOfPages, $pageToken, array $credentials)
    {
        $this->checkCredentials($credentials);

        $this->checkUser($userId);

        $this->checkPagination($maxResultsPerPage, $numberOfPages);

        $this->setAccessToken($credentials);

        $files = array();
        $count = 0;

        do {
            try {
                $driveService = new \Google_Service_Drive($this->client);
                $parameters = array();
                $parameters["q"] = "(mimeType contains 'image' and trashed = false)";
                $parameters["maxResults"] = $maxResultsPerPage;

                if ($pageToken) {
                    $parameters["pageToken"] = $pageToken;
                }

                $filesList = $driveService->files->listFiles($parameters);
                $files[$count] = $filesList->getItems();
                $count++;

                $pageToken = $filesList->getNextPageToken();

                // If number of pages == 0, then all elements are returned
                if (($numberOfPages > 0) && ($count == $numberOfPages)) {
                    break;
                }
            } catch (Exception $e) {
                throw new ConnectorServiceException("Error exporting files: " . $e->getMessage(), $e->getCode());
                $pageToken = null;
            }
        } while ($pageToken);

        $files["pageToken"] = $pageToken;

        return json_encode($files);
    }

    /**
     * Service that upload a media file (image/video) to Google+
     * @param string $userId
     * @param string $path Path to media
     * @param array $credentials
     * @return JSON
     * @throws AuthenticationException
     * @throws ConnectorConfigException
     * @throws ConnectorServiceException
     */
    public function importMedia($userId, $path, array $credentials)
    {
        $this->checkCredentials($credentials);

        $this->checkUser($userId);

        if ((null === $path) || ("" === $path)) {
            throw new ConnectorConfigException("'path' parameter is required", 613);
        } else {
            if (!file_exists($path)) {
                throw new ConnectorConfigException("file doesn't exist", 614);
            } else {
                $mimeType = mime_content_type($path);
                if ((false === strpos($mimeType,"image/")) && (false === strpos($mimeType,"video/"))) {
                    throw new ConnectorConfigException("file must be an image or a video", 615);
                } else {
                    $filesize = filesize($path);
                    if ($filesize > self::MAX_IMPORT_FILE_SIZE) {
                        throw new ConnectorConfigException("Maximum file size is ".(self::MAX_IMPORT_FILE_SIZE_MB)."MB", 616);
                    }
                }
            }
        }

        $this->setAccessToken($credentials);

        try {
            $plusDomainsService = new \Google_Service_PlusDomains($this->client);
            $plusDomainsMedia = new \Google_Service_PlusDomains_Media();
            $plusDomainsMedia->setDisplayName("Uploaded media file");

            $params = array();
            $params["uploadType"] = "media";

            // Size of each chunk of data in bytes. Setting it higher leads faster upload (less chunks,
            // for reliable connections). Setting it lower leads better recovery (fine-grained chunks)
            $chunkSizeBytes = self::BLOCK_SIZE_BYTES;

            // Setting the defer flag to true tells the client to return a request which can be called
            // with ->execute(); instead of making the API call immediately.
            $this->client->setDefer(true);

            $insertRequest = $plusDomainsService->media->insert($userId, "cloud", $plusDomainsMedia, $params);

            $media = new \Google_Http_MediaFileUpload(
                $this->client,
                $insertRequest,
                $mimeType,
                null,
                true,
                $chunkSizeBytes
            );

            $media->setFileSize($filesize);

            // Upload the various chunks. $status will be false until the process is complete.
            $status = false;
            $handle = fopen($path, "rb");
            while (!$status && !feof($handle)) {
                $chunk = fread($handle, $chunkSizeBytes);
                $status = $media->nextChunk($chunk);
            }

            // The final value of $status will be the data from the API for the object that has been uploaded.
            $result = false;
            if($status != false) {
                $result = $status;
            }

            fclose($handle);
            // Reset to the client to execute requests immediately in the future.
            $this->client->setDefer(false);

            return json_encode($result);
        } catch (Exception $e) {
            throw new ConnectorServiceException("Error importing '".$path."'': " . $e->getMessage(), $e->getCode());
        }
    }

    /**
     * Service that publish in Google +
     * @param array $credentials
     * @param array $parameters
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
     * @return JSON
     * @throws AuthenticationException
     * @throws ConnectorConfigException
     * @throws ConnectorServiceException
     */
    public function post(array $parameters, array $credentials) {
        $this->checkCredentials($credentials);

        if ((null === $parameters) || (!is_array($parameters)) || (count($parameters) == 0)) {
            throw new ConnectorConfigException("Invalid post parameters'", 617);
        }

        if ((!isset($parameters["user_id"])) || (null === $parameters["user_id"]) || ("" === $parameters["user_id"])) {
            throw new ConnectorConfigException("'user_id' post parameter is required", 618);
        }

        if ((!isset($parameters["content"])) || (null === $parameters["content"]) || ("" === $parameters["content"])) {
            throw new ConnectorConfigException("'content' post parameter is required", 619);
        }

        if ((!isset($parameters["access_type"])) || (null === $parameters["access_type"]) || ("" === $parameters["access_type"])) {
            throw new ConnectorConfigException("'access_type' post parameter is required", 620);
        } else {
            if (("circle" == $parameters["access_type"]) &&
                ((!isset($parameters["circle_id"])) || (null === $parameters["circle_id"]) || ("" === $parameters["circle_id"]))) {
                throw new ConnectorConfigException("'circle_id' post parameter is required since access_type is 'circle'", 621);
            }

            if (("person" == $parameters["access_type"]) &&
                ((!isset($parameters["person_id"])) || (null === $parameters["person_id"]) || ("" === $parameters["person_id"]))) {
                throw new ConnectorConfigException("'person_id' post parameter is required since access_type is 'person'", 622);
            }
        }

        if ((isset($parameters["attachment"])) && (!is_array($parameters["attachment"]))) {
            throw new ConnectorConfigException("'attachment' post parameter must be an array", 623);
        } else {
            if (count($parameters["attachment"]) == 0) {
                throw new ConnectorConfigException("'attachment' post parameter array is empty'", 632);
            }
            if ((isset($parameters["attachment"][0])) &&
                (("link" !== $parameters["attachment"][0]) &&
                ("photo" !== $parameters["attachment"][0]) &&
                ("video" !== $parameters["attachment"][0]))) {
                throw new ConnectorConfigException("'attachment' type must be 'link', 'photo' or 'video'", 624);
            }

            if ((isset($parameters["attachment"][1])) &&
                ((null === $parameters["attachment"][1]) ||
                ("" === $parameters["attachment"][1]))) {
                throw new ConnectorConfigException("'attachment' value must be an url ('link') or a file path ('photo' or 'video')", 625);
            } else {
                if (("link" === $parameters["attachment"][0]) && (!$this->wellFormedUrl($parameters["attachment"][1]))) {
                    throw new ConnectorConfigException("'attachment' value url is malformed", 626);
                }
            }
        }

        $this->setAccessToken($credentials);

        // Activity
        $postBody = new \Google_Service_PlusDomains_Activity();

        // Activity object
        $object = new \Google_Service_PlusDomains_ActivityObject();
        $object->setObjectType("activity");
        $object->setOriginalContent($parameters["content"]);

        // Activity attachments
        $attachments = array();

        if (isset($parameters["attachment"])) {
            switch($parameters["attachment"][0]) {
                case "link":
                    $linkAttachment = new \Google_Service_PlusDomains_ActivityObjectAttachments();
                    $linkAttachment->setObjectType("article");
                    $linkAttachment->setUrl($parameters["attachment"][1]);
                    $postBody->setUrl($parameters["attachment"][1]);
                    $attachments[] = $linkAttachment;
                    break;
                default:
                    $mediaAttachment = new \Google_Service_PlusDomains_ActivityObjectAttachments();
                    $mediaAttachment->setObjectType($parameters["attachment"][0]);
                    $mediaAttachment->setId($parameters["attachment"][1]);
                    $attachments[] = $mediaAttachment;
                    break;
            }
        }

        if (count($attachments) > 0) {
            $object->setAttachments($attachments);
        }

        $postBody->setObject($object);

        // Activity access
        $access = new \Google_Service_PlusDomains_Acl();
        $access->setDomainRestricted(true);

        $resource = new \Google_Service_PlusDomains_PlusDomainsAclentryResource();

        $resource->setType($parameters["access_type"]);
        if ("circle" === $resource->getType()) {
            $resource->setId($parameters["circle_id"]);
        } else if ("person" === $resource->getType()) {
            $resource->setId($parameters["person_id"]);
        }

        $resources = array();
        $resources[] = $resource;

        $access->setItems($resources);
        $postBody->setAccess($access);

        try {
            $plusDomainService = new \Google_Service_PlusDomains($this->client);
            $activity = $plusDomainService->activities->insert($parameters["user_id"], $postBody);
        } catch(\Exception $e) {
            throw new ConnectorServiceException("Error creating post: " . $e->getMessage(), $e->getCode());
        }

        return json_encode($activity);
    }

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

        $this->client->setRedirectUri($redirectUrl);

        try {
            $this->client->authenticate($code);

            $googleCredentials = $this->client->getAccessToken();
            $googleCredentials = json_decode($this->client->getAccessToken(), true);
        } catch(\Exception $e) {
            if (401 === $e->getCode()) {
                throw new AuthenticationException("Error fetching OAuth2 access token, client is invalid", 601);
            } else {
                throw new ConnectorServiceException($e->getMessage(), $e->getCode());
            }
        }

        return $googleCredentials;
    }

    /**
     * Service that query to Google api to revoke access token in order
     * to ensure the permissions granted to the application are removed
     * @param array $credentials
     * @return JSON
     * @throws AuthenticationException
     * @throws ConnectorConfigException
     * @throws ConnectorServiceException
     */
    public function revokeToken(array $credentials)
    {
        $this->checkCredentials($credentials);

        $this->setAccessToken($credentials);

        try {
            $this->client->revokeToken();
        } catch(\Exception $e) {
            throw new ConnectorServiceException("Error revoking token: " . $e->getMessage(), $e->getCode());
        }

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
            throw new ConnectorConfigException("'redirectUrl' parameter is required", 628);
        } else {
            if (!$this->wellFormedUrl($redirectUrl)) {
                throw new MalformedUrlException("'redirectUrl' is malformed", 601);
            }
        }

        return SocialNetworks::getInstance()->getSocialLoginUrl(self::ID, $redirectUrl);
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
            throw new ConnectorConfigException("'redirectUrl' parameter is required", 628);
        } else {
            if (!$this->wellFormedUrl($redirectUrl)) {
                throw new MalformedUrlException("'redirectUrl' is malformed", 601);
            }
        }

        $this->client->setRedirectUri($redirectUrl);
        $this->client->setAccessType("offline");
        foreach($this->clientScope as $scope) {
            $this->client->addScope($scope);
        }

        $authUrl = $this->client->createAuthUrl();

        if ((null === $authUrl) || (empty($authUrl))) {
            throw new ConnectorConfigException("'authUrl' parameter is required", 629);
        } else {
            if (!$this->wellFormedUrl($authUrl)) {
                throw new MalformedUrlException("'authUrl' is malformed", 602);
            }
        }

        // Authentication request
        return $authUrl;
    }

    /**
     * Method that set (and refresh if is necessary) the access token
     * @param array $credentials
     * @throws AuthenticationException
     */
    private function setAccessToken(array $credentials) {
        $this->client->setAccessToken(json_encode($credentials));

        if ($this->client->isAccessTokenExpired()) {
            try {
                $this->client->setClientId($this->clientId);
                $this->client->setClientSecret($this->clientSecret);
                $this->client->refreshToken($credentials["refresh_token"]);
            } catch(\Exception $e) {
                throw new AuthenticationException("Error refreshing token: " . $e->getMessage(), 602);
            }
        }
    }

    /**
     * Method that check credentials and userId are ok
     * @param array $credentials
     * @throws ConnectorConfigException
     */
    private function checkCredentials(array $credentials) {
        if ((null === $credentials) || (!is_array($credentials)) || (count($credentials) == 0)) {
            throw new ConnectorConfigException("Invalid credentials set'", 604);
        }

        if ((!isset($credentials["access_token"])) || (null === $credentials["access_token"]) || ("" === $credentials["access_token"])) {
            throw new ConnectorConfigException("'access_token' parameter is required", 605);
        }

        if ((!isset($credentials["refresh_token"])) || (null === $credentials["refresh_token"]) || ("" === $credentials["refresh_token"])) {
            throw new ConnectorConfigException("'refresh_token' parameter is required", 606);
        }
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
     * @param $maxResultsPerPage
     * @param $numberOfPages
     * @throws ConnectorConfigException
     */
    private function checkPagination($maxResultsPerPage, $numberOfPages) {
        if (null === $maxResultsPerPage) {
            throw new ConnectorConfigException("'maxResultsPerPage' parameter is required", 608);
        } else if (!is_numeric($maxResultsPerPage)) {
            throw new ConnectorConfigException("'maxResultsPerPage' parameter is not numeric", 609);
        }

        if (null === $maxResultsPerPage) {
            throw new ConnectorConfigException("'numberOfPages' parameter is required", 610);
        } else if (!is_numeric($numberOfPages)) {
            throw new ConnectorConfigException("'numberOfPages' parameter is not numeric", 611);
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