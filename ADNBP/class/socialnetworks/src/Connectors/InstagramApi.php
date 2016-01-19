<?php
namespace CloudFramework\Service\SocialNetworks\Connectors;

use CloudFramework\Patterns\Singleton;
use CloudFramework\Service\SocialNetworks\Interfaces\SocialNetworkInterface;
use CloudFramework\Service\SocialNetworks\SocialNetworks;
use CloudFramework\Service\SocialNetworks\Dtos\ExportDTO;

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
    public static $auth_keys = array("access_token", "token_type", "expires_in", "id_token", "created", "refresh_token");
    public static $api_keys = array("client", "secret");

    /**
     * Compose Google Api credentials array from session data
     * @param array|null $credentials
     * @return array
     */
    public function getAuth(array $credentials)
    {
        return SocialNetworks::hydrateCredentials(InstagramApi::ID, InstagramApi::$auth_keys,
            InstagramApi::$api_keys, $credentials);
    }

    /**
     * Service that compose url to authorize instagram api
     * @param array $apiKeys
     * @return string
     */
    public function getAuthUrl(array $apiKeys)
    {
        if (null !== $apiKeys) {
            $_SESSION[InstagramApi::ID . "_apikeys"] = $apiKeys;
        }

        $urlOauth = InstagramAPI::INSTAGRAM_OAUTH_URL.
                                    "?client_id=".$apiKeys["client"].
                                    "&redirect_uri=".SocialNetworks::generateRequestUrl() . "socialnetworks?instagramOAuthCallback".
                                    "&response_type=code".
                                    "&scope=basic+public_content+comments";

        // Authentication request
        return $urlOauth;
    }

    /**
     * Service that query to Instagram Api a followers count
     * @param array $credentials
     * @return int
     */
    public function getFollowers(array $credentials)
    {
        $url = InstagramApi::INSTAGRAM_API_USERS_URL."self/?access_token=".$credentials["access_token"];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $data = curl_exec($ch);
        curl_close($ch);

        return $data["data"]["counts"]["followed_by"];
    }

    /**
     * Service that query to Instagram Api Drive service for images
     * @param array $credentials
     * @return array
     */
    public function import(array $credentials)
    {
        $url = InstagramApi::INSTAGRAM_API_USERS_URL."self/media/recent/?access_token=".$credentials["access_token"];
        $pagination = true;
        $files = array();

        while ($pagination) {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            $data = curl_exec($ch);
            curl_close($ch);

            foreach ($data["data"] as $key => $media) {
                if ("image" === $media["type"]) {
                    array_push($files, array(
                        "title" => $data["caption"]["text"],
                        "link" => $data["images"]["standard_resolution"]["url"]
                    ));
                }
            }

            if (!isset($data["pagination"])) {
                $pagination = false;
            } else {
                $url = $data["pagination"]["next_url"];
            }
        }

        return $files;
    }

    /**
     * Service that publish a comment in an Instagram media
     * @param array $credentials
     * @param $content Text of the comment
     * GOOGLE
     * @param $link External link
     * @param $logo Logo
     * @param $circleId Google circle where the stream will be published in
     * @param $personId Google + user whose domain the stream will be published in
     * @param $userId User whose google domain the stream will be published in
     * $personId and $circleId are excluding
     * INSTAGRAM
     * @param $mediaId Instagram media's ID
     *
     * @return ExportDTO
     */
    public function export(array $credentials, $content, $link = null, $logo = null,
                                    $circleId = null, $personId = null, $mediaId, $userId = 'me') {
        $url = InstagramApi::INSTAGRAM_API_MEDIA_URL.$mediaId."/comments/?access_token=".$credentials["access_token"];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $data = curl_exec($ch);
        curl_close($ch);

        if ($data["meta"]["code"] != 200) {
            SocialNetworks::generateErrorResponse("Error making comments on an Instagram media", 500);
        }

        $today = new \DateTime();
        $exportDto = new ExportDTO($today->format("d/m/Y H:i:s"), $content, null, null, null, null);

        return $exportDto;
    }

    /**
     * Authentication service from instagram sign in request
     * @param array $credentials
     * @return array
     */
    public function authorize(array $credentials)
    {
        if (null === $credentials["code"]) {
            SocialNetworks::generateErrorResponse($credentials["error_description"], 500);
        }

        $url = InstagramApi::INSTAGRAM_OAUTH_ACCESS_TOKEN_URL.
            "?client_id=".$credentials["client"].
            "&client_secret=".$credentials["secret"].
            "&grant_type=authorization_code".
            "&redirect_uri=".SocialNetworks::generateRequestUrl() . "socialnetworks?instagramOAuthCallback".
            "&code".$credentials["code"];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $data = curl_exec($ch);
        curl_close($ch);

        /**
         * Returned data format
         *  {
                    "access_token": "fb2e77d.47a0479900504cb3ab4a1f626d174d2d",
                    "user": {
                        "id": "1574083",
                        "username": "snoopdogg",
                        "full_name": "Snoop Dogg",
                        "profile_picture": "..."
                }
            }
         **/

        return json_decode($data, true);
    }
}