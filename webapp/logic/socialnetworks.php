<?php
/**
 * Followers count service from Social Networks
 * @Author Fran López <ll@bloombees.com>
 * @version 1.0
 */
use CloudFramework\Service\SocialNetworks\SocialNetworks;

/** @var ADNBP $this */
$this->loadClass('socialnetworks/src/SocialNetworksAutoloader');
$sc = SocialNetworks::getInstance();
$google = (array_key_exists("google_form_credentials", $_SESSION)) ? $_SESSION["google_form_credentials"] : array();
$twitter = (array_key_exists("twitter_form_credentials", $_SESSION)) ? $_SESSION["twitter_form_credentials"] : array();

function getFromArray($key, array $array = array()) {
    if(array_key_exists($key, $array)) {
        return $array[$key];
    }
    return '';
}

$requestMethod = strtoupper($_SERVER['REQUEST_METHOD']);
if ($requestMethod === 'POST') {
    $postData = json_decode(file_get_contents("php://input"), true);
    if (array_key_exists("social", $postData)) {
        $credentials = $sc->auth($postData["social"], $postData);
        $result = $sc->getFollowers($postData["social"], $credentials);
        SocialNetworks::jsonResponse(array(
            "social" => $postData['social'],
            "followers" => $result,
        ));
    } else {
        SocialNetworks::generateErrorResponse("Need a social to make followers request", 400);
    }

} else {
    if (array_key_exists("googlePlusOAuthCallback", $_REQUEST)) {
        $params = array(
            "client" => $this->getConf("GoogleOauth_CLIENT_ID"),
            "secret" => $this->getConf("GoogleOauth_CLIENT_SECRET"),
            "code" => $_REQUEST["code"],
        );
        $sc->saveInSession("Google", $params);
    } elseif (array_key_exists("twitterOAuthCallback", $_REQUEST)) {
        $params = array(
            "client" => $this->getConf("TwitterOauth_KEY"),
            "secret" => $this->getConf("TwitterOauth_SECRET"),
            "verifier" => $_REQUEST["oauth_verifier"],
            "token" => $_REQUEST["oauth_token"],
        );
        $sc->saveInSession("Twitter", $params);
    }
}