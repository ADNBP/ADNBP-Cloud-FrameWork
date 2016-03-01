<?php
namespace CloudFramework\Service\SocialNetworks;

use CloudFramework\Patterns\Singleton;

/**
 * Class SocialNetworks
 * @author Fran López <fl@bloombees.com>
 */
class SocialNetworks extends Singleton
{
    const ENTITY_USER = 'user';
    const ENTITY_PAGE = 'page';

    /**
     * @return string
     */
    public static function generateRequestUrl()
    {
        $protocol = (array_key_exists("HTTPS", $_SERVER) && $_SERVER["HTTPS"] === 'on') ? 'https' : 'http';
        $domain = $_SERVER['SERVER_NAME'];
        $port = "";
        if (array_key_exists('SERVER_PORT', $_SERVER)) {
            $port = ":" . $_SERVER['SERVER_PORT'];
        }
        return "$protocol://$domain$port/";
    }

    /**
     * Method that initialize a social api instance to use
     * @param $social
     * @return \CloudFramework\Services\SocialNetworks\Interfaces\SocialNetworksInterface
     * @throws \Exception
     */
    public function getSocialApi($social) {
        $social = ucfirst($social);
        $socialNetworkClass = "CloudFramework\\Service\\SocialNetworks\\Connectors\\{$social}Api";
        if (class_exists($socialNetworkClass)) {
            try {
                return $connector = $socialNetworkClass::getInstance();
            } catch(\Exception $e) {
                throw $e;
            }
        } else {
            throw new \Exception("Social Network Requested not exists", 501);
        }
    }

    /**
     * Service that set the api keys for social network
     * @param $social
     * @param $clientId
     * @param $clientSecret
     * @param array $clientScope
     * @return mixed
     * @throws \Exception
     */
    public function setApiKeys($social, $clientId, $clientSecret, $clientScope = array()) {
        $connector = $this->getSocialApi($social);
        return $connector->setApiKeys($clientId, $clientSecret, $clientScope);
    }

    /**
     * Service that set the access token for social network
     * @param $social
     * @param array $credentials
     * @return mixed
     * @throws \Exception
     */
    public function setAccessToken($social, array $credentials) {
        $connector = $this->getSocialApi($social);
        return $connector->setAccessToken($credentials);
    }

    /**
     * Service to request authorization to the social network
     * @param string $social
     * @param string $redirectUrl
     * @return mixed
     * @throws \Exception
     */
    public function requestAuthorization($social, $redirectUrl)
    {
        $connector = $this->getSocialApi($social);
        return $connector->requestAuthorization($redirectUrl);
    }

    /**
     * Service that authorize a user in the social network.
     * (This method receives the callback from the social network after login)
     * @param string $social
     * @param string $code
     * @param string $redirectUrl
     * @return mixed
     * @throws \Exception
     */
    public function confirmAuthorization($social, $code, $redirectUrl)
    {
        $connector = $this->getSocialApi($social);
        return $connector->authorize($code, $redirectUrl);
    }

    /**
     * Service that check if session user's credentials are authorized and not expired / revoked
     * @param $social
     * @param $credentials
     * @return mixed
     * @throws \Exception
     */
    public function checkCredentials($social, $credentials) {
        $connector = $this->getSocialApi($social);
        return $connector->checkCredentials($credentials);
    }

    /**
     * Service that refresh credentials and return new ones
     * @param $social
     * @param $credentials
     * @throws \Exception
     */
    public function refreshCredentials($social, $credentials) {
        $connector = $this->getSocialApi($social);
        return $connector->refreshCredentials($credentials);
    }

    /**
     * Service that query to a social network api to revoke access token in order
     * to ensure the permissions granted to the application are removed
     * @param string $social
     * @return mixed
     * @throws \Exception
     */
    public function revokeToken($social)
    {
        $connector = $this->getSocialApi($social);
        return $connector->revokeToken();
    }

    /**
     * Service that query to a social network api to get user profile
     * @param string $social
     * @param string $userId
     * @return mixed
     * @throws \Exception
     */
    public function getProfile($social, $userId)    {
        $connector = $this->getSocialApi($social);
        return $connector->getProfile($userId);
    }

    /**
     * Service that query to a social network api to get followers
     * @param string $social
     * @param string $userId
     * @param integer $maxResultsPerPage maximum elements per page
     * @param integer $numberOfPages number of pages
     * @param string $pageToken Indicates a specific page for pagination
     * @return mixed
     * @throws \Exception
     */
    public function getFollowers($social, $userId, $maxResultsPerPage, $numberOfPages, $pageToken)
    {
        $connector = $this->getSocialApi($social);
        return $connector->getFollowers($userId, $maxResultsPerPage, $numberOfPages, $pageToken);
    }

    /**
     * Service that query to a social network api to get followers info
     * @param string $social
     * @param string $userId
     * @param string $postId
     * @return mixed
     * @throws \Exception
     */
    public function getFollowersInfo($social, $userId, $postId)
    {
        $connector = $this->getSocialApi($social);
        return $connector->getFollowersInfo($userId, $postId);
    }

    /**
     * Service that query to a social network api to get subscribers
     * @param string $social
     * @param string $userId
     * @param integer $maxResultsPerPage maximum elements per page
     * @param integer $numberOfPages number of pages
     * @param string $nextPageUrl Indicates a specific page for pagination
     * @return mixed
     * @throws \Exception
     */
    public function getSubscribers($social,  $userId, $maxResultsPerPage, $numberOfPages, $nextPageUrl)
    {
        $connector = $this->getSocialApi($social);
        return $connector->getSubscribers($userId, $maxResultsPerPage, $numberOfPages, $nextPageUrl);
    }

    /**
     * Service that query to a social network api to get posts info
     * @param string $userId
     * @param integer $maxResultsPerPage maximum elements per page
     * @param integer $numberOfPages number of pages
     * @param string $pageToken Indicates a specific page for pagination
     * @return mixed
     * @throws \Exception
     */
    public function getPosts($social, $userId, $maxResultsPerPage, $numberOfPages, $pageToken)
    {
        $connector = $this->getSocialApi($social);
        return $connector->getPosts($userId, $maxResultsPerPage, $numberOfPages, $pageToken);
    }

    /**
     * Service that connect to social network api and request for media files for authenticated user
     * @param string $social
     * @param string $entity "user"|"page"
     * @param string $id    user or page id
     * @param integer $maxResultsPerPage maximum elements per page
     * @param integer $numberOfPages number of pages
     * @param string $pageToken Indicates a specific page for pagination
     * @return mixed
     * @throws \Exception
     */
    public function exportMedia($social, $entity, $id, $maxResultsPerPage, $numberOfPages, $pageToken)
    {
        $connector = $this->getSocialApi($social);
        return $connector->exportMedia($entity, $id, $maxResultsPerPage, $numberOfPages, $pageToken);
    }

    /**
     * Service that get the list of recent media liked by the owner
     * @param $social
     * @param $userId
     * @param $maxTotalResults
     * @param $numberOfPages
     * @param $nextPageUrl
     * @return mixed
     * @throws \Exception
     */
    public function exportMediaRecentlyLiked($social, $userId, $maxTotalResults, $numberOfPages, $nextPageUrl) {
        $connector = $this->getSocialApi($social);
        return $connector->exportMediaRecentlyLiked($userId, $maxTotalResults, $numberOfPages, $nextPageUrl);
    }

    /**
     * Service that connect to social network api and upload a media file (image/video)
     * @param string $social
     * @param array $parameters
     * COMMON TO ALL SOCIAL NETWORKS
     *      "entity"        =>      "user"|"page"
     *      "id"            =>      user or page id
     *      "media_type"    =>      "url"|"path"
     *      "value"         =>      url or path
     * FACEBOOK
     *      "title"         =>      message for the media (mandatory)
     *      "album_id"      =>      album where media will be saved in
     *
     * @return mixed
     * @throws \Exception
     */
    public function importMedia($social, $parameters)
    {
        $connector = $this->getSocialApi($social);
        return $connector->importMedia($parameters);
    }

    /**
     * Service that connect to social network api and export data for authenticated user
     * @param string $social
     * @param string $entity "user"|"page"
     * @param string $id    user or page id
     * @param array $parameters
     * GOOGLE
     *      "user_id"    => User whose google domain the stream will be published in
     *      "content"   => Text of the comment
     *      "access_type" => The type of entry describing to whom access to new post/activity is granted
     *              "person"          => Need a personId parameter
     *              "circle"          => Need a circleId parameter
     *              "myCircles"       => Access to members of all the person's circles
     *              "extendedCircles" => Access to members of all the person's circles, plus all of the people in their circles
     *              "domain"          => Access to members of the person's Google Apps domain
     *              "public"          => Access to anyone on the web
     *      "attachment":
     *          "0": "link" | "image" | "video"
     *          "1": url or path for a file
     *      "person_id"  => Google + user whose domain the stream will be published in (mandatory in case of access_type = "person")
     *      "circle_id"  => Google circle where the stream will be published in (mandatory in case of access_type = "circle")
     * INSTAGRAM
     *      "content"   => Text of the comment
     *      "media_id"   => Instagram media's ID
     *
     * @return mixed
     * @throws \Exception
     */
    public function post($social, $entity, $id, $parameters)
    {
        $connector = $this->getSocialApi($social);
        return $connector->post($entity, $id, $parameters);
    }

    /**
     * Service that get information about a relationship to another user in a social network
     * @param $social
     * @param $authenticatedUserId
     * @param $userId
     * @return mixed
     * @throws \Exception
     */
    public function getUserRelationship($social, $authenticatedUserId, $userId) {
        $connector = $this->getSocialApi($social);
        return $connector->getUserRelationship($authenticatedUserId, $userId);
    }

    /**
     * Service that modify the relationship between the authenticated user and the target user in a social network.
     * @param $social
     * @param $authenticatedUserId
     * @param $userId
     * @param $action
     * @return mixed
     * @throws \Exception
     */
    public function modifyUserRelationship($social, $authenticatedUserId, $userId, $action) {
        $connector = $this->getSocialApi($social);
        return $connector->modifyUserRelationship($authenticatedUserId, $userId, $action);
    }

    /**
     * Service that searches for users in a social network by a name passed as a parameter
     * @param $social
     * @param $userId
     * @param $name
     * @param $maxTotalResults
     * @param $numberOfPages
     * @param $nextPageUrl
     */
    public function searchUsers($social, $userId, $name, $maxTotalResults, $numberOfPages, $nextPageUrl) {
        $connector = $this->getSocialApi($social);
        return $connector->searchUsers($userId, $name, $maxTotalResults, $numberOfPages, $nextPageUrl);
    }

    /**
     * Service that creates a new photo album for the user in a social network
     * @param $social
     * @param string $entity "user"|"page"
     * @param string $id    user or page id
     * @param $title
     * @param $caption
     * @return mixed
     * @throws \Exception
     */
    public function createPhotosAlbum($social, $entity, $id, $title, $caption) {
        $connector = $this->getSocialApi($social);
        return $connector->createPhotosAlbum($entity, $id, $title, $caption);
    }

    /**
     * Service that gets photos albums owned by users in a social network
     * @param $social
     * @param string $entity "user"|"page"
     * @param string $id    user or page id
     * @param $maxResultsPerPage
     * @param $numberOfPages
     * @param $pageToken
     * @return mixed
     * @throws \Exception
     */
    public function exportPhotosAlbumsList($social, $entity, $id, $maxResultsPerPage, $numberOfPages, $pageToken) {
        $connector = $this->getSocialApi($social);
        return $connector->exportPhotosAlbumsList($entity, $id, $maxResultsPerPage, $numberOfPages, $pageToken);
    }

    /**
     * Service that gets photos from an album owned by user in a social network
     * @param $social
     * @param string $entity "user"|"page"
     * @param string $id    user or page id
     * @param $albumId
     * @param $maxResultsPerPage
     * @param $numberOfPages
     * @param $pageToken
     * @return mixed
     * @throws \Exception
     */
    public function exportPhotosFromAlbum($social, $entity, $id, $albumId, $maxResultsPerPage, $numberOfPages, $pageToken) {
        $connector = $this->getSocialApi($social);
        return $connector->exportPhotosFromAlbum($entity, $id, $albumId, $maxResultsPerPage, $numberOfPages, $pageToken);
    }

    /**
     * Service that gets a list of all of the circles for a user
     * @param $social
     * @param $userId
     * @param $maxResultsPerPage
     * @param $numberOfPages
     * @param $pageToken
     * @return mixed
     * @throws \Exception
     */
    public function exportCircles($social, $userId, $maxResultsPerPage, $numberOfPages, $pageToken) {
        $connector = $this->getSocialApi($social);
        return $connector->exportCircles($userId, $maxResultsPerPage, $numberOfPages, $pageToken);
    }

    /**
     * Service that gets a list of people in a circle
     * @param $social
     * @param $userId
     * @param $circleId
     * @param $maxResultsPerPage
     * @param $numberOfPages
     * @param $pageToken
     * @return mixed
     * @throws \Exception
     */
    public function exportPeopleInCircle($social, $userId, $circleId, $maxResultsPerPage, $numberOfPages, $pageToken) {
        $connector = $this->getSocialApi($social);
        return $connector->exportPeopleInCircle($userId, $circleId, $maxResultsPerPage, $numberOfPages, $pageToken);
    }

    /**
     * Service that gets all pages this person administers/is an admin for
     * @param $social
     * @param $userId
     * @param $maxResultsPerPage
     * @param $numberOfPages
     * @param $pageToken
     */
    public function exportPages($social, $userId, $maxResultsPerPage, $numberOfPages, $pageToken) {
        $connector = $this->getSocialApi($social);
        return $connector->exportPages($userId, $maxResultsPerPage, $numberOfPages, $pageToken);
    }

    /**
     * Service that query to a social network api to get page setting
     * @param string $social
     * @param string $pageId
     * @return mixed
     * @throws \Exception
     */
    public function getPage($social, $pageId)    {
        $connector = $this->getSocialApi($social);
        return $connector->getPage($pageId);
    }

    /**
     * General function to check url format
     * @param $redirectUrl
     * @return bool
     */
    public static function wellFormedUrl($redirectUrl) {
        if (!filter_var($redirectUrl, FILTER_VALIDATE_URL) === false) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * General function to get mime type of a file
     * @param $filename
     * @return mixed|string
     */
    public static function mime_content_type($filename) {
        $mime_types = array(

            'txt' => 'text/plain',
            'htm' => 'text/html',
            'html' => 'text/html',
            'php' => 'text/html',
            'css' => 'text/css',
            'js' => 'application/javascript',
            'json' => 'application/json',
            'xml' => 'application/xml',
            'swf' => 'application/x-shockwave-flash',
            'flv' => 'video/x-flv',

            // images
            'png' => 'image/png',
            'jpe' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'jpg' => 'image/jpeg',
            'gif' => 'image/gif',
            'bmp' => 'image/bmp',
            'ico' => 'image/vnd.microsoft.icon',
            'tiff' => 'image/tiff',
            'tif' => 'image/tiff',
            'svg' => 'image/svg+xml',
            'svgz' => 'image/svg+xml',

            // archives
            'zip' => 'application/zip',
            'rar' => 'application/x-rar-compressed',
            'exe' => 'application/x-msdownload',
            'msi' => 'application/x-msdownload',
            'cab' => 'application/vnd.ms-cab-compressed',

            // audio/video
            'mp3' => 'audio/mpeg',
            'qt' => 'video/quicktime',
            'mov' => 'video/quicktime',

            // adobe
            'pdf' => 'application/pdf',
            'psd' => 'image/vnd.adobe.photoshop',
            'ai' => 'application/postscript',
            'eps' => 'application/postscript',
            'ps' => 'application/postscript',

            // ms office
            'doc' => 'application/msword',
            'rtf' => 'application/rtf',
            'xls' => 'application/vnd.ms-excel',
            'ppt' => 'application/vnd.ms-powerpoint',

            // open office
            'odt' => 'application/vnd.oasis.opendocument.text',
            'ods' => 'application/vnd.oasis.opendocument.spreadsheet',
        );

        $exploded = explode('.',$filename);
        $ext = strtolower(array_pop($exploded));
        if (array_key_exists($ext, $mime_types)) {
            return $mime_types[$ext];
        }
        elseif (function_exists('finfo_open')) {
            $finfo = finfo_open(FILEINFO_MIME);
            $mimetype = finfo_file($finfo, $filename);
            finfo_close($finfo);
            return $mimetype;
        }
        else {
            return 'application/octet-stream';
        }
    }
}