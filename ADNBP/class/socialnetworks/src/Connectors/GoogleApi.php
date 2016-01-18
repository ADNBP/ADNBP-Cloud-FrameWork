<?php
namespace CloudFramework\Service\SocialNetworks\Connectors;

use CloudFramework\Patterns\Singleton;
use CloudFramework\Service\SocialNetworks\Interfaces\SocialNetworkInterface;
use CloudFramework\Service\SocialNetworks\SocialNetworks;
use CloudFramework\Service\SocialNetworks\Dtos\ExportDTO;

/**
 * Class GoogleApi
 * @package CloudFramework\Service\SocialNetworks\Connectors
 * @author Salvador Castro <sc@bloombees.com>
 */
class GoogleApi extends Singleton implements SocialNetworkInterface {

    const ID = 'google';
    public static $auth_keys = array("access_token", "token_type", "expires_in", "id_token", "created", "refresh_token");
    public static $api_keys = array("client", "secret");

    /**
     * Compose Google Api credentials array from session data
     * @param array|null $credentials
     * @return array
     */
    public function getAuth(array $credentials)
    {
        return SocialNetworks::hydrateCredentials(GoogleApi::ID, GoogleApi::$auth_keys,
                                                        GoogleApi::$api_keys, $credentials);
    }

    /**
     * Service that compose url to authorize google api
     * @param array $apiKeys
     * @return string
     */
    public function getAuthUrl(array $apiKeys)
    {
        if (null !== $apiKeys) {
            $_SESSION[GoogleApi::ID . "_apikeys"] = $apiKeys;
        }

        $client = new \Google_Client();
        $client->setAccessType("offline");
        $client->setClientId($apiKeys["client"]);
        $client->setClientSecret($apiKeys["secret"]);
        $client->setRedirectUri(SocialNetworks::generateRequestUrl() . "socialnetworks?googlePlusOAuthCallback");
        //$client->addScope("https://www.googleapis.com/auth/plus.login");
        $client->addScope("https://www.googleapis.com/auth/plus.me");
        $client->addScope("https://www.googleapis.com/auth/drive");
        $client->addScope("https://www.googleapis.com/auth/plus.circles.read");
        $client->addScope("https://www.googleapis.com/auth/plus.stream.write");
        $client->addScope("https://www.googleapis.com/auth/plus.media.upload");

        // Authentication request
        return $client->createAuthUrl();
    }

    /**
     * Service that query to Google Api a followers count
     * @param array $credentials
     * @return int
     */
    public function getFollowers(array $credentials)
    {
        $client = new \Google_Client();
        $client->setAccessToken(json_encode($credentials["auth_keys"]));

        if ($client->isAccessTokenExpired()) {
            try {
                $client->setClientId($credentials["api_keys"]["client"]);
                $client->setClientSecret($credentials["api_keys"]["secret"]);
                $client->setRedirectUri(SocialNetworks::generateRequestUrl() . "socialnetworks?googlePlusOAuthCallback");
                //$client->addScope("https://www.googleapis.com/auth/plus.login");
                $client->addScope("https://www.googleapis.com/auth/plus.me");
                $client->addScope("https://www.googleapis.com/auth/drive");
                $client->addScope("https://www.googleapis.com/auth/plus.circles.read");
                $client->addScope("https://www.googleapis.com/auth/plus.stream.write");
                $client->addScope("https://www.googleapis.com/auth/plus.media.upload");
                $client->refreshToken($credentials["auth_keys"]["refresh_token"]);
            } catch(\Exception $e) {
                SocialNetworks::generateErrorResponse($e->getMessage(), 500);
            }
        }

        $plusService = new \Google_Service_Plus($client);
        $peopleList = $plusService->people->listPeople("me", "visible", array("fields" => "etag,title,totalItems"));
        return $peopleList->getTotalItems();
    }

    /**
     * Service that query to Google Api Drive service for images
     * @param array $credentials
     * @return array
     */
    public function import(array $credentials)
    {
        $client = new \Google_Client();
        $client->setAccessToken(json_encode($credentials["auth_keys"]));

        if ($client->isAccessTokenExpired()) {
            try {
                $client->setClientId($credentials["api_keys"]["client"]);
                $client->setClientSecret($credentials["api_keys"]["secret"]);
                $client->setRedirectUri(SocialNetworks::generateRequestUrl() . "socialnetworks?googlePlusOAuthCallback");
                //$client->addScope("https://www.googleapis.com/auth/plus.login");
                $client->addScope("https://www.googleapis.com/auth/plus.me");
                $client->addScope("https://www.googleapis.com/auth/drive");
                $client->addScope("https://www.googleapis.com/auth/plus.circles.read");
                $client->addScope("https://www.googleapis.com/auth/plus.stream.write");
                $client->addScope("https://www.googleapis.com/auth/plus.media.upload");
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
            array_push($files, array(
                "title" => $fileList["title"],
                "mimetype" => $fileList["mimeType"],
                "content" => base64_encode($this->downloadFile($driveService, $fileList))
            ));
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
     * @param $content Text of the stream
     * @param $link External link
     * @param $logo Logo
     * @param $circleId Google circle where the stream will be published in
     * @param $personId Google + user whose domain the stream will be published in
     * @param $userId User whose google domain the stream will be published in
     * $personId and $circleId are excluding
     * @return ExportDTO
     */
    public function export(array $credentials, $content, $link = null, $logo = null,
                                    $circleId = null, $personId = null, $userId = 'me') {
        $client = new \Google_Client();
        $client->setAccessToken(json_encode($credentials["auth_keys"]));

        if ($client->isAccessTokenExpired()) {
            try {
                $client->setClientId($credentials["api_keys"]["client"]);
                $client->setClientSecret($credentials["api_keys"]["secret"]);
                $client->setRedirectUri(SocialNetworks::generateRequestUrl() . "socialnetworks?googlePlusOAuthCallback");
                //$client->addScope("https://www.googleapis.com/auth/plus.login");
                $client->addScope("https://www.googleapis.com/auth/plus.me");
                $client->addScope("https://www.googleapis.com/auth/drive");
                $client->addScope("https://www.googleapis.com/auth/plus.circles.read");
                $client->addScope("https://www.googleapis.com/auth/plus.stream.write");
                $client->addScope("https://www.googleapis.com/auth/plus.media.upload");
                $client->refreshToken($credentials["auth_keys"]["refresh_token"]);
            } catch(\Exception $e) {
                SocialNetworks::generateErrorResponse($e->getMessage(), 500);
            }
        }

        // Activity
        $postBody = new \Google_Service_PlusDomains_Activity();

        // Activity object
        $object = new \Google_Service_PlusDomains_ActivityObject();
        $object->setOriginalContent($content);

        // Activity attachments
        $attachments = array();

        if (null !== $link) {
            $linkAttachment = new \Google_Service_Plus_ActivityObjectAttachments();
            $linkAttachment->setObjectType("article");
            $linkAttachment->setUrl($link);
            $postBody->setUrl($link);

            $attachments[] = $linkAttachment;
        }

        if (null !== $logo) {
            $logoAttachment = new \Google_Service_Plus_ActivityObjectAttachments();
            $logoAttachment->setObjectType("photo");
            $logoAttachment->setUrl($logo);

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

        if ((null === $circleId) && (null === $personId)) {
            $resource->setType("domain");
        } else if (null !== $circleId) {
            $resource->setType("circle");
            $resource->setId($circleId);
        } else if (null !== $personId) {
            $resource->setType("person");
            $resource->setId($personId);
        }

        $resources = array();
        $resources[] = $resource;

        $access->setItems($resources);

        $postBody->setAccess($access);
        $plusDomainService = new \Google_Service_PlusDomains($client);

        $activity = $plusDomainService->activities->insert($userId, $postBody);
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
     */
    public function authorize(array $credentials)
    {
        $client = new \Google_Client();
        $client->setClientId($credentials['client']);
        $client->setClientSecret($credentials['secret']);
        $client->setRedirectUri(SocialNetworks::generateRequestUrl() . "socialnetworks?googlePlusOAuthCallback");

        $client->authenticate($credentials["code"]);
        return json_decode($client->getAccessToken(), true);
    }
}