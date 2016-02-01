<?php
    namespace CloudFramework\Service\SocialNetworks\Exceptions;

    class ConnectorConfigException extends \Exception
    {
        /**
         * Error codes
         *
         *      600     api_keys set is empty
         *      601     'client' parameter is required
         *      602     'client' parameter is empty
         *      603     'secret' parameter is required
         *      604     'secret' parameter is empty
         *      605     auth_keys set is empty
         *      606     'access_token' parameter is required
         *      607     'access_token' parameter is empty
         *      608     'token_type' parameter is required
         *      609     'token_type' parameter is empty
         *      610     'expires_in' parameter is required
         *      611     'expires_in' parameter is empty
         *      612     'created' parameter is required
         *      613     'created' parameter is empty
         *      614     'refresh_token' parameter is required
         *      615     'refresh_token' parameter is empty
         *      616     'code' parameter is required
         *      617     'code' parameter is empty
         *      618     'redirectUrl' parameter is required
         *      619     'redirectUrl' is malformed
         *      620     'userId' parameter is required (google)
         *      621     'userId' parameter is empty (google)
         *      622     'content' parameter is required
         *      623     'content' parameter is empty
         *      624     'redirectUrl' parameter is empty
         *      625     'mediaId' parameter is required (instagram)
         *      626     'mediaId' parameter is empty (instagram)
         *
         */
    }