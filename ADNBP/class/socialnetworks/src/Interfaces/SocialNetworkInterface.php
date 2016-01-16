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
    function getImages(array $credentials);
    function plusStreamWrite(array $credentials, $content, $link, $logo, $userId,
                             $circleId, $personId);
    function authorize(array $credentials);
}