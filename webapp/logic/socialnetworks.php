<?php
/**
 * Followers count service from Social Networks
 * @Author Fran López <ll@bloombees.com>
 * @version 1.0
 */
use CloudFramework\Service\SocialNetworks;

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
        $result = null;
        switch(strtoupper($postData['social'])) {
            case "GOOGLE":
                $credentials = $sc->getGoogleAuth($postData);
                $result = $sc->getGoogleFollowers($credentials);
                break;
            case "TWITTER":
                $credentials = $sc->getTwitterAuth($postData);
                $result = $sc->getTwitterFollower($credentials);
                break;
            case "FACEBOOK":
            case "LINKEDIN":
            case "PINTEREST":
            case "INSTAGRAM":
            default: SocialNetworks::generateErrorResponse("Social Network not implemented", 501);
        }
        SocialNetworks::jsonResponse(array(
            "social" => $postData['social'],
            "followers" => $result,
        ));
    } else {
        SocialNetworks::generateErrorResponse("Need a social to make followers request", 400);
    }

} else {
    if (array_key_exists("googlePlusOAuthCallback", $_REQUEST)) {
        $sc->saveInSessionGoogleAuth($this->getConf("GoogleOauth_CLIENT_ID"), $this->getConf("GoogleOauth_CLIENT_SECRET"));
    } elseif (array_key_exists("twitterOAuthCallback", $_REQUEST)) {
        $sc->saveInSessionTwitterAuth($this->getConf("TwitterOauth_KEY"), $this->getConf("TwitterOauth_SECRET"));
    }
}