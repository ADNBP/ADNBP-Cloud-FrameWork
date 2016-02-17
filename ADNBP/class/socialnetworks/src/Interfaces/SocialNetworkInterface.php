<?php
namespace CloudFramework\Service\SocialNetworks\Interfaces;

/**
 * Interface SocialNetworksInterface
 * @package CloudFramework\Service\SocialNetworks\Interfaces
 */
interface SocialNetworkInterface {

    function setApiKeys($clientId, $clientSecret, $clientScope);
    function requestAuthorization($redirectUrl);
    function authorize($code, $redirectUrl);
    function setAccessToken(array $credentials);
    function revokeToken();
    function getFollowers($userId, $maxResultsPerPage, $numberOfPages, $pageToken);
    function getFollowersInfo($userId, $postId);
    function getSubscribers($userId, $maxResultsPerPage, $numberOfPages, $nextPageUrl);
    function getPosts($userId, $maxResultsPerPage, $numberOfPages, $pageToken);
    function getProfile($userId);
    function importMedia($userId, $mediaType, $value);
    function exportImages($userId, $maxResultsPerPage, $numberOfPages, $pageToken);
    function post(array $parameters);

}