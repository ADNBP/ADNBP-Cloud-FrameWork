<?php
namespace CloudFramework\Service\SocialNetworks\Connectors;

use CloudFramework\Patterns\Singleton;
use CloudFramework\Service\SocialNetworks\Exceptions\AuthenticationException;
use CloudFramework\Service\SocialNetworks\Exceptions\ConnectorConfigException;
use CloudFramework\Service\SocialNetworks\Exceptions\ExportException;
use CloudFramework\Service\SocialNetworks\Exceptions\ImportException;
use CloudFramework\Service\SocialNetworks\Exceptions\MalformedUrlException;
use CloudFramework\Service\SocialNetworks\Exceptions\ProfileInfoException;
use CloudFramework\Service\SocialNetworks\SocialNetworks;
use CloudFramework\Service\SocialNetworks\Dtos\ExportDTO;
use CloudFramework\Service\SocialNetworks\Dtos\ProfileDTO;

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
    public static $auth_keys = array("access_token");
    public static $api_keys = array("client", "secret");

    /**
     * Compose Google Api credentials array from session data
     * @param array|null $credentials
     * @param string $redirectUrl
     * @throws ConnectorConfigException
     * @throws MalformedUrlException
     * @return array
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

        return SocialNetworks::hydrateCredentials(InstagramApi::ID, InstagramApi::$auth_keys,
            InstagramApi::$api_keys, $credentials, $redirectUrl);
    }

    /**
     * Service that compose url to authorize instagram api
     * @param array $apiKeys
     * @param string $redirectUrl
     * @return string
     * @throws ConnectorConfigException
     * @throws MalformedUrlException
     */
    public function getAuthUrl(array $apiKeys, $redirectUrl)
    {
        if (count($apiKeys) > 0) {
            $_SESSION[InstagramApi::ID . "_apikeys"] = $apiKeys;
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

        $urlOauth = InstagramAPI::INSTAGRAM_OAUTH_URL.
                                    "?client_id=".$apiKeys["client"].
                                    "&redirect_uri=".$redirectUrl.
                                    "&response_type=code".
                                    "&scope=basic+public_content+comments";

        if ((null === $urlOauth) || (empty($urlOauth))) {
            throw new ConnectorConfigException("'authUrl' parameter is required", 624);
        } else {
            if (!$this->wellFormedUrl($urlOauth)) {
                throw new MalformedUrlException("'authUrl' is malformed", 600);
            }
        }

        // Authentication request
        return $urlOauth;
    }

    /**
     * Service that query to Instagram Api to get user profile
     * @param array $credentials
     * @return ProfileDTO
     * @throws AuthenticationException
     * @throws ConnectorConfigException
     * @throws ProfileInfoException
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

        $url = InstagramApi::INSTAGRAM_API_USERS_URL . "self/?access_token=" . $credentials["auth_keys"]["access_token"];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $data = curl_exec($ch);
        curl_close($ch);

        $data = json_decode($data, true);

        if ((!isset($data["data"])) || (!isset($data["data"]["id"])) || (!isset($data["data"]["full_name"])) ||
            (!isset($data["data"]["profile_picture"]))) {
            throw new ProfileInfoException("Error fetching user profile info: missing fields", 601);
        }

        // Instagram doesn't return the user's e-mail :(
        $profileDto = new ProfileDTO($data["data"]["id"], $data["data"]["full_name"],
                                            null, $data["data"]["profile_picture"]);

        return $profileDto;
    }

    /**
     * Service that query to Instagram Api Drive service for images
     * @param array $credentials
     * @param string $path
     * @return array
     * @throws ConnectorConfigException
     * @throws ImportException
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

        if ((null === $path) || (empty($path))) {
            throw new ConnectorConfigException("'path' parameter is required", 618);
        }

        $url = InstagramApi::INSTAGRAM_API_USERS_URL."self/media/recent/?access_token=".$credentials["auth_keys"]["access_token"];
        $pagination = true;
        $files = array();

        while ($pagination) {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            $data = curl_exec($ch);
            curl_close($ch);

            $data = json_decode($data);

            if ((!property_exists($data->data)) || (null === $data->data)) {
                throw new ImportException("Error importing files", 601);
            }

            foreach ($data->data as $key => $media) {
                if ("image" === $media->type) {
                    // Save file
                    $binaryContent = file_get_contents($media->images->standard_resolution->url);
                    $fileExtension = substr(strrchr($media->images->standard_resolution->url, "."), 1);
                    file_put_contents($path.$media->id.".".$fileExtension, $binaryContent);
                    array_push($files, array(
                        "id" => $media->id,
                        "name" => $media->id.".".$fileExtension,
                        "title" => $media->caption->text,
                    ));
                }
            }

            if (!isset($data->pagination->next_url)) {
                $pagination = false;
            } else {
                $url = $data->pagination->next_url;
            }
        }

        return $files;
    }


    /**
     * Service that publish a comment in an Instagram media
     * @param array $credentials
     * @param array $parameters
     *      "content" => Text of the comment
     *      "mediaId" => Instagram media's ID
     *
     * @return ExportDTO
     * @throws ConnectorConfigException
     * @throws ExportException
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

        if (count($parameters) == 0) {
            throw new ConnectorConfigException("parameters set is empty", 619);
        }

        if (!array_key_exists('mediaId', $parameters)) {
            throw new ConnectorConfigException("'mediaId' parameter is required", 625);
        } else if ((null === $parameters["mediaId"]) || (empty($parameters["mediaId"]))) {
            throw new ConnectorConfigException("'mediaId' parameter is empty", 626);
        }

        if (!array_key_exists('content', $parameters)) {
            throw new ConnectorConfigException("'content' parameter is required", 622);
        } else if ((null === $parameters["content"]) || (empty($parameters["content"]))) {
            throw new ConnectorConfigException("'content' parameter is empty", 623);
        }

        $url = InstagramApi::INSTAGRAM_API_MEDIA_URL.$parameters["mediaId"]."/comments";

        $fields = "access_token=".$credentials["auth_keys"]["access_token"].
                    "&text=".$parameters["content"];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $data = curl_exec($ch);
        curl_close($ch);

        $data = json_decode($data);

        if ($data->meta->code != 200) {
            throw new ExportException("Error exporting files: Error making comments on an Instagram media", 601);
        }

        $today = new \DateTime();
        $exportDto = new ExportDTO($today->format("Y-m-d\TH:i:s\Z"), $data->data->text, null,
                                        $data->data->from->id, $data->data->from->full_name, $data->data->from->profile_picture);

        return $exportDto;
    }

    /**
     * Authentication service from instagram sign in request
     * @param array $credentials
     * @return array
     * @throws AuthenticationException
     * @throws ConnectorConfigException
     * @throws MalformedUrlException
     * @throws ProfileInfoException
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

        $fields = "client_id=".$credentials["client"].
                    "&client_secret=".$credentials["secret"].
                    "&grant_type=authorization_code".
                    "&code=".$credentials["code"].
                    "&redirect_uri=".$credentials["redirectUrl"];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, InstagramApi::INSTAGRAM_OAUTH_ACCESS_TOKEN_URL);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $data = curl_exec($ch);
        curl_close($ch);

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

        $instagramCredentials = json_decode($data, true);

        if (!isset($instagramCredentials["access_token"])) {
            throw new AuthenticationException("Error fetching OAuth2 access token, client is invalid", 601);
        } else if ((!isset($instagramCredentials["user"])) || (!isset($instagramCredentials["user"]["id"])) ||
                   (!isset($instagramCredentials["user"]["full_name"])) ||
                    (!isset($instagramCredentials["user"]["profile_picture"]))) {
            throw new ProfileInfoException("Error fetching user profile info", 601);
        }

        // Instagram doesn't return the user's e-mail :(
        $profileDto = new ProfileDTO($instagramCredentials["user"]["id"], $instagramCredentials["user"]["full_name"],
                                        null, $instagramCredentials["user"]["profile_picture"]);

        $instagramCredentials["user"] = $profileDto;

        return $instagramCredentials;
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