<?php
namespace CloudFramework\Service\SocialNetworks\Interfaces;

/**
 * Interface SocialNetworksInterface
 * @package CloudFramework\Service\SocialNetworks\Interfaces
 */
interface SocialNetworkInterface {

    function getAuth(array $credentials);
    function getAuthUrl();
    function getFollowers(array $credentials);
    function authorize(array $credentials);
}