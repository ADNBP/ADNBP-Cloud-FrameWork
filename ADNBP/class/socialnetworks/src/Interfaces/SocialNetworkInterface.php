<?php
namespace CloudFramework\Service\SocialNetworks\Interfaces;

/**
 * Interface SocialNetworksInterface
 * @package CloudFramework\Service\SocialNetworks\Interfaces
 */
interface SocialNetworkInterface {

    function setApiKeys($clientId, $clientSecret, $clientScope);
    function requestAuthorization($redirectUrl);
    function getFollowers($userId, $maxResultsPerPage, $numberOfPages, $pageToken, array $credentials);
    function getFollowersInfo($userId, $postId, array $credentials);
    function getSubscribers($userId, $maxResultsPerPage, $numberOfPages, $nextPageUrl, array $credentials);
    function getPosts($userId, $maxResultsPerPage, $numberOfPages, $pageToken, array $credentials);
    function getProfile($userId, array $credentials);
    function importMedia($userId, $path, array $credentials);
    function getProfileId(array $credentials);
    function exportImages($userId, $maxResultsPerPage, $numberOfPages, $pageToken, array $credentials);
    function post(array $parameters, array $credentials);
    function revokeToken(array $credentials);
    function authorize($code, $redirectUrl);
}