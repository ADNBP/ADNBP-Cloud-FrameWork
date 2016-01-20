<?php
namespace CloudFramework\Service\SocialNetworks\Interfaces;

/**
 * Interface SocialNetworksInterface
 * @package CloudFramework\Service\SocialNetworks\Interfaces
 */
interface SocialNetworkInterface {

    function getAuth(array $credentials, $redirectUrl);
    function getAuthUrl(array $credentials, $redirectUrl);
    function import(array $credentials, $path);
    function export(array $credentials, $content, $link, $logo,
                             $circleId, $personId, $mediaId, $userId);
    function authorize(array $credentials);
}