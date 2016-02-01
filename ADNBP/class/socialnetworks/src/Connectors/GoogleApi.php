<?php
namespace CloudFramework\Service\SocialNetworks\Connectors;

use CloudFramework\Patterns\Singleton;
use CloudFramework\Service\SocialNetworks\Exceptions\ConnectorConfigException;
use CloudFramework\Service\SocialNetworks\Exceptions\MalformedUrlException;
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
    public static $auth_keys = array("access_token", "token_type", "expires_in", "created", "refresh_token");
    public static $api_keys = array("client", "secret");

    /**
     * Compose Google Api credentials array from session data
     * @param array|null $credentials
     * @param string $redirectUrl
     * @return array
     * @throws ConnectorConfigException
     * @throws MalformedUrlException
     */
    public function getAuth(array $credentials, $redirectUrl)
    {
        if (count($credentials) == 0) {
            throw new ConnectorConfigException("credentials set is empty", 600);
        }

        if (!array_key_exists('client', $credentials)) {
            throw new ConnectorConfigException("'client' parameter is required", 601);
        } else if ((null === $credentials["client"]) || (empty($credentials["client"]))) {
            throw new ConnectorConfigException("'client' parameter is empty", 602);
        }

        if (!array_key_exists('secret', $credentials)) {
            throw new ConnectorConfigException("'secret' parameter is required", 603);
        } else if ((null === $credentials["secret"]) || (empty($credentials["secret"]))) {
            throw new ConnectorConfigException("'secret' parameter is empty", 604);
        }

        if ((null === $redirectUrl) || (empty($redirectUrl))) {
            throw new ConnectorConfigException("'redirectUrl' parameter is required", 624);
        } else {
            if (!$this->wellFormedUrl($redirectUrl)) {
                throw new MalformedUrlException("'redirectUrl' is malformed", 600);
            }
        }

        return SocialNetworks::hydrateCredentials(GoogleApi::ID, GoogleApi::$auth_keys,
                                                        GoogleApi::$api_keys, $credentials, $redirectUrl);
    }

    /**
     * Service that compose url to authorize google api
     * @param array $apiKeys
     * @param string $redirectUrl
     * @return string
     * @throws ConnectorConfigException
     * @throws MalformedUrlException
     */
    public function getAuthUrl(array $apiKeys, $redirectUrl)
    {
        if (count($apiKeys) > 0) {
            $_SESSION[GoogleApi::ID . "_apikeys"] = $apiKeys;
        } else {
            throw new ConnectorConfigException("apiKeys set is empty", 600);
        }

        if (!array_key_exists('client', $apiKeys)) {
            throw new ConnectorConfigException("'client' parameter is required", 601);
        } else if ((null === $apiKeys["client"]) || (empty($apiKeys["client"]))) {
            throw new ConnectorConfigException("'client' parameter is empty", 602);
        }

        if (!array_key_exists('secret', $apiKeys)) {
            throw new ConnectorConfigException("'secret' parameter is required", 603);
        } else if ((null === $apiKeys["secret"]) || (empty($apiKeys["secret"]))) {
            throw new ConnectorConfigException("'secret' parameter is empty", 604);
        }

        if ((null === $redirectUrl) || (empty($redirectUrl))) {
            throw new ConnectorConfigException("'redirectUrl' parameter is required", 624);
        } else {
            if (!$this->wellFormedUrl($redirectUrl)) {
                throw new MalformedUrlException("'redirectUrl' is malformed", 600);
            }
        }

        $client = new \Google_Client();
        $client->setAccessType("offline");
        $client->setClientId($apiKeys["client"]);
        $client->setClientSecret($apiKeys["secret"]);
        $client->setRedirectUri($redirectUrl);
        $client->addScope("https://www.googleapis.com/auth/plus.me");
        $client->addScope("https://www.googleapis.com/auth/drive");
        $client->addScope("https://www.googleapis.com/auth/plus.circles.read");
        $client->addScope("https://www.googleapis.com/auth/plus.stream.write");
        $client->addScope("https://www.googleapis.com/auth/plus.media.upload");
        $client->addScope("https://www.googleapis.com/auth/userinfo.email");
        $client->addScope("https://www.googleapis.com/auth/userinfo.profile");

        // Authentication request
        return $client->createAuthUrl();
    }

    /**
     * Service that query to Google Oauth Api to get user profile
     * @param array $credentials
     * @return ProfileDTO
     * @throws ConnectorConfigException
     */
    public function getProfile(array $credentials)
    {
        if ((count($credentials) == 0) ||
            (!isset($credentials["api_keys"])) ||
            (null === $credentials["api_keys"]) ||
            (!is_array($credentials["api_keys"]))) {
            throw new ConnectorConfigException("api_keys set is empty", 600);
        }

        if (!array_key_exists('client', $credentials["api_keys"])) {
            throw new ConnectorConfigException("'client' parameter is required", 601);
        } else if ((null === $credentials["api_keys"]["client"]) || (empty($credentials["api_keys"]["client"]))) {
            throw new ConnectorConfigException("'client' parameter is empty", 602);
        }

        if (!array_key_exists('secret', $credentials["api_keys"])) {
            throw new ConnectorConfigException("'secret' parameter is required", 603);
        } else if ((null === $credentials["api_keys"]["secret"]) || (empty($credentials["api_keys"]["secret"]))) {
            throw new ConnectorConfigException("'secret' parameter is empty", 604);
        }

        if ((!isset($credentials["auth_keys"])) ||
            (null === $credentials["auth_keys"]) ||
            (!is_array($credentials["auth_keys"]))) {
            throw new ConnectorConfigException("auth_keys set is empty", 605);
        }

        if (!array_key_exists('access_token', $credentials["auth_keys"])) {
            throw new ConnectorConfigException("'access_token' parameter is required", 606);
        } else if ((null === $credentials["auth_keys"]["access_token"]) || (empty($credentials["auth_keys"]["access_token"]))) {
            throw new ConnectorConfigException("'access_token' parameter is empty", 607);
        }

        if (!array_key_exists('token_type', $credentials["auth_keys"])) {
            throw new ConnectorConfigException("'token_type' parameter is required", 608);
        } else if ((null === $credentials["auth_keys"]["token_type"]) || (empty($credentials["auth_keys"]["token_type"]))) {
            throw new ConnectorConfigException("'token_type' parameter is empty", 609);
        }

        if (!array_key_exists('expires_in', $credentials["auth_keys"])) {
            throw new ConnectorConfigException("'expires_in' parameter is required", 610);
        } else if ((null === $credentials["auth_keys"]["expires_in"]) || (empty($credentials["auth_keys"]["expires_in"]))) {
            throw new ConnectorConfigException("'expires_in' parameter is empty", 611);
        }

        if (!array_key_exists('created', $credentials["auth_keys"])) {
            throw new ConnectorConfigException("'created' parameter is required", 612);
        } else if ((null === $credentials["auth_keys"]["created"]) || (empty($credentials["auth_keys"]["created"]))) {
            throw new ConnectorConfigException("'created' parameter is empty", 613);
        }

        if (!array_key_exists('refresh_token', $credentials["auth_keys"])) {
            throw new ConnectorConfigException("'refresh_token' parameter is required", 614);
        } else if ((null === $credentials["auth_keys"]["refresh_token"]) || (empty($credentials["auth_keys"]["refresh_token"]))) {
            throw new ConnectorConfigException("'refresh_token' parameter is empty", 615);
        }

        $client = new \Google_Client();
        $client->setAccessToken(json_encode($credentials["auth_keys"]));

        if ($client->isAccessTokenExpired()) {
            try {
                $client->setClientId($credentials["api_keys"]["client"]);
                $client->setClientSecret($credentials["api_keys"]["secret"]);
                $client->refreshToken($credentials["auth_keys"]["refresh_token"]);
            } catch(\Exception $e) {
                SocialNetworks::generateErrorResponse($e->getMessage(), 500);
            }
        }

        $oauthService = new \Google_Service_Oauth2($client);
        $profile = $oauthService->userinfo_v2_me->get();

        $profileDto = new ProfileDTO($profile->getId(), $profile->getGivenName()." ".$profile->getFamilyName(),
            $profile->getEmail(), $profile->getPicture());

        return $profileDto;
    }

    /**
     * Service that query to Google Api Drive service for images
     * @param array $credentials
     * @param string $path path where files imported will be saved
     * @return array
     * @throws ConnectorConfigException
     */
    public function import(array $credentials, $path)
    {
        if ((count($credentials) == 0) ||
            (!isset($credentials["api_keys"])) ||
            (null === $credentials["api_keys"]) ||
            (!is_array($credentials["api_keys"]))) {
            throw new ConnectorConfigException("api_keys set is empty", 600);
        }

        if (!array_key_exists('client', $credentials["api_keys"])) {
            throw new ConnectorConfigException("'client' parameter is required", 601);
        } else if ((null === $credentials["api_keys"]["client"]) || (empty($credentials["api_keys"]["client"]))) {
            throw new ConnectorConfigException("'client' parameter is empty", 602);
        }

        if (!array_key_exists('secret', $credentials["api_keys"])) {
            throw new ConnectorConfigException("'secret' parameter is required", 603);
        } else if ((null === $credentials["api_keys"]["secret"]) || (empty($credentials["api_keys"]["secret"]))) {
            throw new ConnectorConfigException("'secret' parameter is empty", 604);
        }

        if ((!isset($credentials["auth_keys"])) ||
            (null === $credentials["auth_keys"]) ||
            (!is_array($credentials["auth_keys"]))) {
            throw new ConnectorConfigException("auth_keys set is empty", 605);
        }

        if (!array_key_exists('access_token', $credentials["auth_keys"])) {
            throw new ConnectorConfigException("'access_token' parameter is required", 606);
        } else if ((null === $credentials["auth_keys"]["access_token"]) || (empty($credentials["auth_keys"]["access_token"]))) {
            throw new ConnectorConfigException("'access_token' parameter is empty", 607);
        }

        if (!array_key_exists('token_type', $credentials["auth_keys"])) {
            throw new ConnectorConfigException("'token_type' parameter is required", 608);
        } else if ((null === $credentials["auth_keys"]["token_type"]) || (empty($credentials["auth_keys"]["token_type"]))) {
            throw new ConnectorConfigException("'token_type' parameter is empty", 609);
        }

        if (!array_key_exists('expires_in', $credentials["auth_keys"])) {
            throw new ConnectorConfigException("'expires_in' parameter is required", 610);
        } else if ((null === $credentials["auth_keys"]["expires_in"]) || (empty($credentials["auth_keys"]["expires_in"]))) {
            throw new ConnectorConfigException("'expires_in' parameter is empty", 611);
        }

        if (!array_key_exists('created', $credentials["auth_keys"])) {
            throw new ConnectorConfigException("'created' parameter is required", 612);
        } else if ((null === $credentials["auth_keys"]["created"]) || (empty($credentials["auth_keys"]["created"]))) {
            throw new ConnectorConfigException("'created' parameter is empty", 613);
        }

        if (!array_key_exists('refresh_token', $credentials["auth_keys"])) {
            throw new ConnectorConfigException("'refresh_token' parameter is required", 614);
        } else if ((null === $credentials["auth_keys"]["refresh_token"]) || (empty($credentials["auth_keys"]["refresh_token"]))) {
            throw new ConnectorConfigException("'refresh_token' parameter is empty", 615);
        }

        if ((null === $path) || (empty($path))) {
            throw new ConnectorConfigException("'path' parameter is required", 618);
        }

        $client = new \Google_Client();
        $client->setAccessToken(json_encode($credentials["auth_keys"]));

        if ($client->isAccessTokenExpired()) {
            try {
                $client->setClientId($credentials["api_keys"]["client"]);
                $client->setClientSecret($credentials["api_keys"]["secret"]);
                $client->refreshToken($credentials["auth_keys"]["refresh_token"]);
            } catch(\Exception $e) {
                SocialNetworks::generateErrorResponse($e->getMessage(), 500);
            }
        }

        $driveService = new \Google_Service_Drive($client);
        $filesList = $driveService->files->listFiles(array(
            "q" => "mimeType contains 'image'"
        ))->getItems();

        $files = array();
        foreach($filesList as $key=>$fileList) {
            if (("image/gif" === $fileList["mimeType"]) ||
                ("image/jpeg" === $fileList["mimeType"]) ||
                ("image/pjpeg" === $fileList["mimeType"]) ||
                ("image/png" === $fileList["mimeType"])) {
                $binaryContent = $this->downloadFile($driveService, $fileList);
                file_put_contents($path . $fileList["id"] . "." . $fileList["fileExtension"], $binaryContent);
                array_push($files, array(
                    "id" => $fileList["id"],
                    "name" => $fileList["id"] . "." . $fileList["fileExtension"],
                    "title" => $fileList["title"]
                ));
            }
        }

        return $files;
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
     * @throws ConnectorConfigException
     */
    public function export(array $credentials, array $parameters) {
        if ((count($credentials) == 0) ||
            (!isset($credentials["api_keys"])) ||
            (null === $credentials["api_keys"]) ||
            (!is_array($credentials["api_keys"]))) {
            throw new ConnectorConfigException("api_keys set is empty", 600);
        }

        if (!array_key_exists('client', $credentials["api_keys"])) {
            throw new ConnectorConfigException("'client' parameter is required", 601);
        } else if ((null === $credentials["api_keys"]["client"]) || (empty($credentials["api_keys"]["client"]))) {
            throw new ConnectorConfigException("'client' parameter is empty", 602);
        }

        if (!array_key_exists('secret', $credentials["api_keys"])) {
            throw new ConnectorConfigException("'secret' parameter is required", 603);
        } else if ((null === $credentials["api_keys"]["secret"]) || (empty($credentials["api_keys"]["secret"]))) {
            throw new ConnectorConfigException("'secret' parameter is empty", 604);
        }

        if ((!isset($credentials["auth_keys"])) ||
            (null === $credentials["auth_keys"]) ||
            (!is_array($credentials["auth_keys"]))) {
            throw new ConnectorConfigException("auth_keys set is empty", 605);
        }

        if (!array_key_exists('access_token', $credentials["auth_keys"])) {
            throw new ConnectorConfigException("'access_token' parameter is required", 606);
        } else if ((null === $credentials["auth_keys"]["access_token"]) || (empty($credentials["auth_keys"]["access_token"]))) {
            throw new ConnectorConfigException("'access_token' parameter is empty", 607);
        }

        if (!array_key_exists('token_type', $credentials["auth_keys"])) {
            throw new ConnectorConfigException("'token_type' parameter is required", 608);
        } else if ((null === $credentials["auth_keys"]["token_type"]) || (empty($credentials["auth_keys"]["token_type"]))) {
            throw new ConnectorConfigException("'token_type' parameter is empty", 609);
        }

        if (!array_key_exists('expires_in', $credentials["auth_keys"])) {
            throw new ConnectorConfigException("'expires_in' parameter is required", 610);
        } else if ((null === $credentials["auth_keys"]["expires_in"]) || (empty($credentials["auth_keys"]["expires_in"]))) {
            throw new ConnectorConfigException("'expires_in' parameter is empty", 611);
        }

        if (!array_key_exists('created', $credentials["auth_keys"])) {
            throw new ConnectorConfigException("'created' parameter is required", 612);
        } else if ((null === $credentials["auth_keys"]["created"]) || (empty($credentials["auth_keys"]["created"]))) {
            throw new ConnectorConfigException("'created' parameter is empty", 613);
        }

        if (!array_key_exists('refresh_token', $credentials["auth_keys"])) {
            throw new ConnectorConfigException("'refresh_token' parameter is required", 614);
        } else if ((null === $credentials["auth_keys"]["refresh_token"]) || (empty($credentials["auth_keys"]["refresh_token"]))) {
            throw new ConnectorConfigException("'refresh_token' parameter is empty", 615);
        }

        if (count($parameters) == 0) {
            throw new ConnectorConfigException("parameters set is empty", 619);
        }

        if (!array_key_exists('userId', $parameters)) {
            throw new ConnectorConfigException("'userId' parameter is required", 620);
        } else if ((null === $parameters["userId"]) || (empty($parameters["userId"]))) {
            throw new ConnectorConfigException("'userId' parameter is empty", 621);
        }

        if (!array_key_exists('content', $parameters)) {
            throw new ConnectorConfigException("'content' parameter is required", 622);
        } else if ((null === $parameters["content"]) || (empty($parameters["content"]))) {
            throw new ConnectorConfigException("'content' parameter is empty", 623);
        }

        $client = new \Google_Client();
        $client->setAccessToken(json_encode($credentials["auth_keys"]));

        if ($client->isAccessTokenExpired()) {
            try {
                $client->setClientId($credentials["api_keys"]["client"]);
                $client->setClientSecret($credentials["api_keys"]["secret"]);
                $client->refreshToken($credentials["auth_keys"]["refresh_token"]);
            } catch(\Exception $e) {
                SocialNetworks::generateErrorResponse($e->getMessage(), 500);
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
        $plusDomainService = new \Google_Service_PlusDomains($client);

        $activity = $plusDomainService->activities->insert($parameters["userId"], $postBody);
        $object = $activity->getObject();
        $user = $activity->getActor();

        $exportDto = new ExportDTO($activity->getPublished(), $object["content"], $object["url"],
                                    $user["id"], $user["displayName"], $user["url"]);

        return $exportDto;
    }

    /**
     * Authentication service from google sign in request
     * @param array $credentials
     * @return array
     * @throws ConnectorConfigException
     * @throws MalformedUrlException
     */
    public function authorize(array $credentials)
    {
        if (count($credentials) == 0) {
            throw new ConnectorConfigException("credentials set is empty", 600);
        }

        if (!array_key_exists('client', $credentials)) {
            throw new ConnectorConfigException("'client' parameter is required", 601);
        } else if ((null === $credentials["client"]) || (empty($credentials["client"]))) {
            throw new ConnectorConfigException("'client' parameter is empty", 602);
        }

        if (!array_key_exists('secret', $credentials)) {
            throw new ConnectorConfigException("'secret' parameter is required", 603);
        } else if ((null === $credentials["secret"]) || (empty($credentials["secret"]))) {
            throw new ConnectorConfigException("'secret' parameter is empty", 604);
        }

        if (!array_key_exists('secret', $credentials)) {
            throw new ConnectorConfigException("'secret' parameter is required", 603);
        } else if ((null === $credentials["secret"]) || (empty($credentials["secret"]))) {
            throw new ConnectorConfigException("'secret' parameter is empty", 604);
        }

        if (!array_key_exists('code', $credentials)) {
            throw new ConnectorConfigException("'code' parameter is required", 616);
        } else if ((null === $credentials["code"]) || (empty($credentials["code"]))) {
            throw new ConnectorConfigException("'code' parameter is empty", 617);
        }

        if ((!array_key_exists('redirectUrl', $credentials)) ||
            (null === $credentials["redirectUrl"]) ||
            (empty($credentials["redirectUrl"]))) {
            throw new ConnectorConfigException("'redirectUrl' parameter is required", 624);
        } else {
            if (!$this->wellFormedUrl($credentials["redirectUrl"])) {
                throw new MalformedUrlException("'redirectUrl' is malformed", 600);
            }
        }

        $client = new \Google_Client();
        $client->setClientId($credentials["client"]);
        $client->setClientSecret($credentials["secret"]);
        $client->setRedirectUri($credentials["redirectUrl"]);

        $client->authenticate($credentials["code"]);

        $googleCredentials = json_decode($client->getAccessToken(), true);

        $oauthService = new \Google_Service_Oauth2($client);
        $profile = $oauthService->userinfo_v2_me->get();

        unset($googleCredentials["id_token"]);

        $profileDto = new ProfileDTO($profile->getId(), $profile->getGivenName()." ".$profile->getFamilyName(),
                                        $profile->getEmail(), $profile->getPicture());

        $googleCredentials["user"] = $profileDto;

        return $googleCredentials;
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