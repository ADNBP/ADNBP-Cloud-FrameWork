<?php
namespace CloudFramework\Service\SocialNetworks\Tests;

use CloudFramework\Service\SocialNetworks\Connectors\GoogleApi;
use GuzzleHttp\Client;

/**
 * Class GoogleApiTest
 * @author Salvador Castro <sc@bloombees.com>
 */
class GoogleApiTest extends \PHPUnit_Framework_TestCase {
    private static $redirectUrl = "http://localhost:9081/socialnetworks?googleOAuthCallback";

    public function testLogin()
    {
        $apiKeys = array(
            "client" => "63108327498-mgodb2hd7n1kpfahvda7npqupk5uhdsp.apps.googleusercontent.com",
            "secret" => "BsWhjY0wXVXDcyQ_m7QiVl6j"
        );

        $googleApi = new GoogleApi();
        $loginUrl = $googleApi->getAuthUrl($apiKeys, self::$redirectUrl);

        $client = new Client();
        $response = $client->get($loginUrl);

        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testGetAuth() {
        $credentials = array(
            "client" => "63108327498-mgodb2hd7n1kpfahvda7npqupk5uhdsp.apps.googleusercontent.com",
            "secret" => "BsWhjY0wXVXDcyQ_m7QiVl6j",
            "access_token" => "ya29.dALHX7cUlTQncll7v7kJ9UuO45Gy2TIdWHwifFAuRAYyZtO1O7NISVFOnTVKMAGLdxWk",
            "token_type" => "Bearer",
            "expires_in" => "3600",
            "created" => "1453740805",
            "refresh_token" => "1/sJiGDqPwAc_HHQpuyfP3EDrrXuXSre7yYWCv3G2ImvRIgOrJDtdun6zK6XiATCKT",
            "id_user" => "117337256065347737339",
        );

        $googleApi = new GoogleApi();

        $googleCredentials = $googleApi->getAuth($credentials, self::$redirectUrl);

        $this->assertEquals(2, count($googleCredentials));
        $this->assertTrue(array_key_exists("auth_keys", $googleCredentials));
        $this->assertTrue(array_key_exists("api_keys", $googleCredentials));
    }

    public function testExport() {
        $credentials = array(
            "api_keys" => array(
                "client" => "63108327498-mgodb2hd7n1kpfahvda7npqupk5uhdsp.apps.googleusercontent.com",
                "secret" => "BsWhjY0wXVXDcyQ_m7QiVl6j"
            ),
            "auth_keys" => array(
                "access_token" => "ya29.dALHX7cUlTQncll7v7kJ9UuO45Gy2TIdWHwifFAuRAYyZtO1O7NISVFOnTVKMAGLdxWk",
                "token_type" => "Bearer",
                "expires_in" => "3600",
                "created" => "1453740805",
                "refresh_token" => "1/sJiGDqPwAc_HHQpuyfP3EDrrXuXSre7yYWCv3G2ImvRIgOrJDtdun6zK6XiATCKT",
                "id_user" => "117337256065347737339"
            )
        );

        $googleApi = new GoogleApi();

        $dto = $googleApi->export($credentials, array(
            "userId" => "me",
            "content" => "Test Publication"
        ));

        $this->assertInstanceOf('CloudFramework\Service\SocialNetworks\Dtos\ExportDTO', $dto);
    }

    public function testImport() {
        $credentials = array(
            "api_keys" => array(
                "client" => "63108327498-mgodb2hd7n1kpfahvda7npqupk5uhdsp.apps.googleusercontent.com",
                "secret" => "BsWhjY0wXVXDcyQ_m7QiVl6j"
            ),
            "auth_keys" => array(
                "access_token" => "ya29.dALHX7cUlTQncll7v7kJ9UuO45Gy2TIdWHwifFAuRAYyZtO1O7NISVFOnTVKMAGLdxWk",
                "token_type" => "Bearer",
                "expires_in" => "3600",
                "created" => "1453740805",
                "refresh_token" => "1/sJiGDqPwAc_HHQpuyfP3EDrrXuXSre7yYWCv3G2ImvRIgOrJDtdun6zK6XiATCKT",
                "id_user" => "117337256065347737339"
            )
        );

        $googleApi = new GoogleApi();

        $files = $googleApi->import($credentials, "./");

        if (count($files) > 0) {
            $file = $files[0];
            $this->assertTrue(array_key_exists("id", $file));
            $this->assertTrue(array_key_exists("name", $file));
            $this->assertTrue(array_key_exists("title", $file));
        }
    }
}