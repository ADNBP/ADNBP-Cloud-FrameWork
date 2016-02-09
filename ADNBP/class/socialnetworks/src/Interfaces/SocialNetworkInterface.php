<?php
namespace CloudFramework\Service\SocialNetworks\Interfaces;

/**
 * Interface SocialNetworksInterface
 * @package CloudFramework\Service\SocialNetworks\Interfaces
 */
interface SocialNetworkInterface {

    function setCredentials($clientId, $clientSecret, $clientScope);
    function getAuth($redirectUrl);
    function getAuthUrl($redirectUrl);
    function getFollowers($userId, array $credentials);
    function getFollowersInfo($postId, array $credentials);
    function getSubscribers($userId, array $credentials);
    function getPosts($userId, array $credentials);
    function getProfile($userId, array $credentials);
    function exportImages($userId, $maxResults, array $credentials);
    function importPost(array $parameters, array $credentials);
    function revokeToken(array $credentials);
    function authorize($code, $redirectUrl);
}