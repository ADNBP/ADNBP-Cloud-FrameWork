<?php
namespace CloudFramework\Service\SocialNetworks\Connectors;

use CloudFramework\Patterns\Singleton;
use CloudFramework\Service\SocialNetworks\Interfaces\SocialNetworkInterface;
use CloudFramework\Service\SocialNetworks\SocialNetworks;
use Facebook\Facebook;

class FacebookApi extends Singleton implements SocialNetworkInterface
{
    const ID = 'facebook';
    public static $auth_keys = array("apiKey", "secret", "facebook_access_token");

    /**
     * Compose Facebook Api credentials array from session data
     * @param array|null $credentials
     * @return array
     */
    public function getAuth(array $credentials)
    {
        return SocialNetworks::hydrateCredentials(FacebookApi::ID, FacebookApi::$auth_keys, $credentials);
    }

    /**
     * Service that compose url to authorize facebook api
     * @return string
     */
    public function getAuthUrl()
    {
        $facebook = new Facebook(array(
            "app_id" => "679052265503996",
            "app_secret" => "9e1f7ec8df2a40fedf9e1a0cfaedf798",
            'default_graph_version' => 'v2.4',
            'cookie' => true
        ));
        $redirect = $facebook->getRedirectLoginHelper();
        return $redirect->getLoginUrl(SocialNetworks::generateRequestUrl() . "socialnetworks?facebookOAuthCallback",
        array(
            "email", "user_photos", "publish_actions", "read_custom_friendlists", "user_friends"
        ));
    }

    /**
     * Service that query to Facebook Api a followers count
     * @param array $credentials
     * @return int
     */
    public function getFollowers(array $credentials)
    {
        $facebook = new Facebook(array(
            "app_id" => $credentials["apiKey"],
            "app_secret" => $credentials["secret"],
            'default_graph_version' => 'v2.4'
        ));
        $response = $facebook->get('/me/friends', $credentials["facebook_access_token"])->getDecodedBody();
        return $response["summary"]["total_count"];
    }

    /**
     * Authentication service from facebook sign in request
     * @param array $credentials
     * @return array
     */
    public function authorize(array $credentials)
    {
        try {
            $facebook = new Facebook(array(
                "app_id" => $credentials["client"],
                "app_secret" => $credentials["secret"],
                'default_graph_version' => 'v2.4'
            ));
            $helper = $facebook->getRedirectLoginHelper();
            $accessToken = $helper->getAccessToken();
            if (empty($accessToken)) {
                throw new \Exception("Error taking access token from Facebook Api", 500);
            }
        } catch(\Exception $e) {
            SocialNetworks::generateErrorResponse($e->getMessage(), 500);
        }

        return array(
            "facebook_access_token" => $accessToken->getValue(),
        );
    }
}