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
     * @param string $redirectUrl
     * @return array
     */
    public function getAuth(array $credentials, $redirectUrl)
    {
        return SocialNetworks::hydrateCredentials(GoogleApi::ID, GoogleApi::$auth_keys,
                                                        GoogleApi::$api_keys, $credentials, $redirectUrl);
    }

    /**
     * Service that compose url to authorize google api
     * @param array $apiKeys
     * @param string $redirectUrl
     * @return string
     */
    public function getAuthUrl(array $apiKeys, $redirectUrl)
    {
        if (null !== $apiKeys) {
            $_SESSION[GoogleApi::ID . "_apikeys"] = $apiKeys;
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

        // Authentication request
        return $client->createAuthUrl();
    }

    /**
     * Service that query to Google Api Drive service for images
     * @param array $credentials
     * @param string $path path where files imported will be saved
     * @return array
     */
    public function import(array $credentials, $path)
    {
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
            $binaryContent = $this->downloadFile($driveService, $fileList);
            file_put_contents($path.$fileList["id"].".".$fileList["fileExtension"], $binaryContent);
            array_push($files, array(
                "id" => $fileList["id"],
                "name" => $fileList["id"].".".$fileList["fileExtension"],
                "title" => $fileList["title"]
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
     * @param array $parameters
     * *      "userId"    => User whose google domain the stream will be published in
     *      "content"   => Text of the comment
     *      "link"      => External link
     *      "logo"      => Logo
     *      "circleId"  => Google circle where the stream will be published in
     *      "personId"  => Google + user whose domain the stream will be published in
     *      ($circleId are excluding)
     *
     * @return ExportDTO
     */
    public function export(array $credentials, array $parameters) {
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