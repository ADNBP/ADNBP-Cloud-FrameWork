<?php
namespace CloudFramework\Service\SocialNetworks\Interfaces;

/**
 * Interface SocialNetworksInterface
 * @package CloudFramework\Service\SocialNetworks\Interfaces
 */
interface SocialNetworkInterface {

    function getAuth(array $credentials);
    function getAuthUrl(array $credentials);
    function getFollowers(array $credentials);
    function import(array $credentials);
    function export(array $credentials, $content, $link, $logo,
                             $circleId, $personId, $userId);
    function authorize(array $credentials);
}