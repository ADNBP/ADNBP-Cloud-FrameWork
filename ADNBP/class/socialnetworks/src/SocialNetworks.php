<?php

namespace CloudFramework\Service;

require_once __DIR__ . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . "vendor" . DIRECTORY_SEPARATOR . "autoload.php";
use Abraham\TwitterOAuth\TwitterOAuth;

/**
 * Class SocialNetworks
 * @author Fran López <fl@bloombees.com>
 */
class SocialNetworks
{

    private static $instance;

    public static $google = array("access_token", "token_type", "expires_in", "id_token", "created");
    public static $twitter = array("consumer_key", "consumer_secret", "oauth_access_token", "oauth_access_token_secret");

    public static function getInstance()
    {
        if (null === self::$instance) {
            self::$instance = new SocialNetworks();
        }
        return self::$instance;
    }

    public static function generateRequestUrl()
    {
        $protocol = (array_key_exists("HTTPS", $_SERVER) && $_SERVER["HTTPS"] === 'on') ? 'https' : 'http';
        $domain = $_SERVER['SERVER_NAME'];
        $port = $_SERVER['SERVER_PORT'];
        return "$protocol://$domain:$port/";
    }

    /**
     * Static method that generate an error response from SocialNetwork Service
     * @param string $message
     * @param int $code
     */
    public static function generateErrorResponse($message, $code = 500)
    {
        ob_start();
        header("HTTP/1.0 $code $message");
        ob_end_clean();
        exit;
    }

    /**
     * Service that make a JSON response
     * @param mixed $result
     */
    public static function jsonResponse($result = null)
    {
        $data = json_encode($result, JSON_PRETTY_PRINT);
        ob_start();
        header("Content-type: application/json");
        header("Content-length: " . strlen($data));
        echo $data;
        ob_flush();
        ob_end_clean();
        exit;
    }

    /**
     * Statis method that hydrate credentials for social network required fields
     * @param string $socialNetwork
     * @param array $keys
     * @param array $data
     * @return array
     */
    public static function hydrateCredentials($socialNetwork, $keys, $data)
    {
        $credentials = array();
        if (null !== $data) {
            foreach ($keys as $key) {
                if (array_key_exists($key, $data) && strlen($data[$key]) > 0) {
                    $credentials[$key] = $data[$key];
                }
            }
        }
        if (count($credentials) !== count($keys)) {
            switch (strtoupper($socialNetwork)) {
                case "GOOGLE":
                    SocialNetworks::generateErrorResponse(SocialNetworks::getAuthGoogleApiUrl(), 401);
                    break;
                case "TWITTER":
                    SocialNetworks::generateErrorResponse(SocialNetworks::getAuthTwitterApiUrl(), 401);
                    break;
                case "FACEBOOK":
                case "LINKEDIN":
                case "INSTAGRAM":
                case "PINTEREST":
                default:
                    SocialNetworks::generateErrorResponse(SocialNetworks::generateRequestUrl() . "socialnetworks", 302);
                    break;
            }

        }
        return $credentials;
    }

    /**
     * GOOGLE PLUS API
     */

    /**
     * Compose Google Api credentials array from session data
     * @param array|null $data
     * @return array
     */
    public function getGoogleAuth($data = null)
    {
        return SocialNetworks::hydrateCredentials("google", SocialNetworks::$google, $data);
    }

    /**
     * Service that redirect to CloudFramework Google OAuth url
     */
    public static function getAuthGoogleApiUrl()
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
     * Authentication service from google sign in request
     * @param string $clientId
     * @param string $clientSecret
     * @param string $code
     * @return string
     */
    public function authorizeGoogleCode($clientId, $clientSecret, $code)
    {
        $client = new \Google_Client();
        $client->setClientId($clientId);
        $client->setClientSecret($clientSecret);
        $client->setRedirectUri(SocialNetworks::generateRequestUrl() . "socialnetworks?googlePlusOAuthCallback");

        $client->authenticate($code);
        return $client->getAccessToken();
    }

    /**
     * Method that store in session google api authorized credentials for user
     * @param string $client
     * @param string $secret
     */
    public function saveInSessionGoogleAuth($client, $secret)
    {
        $google = json_decode($this->authorizeGoogleCode($client, $secret, $_REQUEST["code"]), true);
        $_SESSION["google_form_credentials"] = $google;
        header("Location: " . SocialNetworks::generateRequestUrl() . "socialnetworks");
        exit;
    }

    /**
     * Service that query to Google Api a followers count
     * @param array $credentials
     * @return int
     */
    public function getGoogleFollowers(array $credentials)
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
     * TWITTER API
     */

    /**
     * Authenticate twitter api service
     * @param array $credentials
     */
    public function getTwitterAuth(array $credentials = array())
    {
        return SocialNetworks::hydrateCredentials("twitter", SocialNetworks::$twitter, $credentials);
    }

    /**
     * Service that make twitter authorize url
     * @return string
     * @throws \Abraham\TwitterOAuth\TwitterOAuthException
     */
    public static function getAuthTwitterApiUrl() {
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
     * Method that verify de auth token from twitter
     * @param string $client
     * @param string $secret
     * @param string $verify
     * @param string $token
     * @return array
     * @throws \Abraham\TwitterOAuth\TwitterOAuthException
     */
    private function authorizeTwitterCode($client, $secret, $verify, $token) {
        $twitterApi = new TwitterOAuth($client, $secret);
        $params = array(
            "oauth_verifier" => $verify,
            "oauth_token" => $token,
        );

        $response = $twitterApi->oauth("oauth/access_token", $params);

        $token = array(
            "oauth_access_token" => $response["oauth_token"],
            "oauth_access_token_secret" => $response["oauth_token_secret"],
        );
        return $token;
    }

    /**
     * Method that store in session google api authorized credentials for user
     * @param string $client
     * @param string $secret
     */
    public function saveInSessionTwitterAuth($client, $secret)
    {
        try {
            $twitter = $this->authorizeTwitterCode($client, $secret, $_REQUEST["oauth_verifier"], $_REQUEST["oauth_token"]);
            $_SESSION["twitter_form_credentials"] = $twitter;
            header("Location: " . SocialNetworks::generateRequestUrl() . "socialnetworks");
            exit;
        } catch(\Exception $e) {
            SocialNetworks::generateErrorResponse($e->getMessage(), 500);
        }

    }

    /**
     * Service that connect to Twitter Api and extract a follower count for authorized user
     * @param array $credentials
     * @return int
     */
    function getTwitterFollower(array $credentials)
    {
        try {
            $twitterApi = new TwitterOAuth($credentials["consumer_key"], $credentials["consumer_secret"], $credentials["oauth_access_token"], $credentials["oauth_access_token_secret"]);
            $response = $twitterApi->get("statuses/user_timeline", array("count" => 1));
            return $response[0]->user->followers_count;
        } catch(\Exception $e) {
            SocialNetworks::generateErrorResponse($e->getMessage(), 500);
        }
    }
}
