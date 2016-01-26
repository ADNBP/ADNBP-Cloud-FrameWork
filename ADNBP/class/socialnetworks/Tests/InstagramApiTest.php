<?php
namespace CloudFramework\Service\SocialNetworks\Tests;

use CloudFramework\Service\SocialNetworks\Connectors\InstagramApi;
use GuzzleHttp\Client;

/**
 * Class InstagramApiTest
 * @author Salvador Castro <sc@bloombees.com>
 */
class InstagramApiTest extends \PHPUnit_Framework_TestCase {
    private static $redirectUrl = "http://localhost:9081/socialnetworks?instagramOAuthCallback=1";

    public function testLogin()
    {
        $apiKeys = array(
            "client" => "da2b4d273dfe40058dac4846560c4991",
            "secret" => "8e2b540a13dd4ae8a455eac93e17a8f0"
        );

        $instagramApi = new InstagramApi();
        $loginUrl = $instagramApi->getAuthUrl($apiKeys, self::$redirectUrl);

        $client = new Client();
        $response = $client->get($loginUrl);

        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testGetAuth() {
        $credentials = array(
            "client" => "da2b4d273dfe40058dac4846560c4991",
            "secret" => "8e2b540a13dd4ae8a455eac93e17a8f0",
            "access_token" => "2706621438.da2b4d2.1a6e4eeebc064bb9a22da7960f608b26",
        );

        $instagramApi = new InstagramApi();

        $instagramCredentials = $instagramApi->getAuth($credentials, self::$redirectUrl);

        $this->assertEquals(2, count($instagramCredentials));
        $this->assertTrue(array_key_exists("auth_keys", $instagramCredentials));
        $this->assertTrue(array_key_exists("api_keys", $instagramCredentials));
    }

    public function testImport() {
        $credentials = array(
            "api_keys" => array(
                "client" => "da2b4d273dfe40058dac4846560c4991",
                "secret" => "8e2b540a13dd4ae8a455eac93e17a8f0",
            ),
            "auth_keys" => array(
                "access_token" => "2706621438.da2b4d2.1a6e4eeebc064bb9a22da7960f608b26",
            )
        );

        $instagramApi = new InstagramApi();

        $files = $instagramApi->import($credentials, "./");

        if (count($files) > 0) {
            $file = $files[0];
            $this->assertTrue(array_key_exists("id", $file));
            $this->assertTrue(array_key_exists("name", $file));
            $this->assertTrue(array_key_exists("title", $file));
        }

        return $files;
    }

    /**
     * @depends testImport
     */
    public function testExport(array $files) {
        if (count($files) > 0) {
            $credentials = array(
                "api_keys" => array(
                    "client" => "da2b4d273dfe40058dac4846560c4991",
                    "secret" => "8e2b540a13dd4ae8a455eac93e17a8f0",
                ),
                "auth_keys" => array(
                    "access_token" => "2706621438.da2b4d2.1a6e4eeebc064bb9a22da7960f608b26",
                )
            );

            $instagramApi = new InstagramApi();

            $dto = $instagramApi->export($credentials, array(
                "mediaId" => $files[0]["id"],
                "content" => "Test Publication"
            ));

            $this->assertInstanceOf('CloudFramework\Service\SocialNetworks\Dtos\ExportDTO', $dto);
        }
    }
}