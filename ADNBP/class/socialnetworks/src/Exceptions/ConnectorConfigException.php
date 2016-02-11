<?php
    namespace CloudFramework\Service\SocialNetworks\Exceptions;

    class ConnectorConfigException extends \Exception
    {
        /**
         * Error codes
         *
         *      601     'clientId' parameter is required
         *      602     'clientSecret' parameter is required
         *      603     'clientScope' parameter is required
         *      604     Invalid credentials set
         *      605     'access_token' parameter is required
         *      606     'refresh_token' parameter is required
         *      607     'userId' parameter is required
         *      608     'maxResultsPerPage' parameter is required
         *      609     'maxResultsPerPage' parameter is not numeric
         *      610     'numberOfPages' parameter is required
         *      611     'numberOfPages' parameter is not numeric
         *      612     'postId' parameter is required
         *      613     'path' parameter is required
         *      614     file doesn't exist
         *      615     file must be an image or a video
         *      616     Maximum file size is XXXX MB
         *      617     Invalid post parameters
         *      618     'user_id' post parameter is required
         *      619     'content' post parameter is required"
         *      620     'access_type' post parameter is required
         *      621     'circle_id' post parameter is required since access_type is 'circle'
         *      622     'person_id' post parameter is required since access_type is 'person
         *      623     'attachment' post parameter must be an array
         *      624     'attachment' type must be 'link', 'photo' or 'video'
         *      625     'attachment' value must be an url ('link') or a file path ('photo' or 'video')
         *      626     'attachment' value url is malformed
         *      627     'code' parameter is required
         *      628     'redirectUrl' parameter is required
         *      629     'authUrl' parameter is required
         *      630     'content' parameter is required
         *      631     'mediaId' parameter is required
         */
    }