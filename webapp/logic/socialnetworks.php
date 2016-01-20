<?php
/**
 * Followers count service from Social Networks
 * @Author Fran López <ll@bloombees.com>
 * @version 1.0
 */
use CloudFramework\Service\SocialNetworks\SocialNetworks;
use CloudFramework\Service\SocialNetworks\Connectors\GoogleApi;

/** @var ADNBP $this */
$sc = SocialNetworks::getInstance();
$google = (array_key_exists("google_form_credentials", $_SESSION)) ? $_SESSION["google_form_credentials"] : array();
$twitter = (array_key_exists("twitter_form_credentials", $_SESSION)) ? $_SESSION["twitter_form_credentials"] : array();
$facebook = (array_key_exists("facebook_form_credentials", $_SESSION)) ? $_SESSION["facebook_form_credentials"] : array();

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
        $credentials = $sc->auth($postData["social"], $postData, SocialNetworks::generateRequestUrl() . "socialnetworks?googlePlusOAuthCallback");
        // Get followers
        $images = $sc->import($postData["social"], $credentials, "/home/salvador/ADNBP-Cloud-FrameWork/webapp/");
        $exportdto = null;
        if ("" !== $postData["export_content"]) {
            $parameters = array(
                "userId" => "me",
                "content" => $postData["export_content"]
            );
            $exportdto = $sc->export($postData["social"], $credentials, $parameters);
        }
        SocialNetworks::jsonResponse(array(
            "social" => $postData['social'],
            "followers" => $followers,
            "images" => $images,
            "count" => count($images),
            "exportdto" => $exportdto
        ));
        exit;
    } else {
        SocialNetworks::generateErrorResponse("Need a social to make followers request", 400);
    }

} else {
    if (array_key_exists("googlePlusOAuthCallback", $_REQUEST)) {
        $params = array(
            "client" => $_SESSION["google_apikeys"]["client"],
            "secret" => $_SESSION["google_apikeys"]["secret"],
            "code" => $_REQUEST["code"]
        );
        $sc->saveInSession("Google", $params);
    } elseif (array_key_exists("instagramPlusOAuthCallback", $_REQUEST)) {
            $params = array(
                "client" => $_SESSION["instagram_apikeys"]["client"],
                "secret" => $_SESSION["instagram_apikeys"]["secret"],
                "code" => $_REQUEST["code"],
                "error" => $_REQUEST["error"],
                "error_reason" => $_REQUEST["error_reason"],
                "error_description" => $_REQUEST["error_description"]
            );
            $sc->saveInSession("Instagram", $params);
    } elseif (array_key_exists("twitterOAuthCallback", $_REQUEST)) {
        $params = array(
            "client" => $this->getConf("TwitterOauth_KEY"),
            "secret" => $this->getConf("TwitterOauth_SECRET"),
            "verifier" => $_REQUEST["oauth_verifier"],
            "token" => $_REQUEST["oauth_token"]
        );
        $sc->saveInSession("Twitter", $params);
    } elseif (array_key_exists("facebookOAuthCallback", $_REQUEST)) {
        $params = array(
            "client" => $this->getConf("FacebookOauth_APP_ID"),
            "secret" => $this->getConf("FacebookOauth_APP_SECRET"),
            "code" => $_REQUEST["code"],
            "state" => $_REQUEST["state"]
        );
        $sc->saveInSession("Facebook", $params);
    }
}