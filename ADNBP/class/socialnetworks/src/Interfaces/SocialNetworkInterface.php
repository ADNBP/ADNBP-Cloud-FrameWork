<?php
namespace CloudFramework\Service\SocialNetworks\Interfaces;

/**
 * Interface SocialNetworksInterface
 * @package CloudFramework\Service\SocialNetworks\Interfaces
 */
interface SocialNetworkInterface {

    function getAuth(array $credentials, $redirectUrl);
    function getAuthUrl(array $credentials, $redirectUrl);
    function getFollowers($userId, array $credentials);
    function getFollowersInfo($postId, array $credentials);
    function getSubscribers($userId, array $credentials);
    function getPosts($userId, array $credentials);
    function getProfile($userId, array $credentials);
    function import(array $credentials, $maxResults, $userId);
    function export(array $credentials, array $parameters);
    function revokeToken(array $credentials);
    function authorize(array $credentials);
}