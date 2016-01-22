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
    public static $auth_keys = array("access_token");
    public static $api_keys = array("client", "secret");

    /**
     * Compose Google Api credentials array from session data
     * @param array|null $credentials
     * @param string $redirectUrl
     * @return array
     */
    public function getAuth(array $credentials, $redirectUrl)
    {
        return SocialNetworks::hydrateCredentials(InstagramApi::ID, InstagramApi::$auth_keys,
            InstagramApi::$api_keys, $credentials, $redirectUrl);
    }

    /**
     * Service that compose url to authorize instagram api
     * @param array $apiKeys
     * @param string $redirectUrl
     * @return string
     */
    public function getAuthUrl(array $apiKeys, $redirectUrl)
    {
        if (null !== $apiKeys) {
            $_SESSION[InstagramApi::ID . "_apikeys"] = $apiKeys;
        }

        $urlOauth = InstagramAPI::INSTAGRAM_OAUTH_URL.
                                    "?client_id=".$apiKeys["client"].
                                    "&redirect_uri=".$redirectUrl.
                                    "&response_type=code".
                                    "&scope=basic+public_content+comments";

        // Authentication request
        return $urlOauth;
    }

    /**
     * Service that query to Instagram Api Drive service for images
     * @param array $credentials
     * @param string $path
     * @return array
     */
    public function import(array $credentials, $path)
    {
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
            foreach ($data->data as $key => $media) {
                if ("image" === $media->type) {
                    // Save file
                    $binaryContent = file_get_contents($media->images->standard_resolution->url);
                    $fileExtension = substr(strrchr($media->images->standard_resolution->url, "."), 1);
                    file_put_contents($path.$media->id.".".$fileExtension, $binaryContent);
                    array_push($files, array(
                        "id" => $media->id,
                        "name" => $media->id.".".$fileExtension,
                        "title" => $media->caption,
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
     */
    public function export(array $credentials, array $parameters) {
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
            SocialNetworks::generateErrorResponse("Error making comments on an Instagram media", 500);
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
     */
    public function authorize(array $credentials)
    {
        if (null === $credentials["code"]) {
            SocialNetworks::generateErrorResponse($credentials["error_description"], 500);
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

        return json_decode($data, true);
    }
}