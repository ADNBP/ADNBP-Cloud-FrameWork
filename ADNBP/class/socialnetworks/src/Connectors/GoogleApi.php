<?php
namespace CloudFramework\Service\SocialNetworks\Connectors;

use CloudFramework\Service\SocialNetworks\Interfaces\Singleton;
use CloudFramework\Service\SocialNetworks\Interfaces\SocialNetworkInterface;
use CloudFramework\Service\SocialNetworks\SocialNetworks;

/**
 * Class GoogleApi
 * @package CloudFramework\Service\SocialNetworks\Connectors
 */
class GoogleApi extends Singleton implements SocialNetworkInterface {

    const ID = 'google';
    public static $auth_keys = array("access_token", "token_type", "expires_in", "id_token", "created");

    /**
     * Compose Google Api credentials array from session data
     * @param array|null $credentials
     * @return array
     */
    public function getAuth(array $credentials)
    {
        return SocialNetworks::hydrateCredentials(GoogleApi::ID, GoogleApi::$auth_keys, $credentials);
    }

    /**
     * Service that compose url to authorize google api
     * @return string
     */
    public function getAuthUrl()
    {
        $client = new \Google_Client();
        //TODO change to dynamic api keys
        $client->setClientId('461069694507-sbh2jn892vj1c4rjhqun7j1qqccu29k8.apps.googleusercontent.com');
        $client->setClientSecret('5B-v4m5acZlxfxEEEEDgXjeM');
        $client->setRedirectUri(SocialNetworks::generateRequestUrl() . "socialnetworks?googlePlusOAuthCallback");
        $client->addScope("https://www.googleapis.com/auth/plus.login");
        $client->addScope("https://www.googleapis.com/auth/plus.me");

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
        $client->setAccessToken(json_encode($credentials));

        if ($client->isAccessTokenExpired()) {
            // TODO auto regenerate user token
            SocialNetworks::generateErrorResponse(SocialNetworks::getAuthGoogleApiUrl(), 401);
        }

        $plusService = new \Google_Service_Plus($client);
        $peopleList = $plusService->people->listPeople("me", "visible", array("fields" => "etag,title,totalItems"));
        return $peopleList->getTotalItems();
    }

    /**
     * Authentication service from google sign in request
     * @param array $credentials
     * @return string
     */
    public function authorize(array $credentials)
    {
        $client = new \Google_Client();
        $client->setClientId($credentials["client"]);
        $client->setClientSecret($credentials["secret"]);
        $client->setRedirectUri(SocialNetworks::generateRequestUrl() . "socialnetworks?googlePlusOAuthCallback");

        $client->authenticate($credentials["code"]);
        return json_decode($client->getAccessToken(), true);
    }
}