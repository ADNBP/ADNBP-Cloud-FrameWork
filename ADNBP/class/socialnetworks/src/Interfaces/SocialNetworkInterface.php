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
    function export(array $credentials, array $parameters);
    function authorize(array $credentials);
}