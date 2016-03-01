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
    function getProfile($userId);
    function getFollowers($userId, $maxResultsPerPage, $numberOfPages, $pageToken);
    function getFollowersInfo($userId, $postId);
    function getSubscribers($userId, $maxResultsPerPage, $numberOfPages, $nextPageUrl);
    function getPosts($userId, $maxResultsPerPage, $numberOfPages, $pageToken);
    function importMedia($parameters);
    function exportMedia($entity, $id, $maxResultsPerPage, $numberOfPages, $pageToken);
    function post($entity, $id, array $parameters);
    function getUserRelationship($authenticatedUserId, $userId);
    function modifyUserRelationship($authenticatedUserId, $userId, $action);
    function searchUsers($userId, $name, $maxTotalResults, $numberOfPages, $nextPageUrl);
}