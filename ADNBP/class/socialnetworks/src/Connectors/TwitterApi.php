<?php
namespace CloudFramework\Service\SocialNetworks\Connectors;

use Abraham\TwitterOAuth\TwitterOAuth;
use CloudFramework\Service\SocialNetworks\Interfaces\Singleton;
use CloudFramework\Service\SocialNetworks\Interfaces\SocialNetworkInterface;
use CloudFramework\Service\SocialNetworks\SocialNetworks;

class TwitterApi extends Singleton implements SocialNetworkInterface {

    const ID = 'twitter';

    public static $auth_keys = array("consumer_key", "consumer_secret", "oauth_access_token", "oauth_access_token_secret");

    /**
     * Authenticate twitter api service
     * @param array $credentials
     */
    function getAuth(array $credentials)
    {
        return SocialNetworks::hydrateCredentials(TwitterApi::ID, TwitterApi::$auth_keys, $credentials);
    }

    /**
     * Service that make twitter authorize url
     * @return string
     * @throws \Abraham\TwitterOAuth\TwitterOAuthException
     */
    function getAuthUrl()
    {
        $params = array(
            'oauth_callback' => '',
        );
        //TODO change to dynamic api keys
        $twitterApi = new TwitterOAuth("wa9yeKg4HnsHUSLUItdFv8CfS", "msckuLH8Ee3dVJcayuaYIKHt47aB6Z2EB6mw3uRrnnIDFv7NqF");
        $response = $twitterApi->oauth('oauth/request_token', $params);

        $parameters = array(
            "oauth_token" => $response["oauth_token"],
            "oauth_callback" => SocialNetworks::generateRequestUrl() . "socialnetworks?twitterOAuthCallback"
        );

        return "https://api.twitter.com/oauth/authorize?" . http_build_query($parameters);
    }

    /**
     * Service that connect to Twitter Api and extract a follower count for authorized user
     * @param array $credentials
     * @return int
     */
    function getFollowers(array $credentials)
    {
        try {
            $twitterApi = new TwitterOAuth($credentials["consumer_key"], $credentials["consumer_secret"], $credentials["oauth_access_token"], $credentials["oauth_access_token_secret"]);
            $response = $twitterApi->get("statuses/user_timeline", array("count" => 1));
            return $response[0]->user->followers_count;
        } catch(\Exception $e) {
            SocialNetworks::generateErrorResponse($e->getMessage(), 500);
        }
    }

    /**
     * Method that verify de auth token from twitter
     * @param array $credentials
     * @return array
     * @throws \Abraham\TwitterOAuth\TwitterOAuthException
     */
    function authorize(array $credentials)
    {
        $token = array();
        try {
            $twitterApi = new TwitterOAuth($credentials["client"], $credentials["secret"]);
            $params = array(
                "oauth_verifier" => $credentials["verifier"],
                "oauth_token" => $credentials["token"],
            );

            $response = $twitterApi->oauth("oauth/access_token", $params);

            $token = array(
                "oauth_access_token" => $response["oauth_token"],
                "oauth_access_token_secret" => $response["oauth_token_secret"],
            );
        } catch(\Exception $e) {
            SocialNetworks::generateErrorResponse($e->getMessage(), 500);
        }
        return $token;
    }
}