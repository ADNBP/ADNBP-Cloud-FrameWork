<?php
namespace CloudFramework\Service\SocialNetworks\Connectors;

use CloudFramework\Patterns\Singleton;
use CloudFramework\Service\SocialNetworks\Exceptions\AuthenticationException;
use CloudFramework\Service\SocialNetworks\Exceptions\ConnectorConfigException;
use CloudFramework\Service\SocialNetworks\Exceptions\ConnectorServiceException;
use CloudFramework\Service\SocialNetworks\Exceptions\MalformedUrlException;
use CloudFramework\Service\SocialNetworks\Interfaces\SocialNetworkInterface;
use CloudFramework\Service\SocialNetworks\SocialNetworks;

use DirkGroenen\Pinterest\Pinterest;

/**
 * Class PinterestApi
 * @package CloudFramework\Service\SocialNetworks\Connectors
 * @author Salvador Castro <sc@bloombees.com>
 */
class PinterestApi extends Singleton implements SocialNetworkInterface {

    const ID = 'pinterest';
    const PINTEREST_SELF_USER = "me";

    // Pinterest client object
    private $client;

    // API keys
    private $clientId;
    private $clientSecret;
    private $clientScope = array();

    // Auth keys
    private $accessToken;

    /**
     * Set Pinterest Api keys
     * @param $clientId
     * @param $clientSecret
     * @param $clientScope
     * @throws ConnectorConfigException
     */
    public function setApiKeys($clientId, $clientSecret, $clientScope) {
        if ((null === $clientId) || ("" === $clientId)) {
            throw new ConnectorConfigException("'clientId' parameter is required");
        }

        if ((null === $clientSecret) || ("" === $clientSecret)) {
            throw new ConnectorConfigException("'clientSecret' parameter is required");
        }

        if ((null === $clientScope) || (!is_array($clientScope)) || (count($clientScope) == 0)) {
            throw new ConnectorConfigException("'clientScope' parameter is required");
        }

        $this->clientId = $clientId;
        $this->clientSecret = $clientSecret;
        $this->clientScope = $clientScope;

        $this->client = new Pinterest($this->clientId, $this->clientSecret);
    }

    /**
     * Compose Pinterest Api credentials array from session data
     * @param string $redirectUrl
     * @throws ConnectorConfigException
     * @throws MalformedUrlException
     * @return array
     */
    public function requestAuthorization($redirectUrl)
    {
        if ((null === $redirectUrl) || (empty($redirectUrl))) {
            throw new ConnectorConfigException("'redirectUrl' parameter is required");
        } else {
            if (!SocialNetworks::wellFormedUrl($redirectUrl)) {
                throw new MalformedUrlException("'redirectUrl' is malformed");
            }
        }

        $authUrl = $this->client->auth->getLoginUrl($redirectUrl, $this->clientScope);

        // Authentication request
        return $authUrl;
    }

    /**
     * @param string $code
     * @param string $redirectUrl
     * @return array
     * @throws AuthenticationException
     * @throws ConnectorConfigException
     * @throws ConnectorServiceException
     * @throws MalformedUrlException
     */
    public function authorize($code, $redirectUrl)
    {
        if ((null === $code) || ("" === $code)) {
            throw new ConnectorConfigException("'code' parameter is required");
        }

        if ((null === $redirectUrl) || (empty($redirectUrl))) {
            throw new ConnectorConfigException("'redirectUrl' parameter is required");
        } else {
            if (!SocialNetworks::wellFormedUrl($redirectUrl)) {
                throw new MalformedUrlException("'redirectUrl' is malformed");
            }
        }

        $token = $this->client->auth->getOAuthToken($code);

        $pinterestCredentials = array("access_token" => $token->access_token);

        return $pinterestCredentials;
    }

    /**
     * Method that inject the access token in connector
     * @param array $credentials
     */
    public function setAccessToken(array $credentials) {
        $this->accessToken = $credentials["access_token"];
    }

    /**
     * Service that check if credentials are valid
     * @param $credentials
     * @return mixed
     * @throws ConnectorConfigException
     */
    public function checkCredentials($credentials) {
        $this->checkCredentialsParameters($credentials);

        try {
            return $this->getProfile(SocialNetworks::ENTITY_USER, self::PINTEREST_SELF_USER);
        } catch(\Exception $e) {
            throw new ConnectorConfigException("Invalid credentials set");
        }
    }

    /**
     * Service that query to Pinterest Api to get user profile
     * @param string $entity "user"
     * @param string $id    user id
     * @return string
     * @throws ConnectorConfigException
     * @throws ConnectorServiceException
     */
    public function getProfile($entity, $id)
    {
        $this->checkUser($id);

        try {
            $parameters = array();
            $parameters["fields"] = "id,username,first_name,last_name,bio,created_at,counts,image";
            $this->client->auth->setOAuthToken($this->accessToken);
            $data = $this->client->users->me($parameters);
        } catch(\Exception $e) {
            throw new ConnectorConfigException("Invalid credentials set");
        }

        // Instagram API doesn't return the user's e-mail
        return json_encode($data);
    }

    /**
     * Service that search for an user
     * @param string $entity "user"
     * @param string $id    user id
     * @param $name
     * @return string
     * @throws ConnectorConfigException
     * @throws ConnectorServiceException
     */
    public function searchUsers($entity, $id, $username_or_id, $maxTotalResults = null, $numberOfPages = null,
                                $nextPageUrl = null)
    {
        $this->checkUser($id);
        $this->checkName($username_or_id);

        try {
            $this->client->auth->setOAuthToken($this->accessToken);
            $data = $this->client->users->find($username_or_id);
        } catch (Exception $e) {
            throw new ConnectorServiceException("Error searching for an user: " . $e->getMessage(), $e->getCode());
            $pageToken = null;
        }

        return json_encode($data);
    }

    /**
     * Service that query to Pinterest Api for pins of the user
     * @param string $entity "user"
     * @param string $id    user id
     * @param string $query if not null, search this token in the description of the authenticated user's pins
     * @param string $liked if true, search pins liked by the user
     * @param integer $maxResultsPerPage.
     * @param integer $numberOfPages
     * @param string $pageToken
     * @return string
     * @throws ConnectorConfigException
     * @throws ConnectorServiceException
     */
    public function exportPins($entity, $id, $query, $liked, $maxResultsPerPage, $numberOfPages, $pageToken) {
        $this->checkUser($id);
        $this->checkPagination($maxResultsPerPage, $numberOfPages);
        $this->client->auth->setOAuthToken($this->accessToken);

        $pins = array();
        $count = 0;

        do {
            try {
                $parameters = array();
                $parameters["limit"] = $maxResultsPerPage;

                if ($pageToken) {
                    $parameters["cursor"] = urldecode($pageToken);
                }

                $parameters["fields"] = "id,link,url,creator,board,created_at,note,color,counts,media,attribution,image,metadata,original_link";

                if (null == $query) {
                    if ($liked) {
                        $pinsList = $this->client->users->getMeLikes($parameters);
                    } else {
                        $pinsList = $this->client->users->getMePins($parameters);
                    }
                } else {
                    $pinsList = $this->client->users->searchMePins($query, $parameters);
                }

                // The strange pagination behaviour in Pinterest: although there aren't more elements / more pages,
                // current list object returns cursor/pagetoken to go to the next page, what is obvously empty, so it
                // should be checked that pinterest api is returning an empty list
                if (count($pinsList->all()) == 0) {
                    $pageToken = null;
                    break;
                }

                $pins[$count] = array();
                foreach($pinsList->all() as $pin) {
                    $pins[$count][] = $pin;
                }
                $count++;

                $pageToken = $pinsList->pagination["cursor"];

                // If number of pages is zero, then all elements are returned
                if (($numberOfPages > 0) && ($count == $numberOfPages)) {
                    // Make a last call to check if next page is empty
                    $parameters["cursor"] = urldecode($pageToken);
                    if (null == $query) {
                        if ($liked) {
                            $pinsList = $this->client->users->getMeLikes($parameters);
                        } else {
                            $pinsList = $this->client->users->getMePins($parameters);
                        }
                    } else {
                        $pinsList = $this->client->users->searchMePins($query, $parameters);
                    }

                    // The strange pagination behaviour in Pinterest: although there aren't more elements / more pages,
                    // current list object returns cursor/pagetoken to go to the next page, what is obvously empty, so it
                    // should be checked that pinterest api is returning an empty list
                    if (count($pinsList->all()) == 0) {
                        $pageToken = null;
                    }
                    break;
                }
            } catch (Exception $e) {
                $pageToken = null;
                throw new ConnectorServiceException("Error exporting pins: " . $e->getMessage(), $e->getCode());
            }
        } while ($pinsList->hasNextPage());

        $pins["pageToken"] = $pageToken;

        return json_encode($pins);
    }

    /**
     * Service that query to Pinterest Api for boards of the user
     * @param string $entity "user"
     * @param string $id    user id
     * @param string $query if not null, search this token in the description of the authenticated user's pins
     * @param integer $maxResultsPerPage.
     * @param integer $numberOfPages
     * @param string $pageToken
     * @return string
     * @throws ConnectorConfigException
     * @throws ConnectorServiceException
     */
    public function exportBoards($entity, $id, $query = null, $maxResultsPerPage, $numberOfPages, $pageToken) {
        $this->checkUser($id);
        $this->checkPagination($maxResultsPerPage, $numberOfPages);
        $this->client->auth->setOAuthToken($this->accessToken);

        $boards = array();
        $count = 0;

        do {
            try {
                $parameters = array();
                $parameters["limit"] = $maxResultsPerPage;

                if ($pageToken) {
                    $parameters["cursor"] = urldecode($pageToken);
                }

                $parameters["fields"] = "id,name,url,description,creator,created_at,counts,image";

                if (null == $query) {
                    $boardsList = $this->client->users->getMeBoards($parameters);
                } else {
                    $boardsList = $this->client->users->searchMeBoards($query, $parameters);
                }

                // The strange pagination behaviour in Pinterest: although there aren't more elements / more pages,
                // current list object returns cursor/pagetoken to go to the next page, what is obvously empty, so it
                // should be checked that pinterest api is returning an empty list
                if (count($boardsList->all()) == 0) {
                    $pageToken = null;
                    break;
                }

                $boards[$count] = array();
                foreach($boardsList->all() as $board) {
                    $boards[$count][] = $board;
                }
                $count++;

                $pageToken = $boardsList->pagination["cursor"];

                // If number of pages is zero, then all elements are returned
                if (($numberOfPages > 0) && ($count == $numberOfPages)) {
                    // Make a last call to check if next page is empty
                    $parameters["cursor"] = urldecode($pageToken);
                    if (null == $query) {
                        $boardsList = $this->client->users->getMeBoards($parameters);
                    } else {
                        $boardsList = $this->client->users->searchMeBoards($query, $parameters);
                    }

                    // The strange pagination behaviour in Pinterest: although there aren't more elements / more pages,
                    // current list object returns cursor/pagetoken to go to the next page, what is obvously empty, so it
                    // should be checked that pinterest api is returning an empty list
                    if (count($boardsList->all()) == 0) {
                        $pageToken = null;
                    }
                    break;
                }
            } catch (Exception $e) {
                $pageToken = null;
                throw new ConnectorServiceException("Error exporting boards: " . $e->getMessage(), $e->getCode());
            }
        } while ($boardsList->hasNextPage());

        $boards["pageToken"] = $pageToken;

        return json_encode($boards);
    }

    /**
     * Service that query to Pinterest Api for users the user is followed by
     * @param string $entity "user"
     * @param string $id    user id
     * @param $maxResultsPerPage
     * @param $numberOfPages
     * @param $pageToken
     * @return string
     * @throws ConnectorConfigException
     * @throws ConnectorServiceException
     */
    public function exportFollowers($entity, $id, $maxResultsPerPage, $numberOfPages, $pageToken) {
        $this->checkUser($id);
        $this->checkPagination($maxResultsPerPage, $numberOfPages);
        $this->client->auth->setOAuthToken($this->accessToken);

        $followers = array();
        $count = 0;

        do {
            try {
                $parameters = array();
                $parameters["limit"] = $maxResultsPerPage;

                if ($pageToken) {
                    $parameters["cursor"] = urldecode($pageToken);
                }

                $followersList = $this->client->users->getMeFollowers($parameters);

                // The strange pagination behaviour in Pinterest: although there aren't more elements / more pages,
                // current list object returns cursor/pagetoken to go to the next page, what is obvously empty, so it
                // should be checked that pinterest api is returning an empty list
                if (count($followersList->all()) == 0) {
                    $pageToken = null;
                    break;
                }

                $followers[$count] = array();
                foreach($followersList->all() as $follower) {
                    $followers[$count][] = $follower;
                }
                $count++;

                $pageToken = $followersList->pagination["cursor"];

                // If number of pages is zero, then all elements are returned
                if (($numberOfPages > 0) && ($count == $numberOfPages)) {
                    // Make a last call to check if next page is empty
                    $parameters["cursor"] = urldecode($pageToken);
                    $followersList = $this->client->users->getMeFollowers($parameters);

                    // The strange pagination behaviour in Pinterest: although there aren't more elements / more pages,
                    // current list object returns cursor/pagetoken to go to the next page, what is obvously empty, so it
                    // should be checked that pinterest api is returning an empty list
                    if (count($followersList->all()) == 0) {
                        $pageToken = null;
                    }
                    break;
                }
            } catch (Exception $e) {
                $pageToken = null;
                throw new ConnectorServiceException("Error exporting followers: " . $e->getMessage(), $e->getCode());
            }
        } while ($followersList->hasNextPage());

        $followers["pageToken"] = $pageToken;

        return json_encode($followers);
    }

    /**
     * Service that query to Pinterest Api to get board settings
     * @param $entity   "board"
     * @param $username
     * @param $boardname
     * @return string
     * @throws ConnectorConfigException
     * @throws ConnectorServiceException
     */
    public function getBoard($entity, $username, $boardname) {
        $this->checkBoard($username, $boardname);
        $this->client->auth->setOAuthToken($this->accessToken);

        try {
            $parameters["fields"] = "id,name,url,description,creator,created_at,counts,image";
            $boardname = strtolower(str_replace(" ","-",urldecode($boardname)));
            $board = $this->client->boards->get($username."/".$boardname, $parameters);
        } catch(\Exception $e) {
            throw new ConnectorServiceException('Error getting board settings: ' . $e->getMessage(), $e->getCode());
        }

        return json_encode($board);
    }

    /**
     * Service that creates a new board for the user in Pinterest
     * @param $entity
     * @param $id
     * @param $name
     * @param $description
     */
    public function createBoard($entity, $id, $name, $description) {
        $this->checkUser($id);
        $this->client->auth->setOAuthToken($this->accessToken);

        try {
            $parameters = array("name" => $name);

            if (null !== $description) {
                $parameters["description"] = $description;
            }

            $board = $this->client->boards->create($parameters);
        } catch(\Exception $e) {
            throw new ConnectorServiceException('Error creating board: ' . $e->getMessage(), $e->getCode());
        }

        return json_encode($board);
    }

    /**
     * Service that query to Instagram Api for users the user is following
     * @param string $entity "user"
     * @param string $id    user id
     * @param integer $maxResultsPerPage.
     * @param integer $numberOfPages
     * @param string $nextPageUrl
     * @return string
     * @throws ConnectorConfigException
     * @throws ConnectorServiceException
     */
    public function exportSubscribers($entity, $id, $maxResultsPerPage, $numberOfPages, $nextPageUrl) {
        $this->checkUser($id);
        $this->checkPagination($numberOfPages);

        if (!$nextPageUrl) {
            $nextPageUrl = self::INSTAGRAM_API_USERS_URL . $id .
                "/follows?access_token=" . $this->accessToken;
        }

        $pagination = true;
        $subscribers = array();
        $count = 0;

        while ($pagination) {
            $data = $this->curlGet($nextPageUrl);

            if ((null === $data["data"]) && ($data["meta"]["code"] !== 200)) {
                throw new ConnectorServiceException("Error getting subscribers: " .
                    $data["meta"]["error_message"], $data["meta"]["code"]);
            }

            $subscribers[$count] = array();

            foreach ($data["data"] as $key => $subscriber) {
                $subscribers[$count][] = $subscriber;
            }

            // If number of pages is zero, then all elements are returned
            if ((($numberOfPages > 0) && ($count == $numberOfPages)) || (!isset($data->pagination->next_url))) {
                $pagination = false;
                if (!isset($data->pagination->next_url)) {
                    $nextPageUrl = null;
                }
            } else {
                $nextPageUrl = $data->pagination->next_url;
                $count++;
            }
        }

        $subscribers["nextPageUrl"] = $nextPageUrl;

        return json_encode($subscribers);
    }

    public function exportPosts($entity, $id, $maxResultsPerPage, $numberOfPages, $pageToken) {
        return;
    }

    /**
     * Service that query to Instagram Api service for media files
     * @param string $entity "user"
     * @param string $id    user id
     * @param integer $maxTotalResults.
     * @param integer $numberOfPages
     * @param string $nextPageUrl
     * @return string
     * @throws ConnectorConfigException
     * @throws ConnectorServiceException
     */
    public function exportMedia($entity, $id, $maxTotalResults, $numberOfPages, $nextPageUrl)
    {
        $this->checkUser($id);
        $this->checkPagination($numberOfPages, $maxTotalResults);

        if (!$nextPageUrl) {
            $nextPageUrl = self::INSTAGRAM_API_USERS_URL . $id .
                        "/media/recent/?access_token=" . $this->accessToken .
                        "&count=".$maxTotalResults;
        }

        $pagination = true;
        $files = array();
        $count = 0;

        while ($pagination) {
            $data = $this->curlGet($nextPageUrl);

            if ((null === $data["data"]) && ($data["meta"]["code"] !== 200)) {
                throw new ConnectorServiceException("Error exporting media: " .
                    $data["meta"]["error_message"], $data["meta"]["code"]);
            }

            $files[$count] = array();

            foreach ($data["data"] as $key => $media) {
                if ("image" === $media["type"]) {
                    $files[$count][] = $media;
                }
            }

            // If number of pages is zero, then all elements are returned
            if ((($numberOfPages > 0) && ($count == $numberOfPages)) || (!isset($data->pagination->next_url))) {
                $pagination = false;
                if (!isset($data->pagination->next_url)) {
                    $nextPageUrl = null;
                }
            } else {
                $nextPageUrl = $data->pagination->next_url;
                $count++;
            }
        }

        $files["nextPageUrl"] = $nextPageUrl;

        return json_encode($files);
    }

    /**
     * Service that get the list of recent media liked by the owner
     * @param string $entity "user"
     * @param string $id    user id
     * @param $maxTotalResults
     * @param $numberOfPages
     * @param $nextPageUrl
     * @return string
     * @throws ConnectorConfigException
     * @throws ConnectorServiceException
     */
    public function exportMediaRecentlyLiked($entity, $id, $maxTotalResults, $numberOfPages, $nextPageUrl)
    {
        $this->checkUser($id);
        $this->checkPagination($numberOfPages, $maxTotalResults);

        $id = self::INSTAGRAM_SELF_USER;

        if (!$nextPageUrl) {
            $nextPageUrl = self::INSTAGRAM_API_USERS_URL . $id .
                "/media/liked/?access_token=" . $this->accessToken .
                "&count=".$maxTotalResults;
        }

        $pagination = true;
        $files = array();
        $count = 0;

        while ($pagination) {
            $data = $this->curlGet($nextPageUrl);

            if ((null === $data["data"]) && ($data["meta"]["code"] !== 200)) {
                throw new ConnectorServiceException("Error exporting media: " .
                    $data["meta"]["error_message"], $data["meta"]["code"]);
            }

            $files[$count] = array();

            foreach ($data["data"] as $key => $media) {
                if ("image" === $media["type"]) {
                    $files[$count][] = $media;
                }
            }

            // If number of pages is zero, then all elements are returned
            if ((($numberOfPages > 0) && ($count == $numberOfPages)) || (!isset($data->pagination->next_url))) {
                $pagination = false;
                if (!isset($data->pagination->next_url)) {
                    $nextPageUrl = null;
                }
            } else {
                $nextPageUrl = $data->pagination->next_url;
                $count++;
            }
        }

        $files["nextPageUrl"] = $nextPageUrl;

        return json_encode($files);
    }

    public function importMedia($entity, $id, $parameters) {
        return;
    }

    /**
     * Service that publish a comment in an Instagram media
     * @param array $parameters
     *      "content" => Text of the comment
     *      "media_id" => Instagram media's ID
     * @return string
     * @throws ConnectorConfigException
     * @throws ConnectorServiceException
     */
    public function post($entity, $id, array $parameters) {
        if ((null === $parameters) || (!is_array($parameters)) || (count($parameters) == 0)) {
            throw new ConnectorConfigException("Invalid post parameters'");
        }

        if ((!array_key_exists('content', $parameters)) ||
            (null === $parameters["content"]) || (empty($parameters["content"]))) {
            throw new ConnectorConfigException("'content' parameter is required");
        }

        if ((!array_key_exists('media_id', $parameters)) ||
            (null === $parameters["media_id"]) || (empty($parameters["media_id"]))) {
            throw new ConnectorConfigException("'media_id' parameter is required");
        }

        $url = self::INSTAGRAM_API_MEDIA_URL.$parameters["media_id"]."/comments";

        $fields = "access_token=".$this->accessToken.
                    "&text=".$parameters["content"];

        $data = $this->curlPost($url, $fields);

        if ((null === $data["data"]) && ($data["meta"]["code"] !== 200)) {
            throw new ConnectorServiceException("Error making comments on an Instagram media: " . 
                $data["meta"]["error_message"], $data["meta"]["code"]);
        }

        return json_encode($data);
    }

    /**
     * Service that query to Instagram Api to get user relationship information
     * @param string $entity "user"
     * @param string $id    user id
     * @param string $userId
     * @return string
     * @throws ConnectorConfigException
     * @throws ConnectorServiceException
     */
    public function getUserRelationship($entity, $id, $userId)
    {
        $this->checkUser($userId);

        $url = self::INSTAGRAM_API_USERS_URL . $id . "/relationship?access_token=" . $this->accessToken;

        $data = $this->curlGet($url);

        if ((null === $data["data"]) && ($data["meta"]["code"] !== 200)) {
            throw new ConnectorServiceException("Error getting relationship info: " .
                $data["meta"]["error_message"], $data["meta"]["code"]);
        }

        return json_encode($data["data"]);
    }

    /**
     * Service that modify the relationship between the authenticated user and the target user.
     * @param string $entity "user"
     * @param string $id    user id
     * @param $userId
     * @param $action
     * @return string
     * @throws ConnectorConfigException
     * @throws ConnectorServiceException
     */
    public function modifyUserRelationship($entity, $id, $userId, $action) {
        $this->checkUser($id);

        $fields = "action=".$action;
        $url = self::INSTAGRAM_API_USERS_URL . $userId . "/relationship?access_token=" . $this->accessToken;

        $data = $this->curlPost($url, $fields);

        if ((null === $data["data"]) && ($data["meta"]["code"] !== 200)) {
            throw new ConnectorServiceException("Error modifying relationship: " .
                $data["meta"]["error_message"], $data["meta"]["code"]);
        }

        return json_encode($data["data"]);
    }

    /**
     * Method that check credentials are present and valid
     * @param array $credentials
     * @throws ConnectorConfigException
     */
    private function checkCredentialsParameters(array $credentials) {
        if ((null === $credentials) || (!is_array($credentials)) || (count($credentials) == 0)) {
            throw new ConnectorConfigException("Invalid credentials set'");
        }

        if ((!isset($credentials["access_token"])) || (null === $credentials["access_token"]) || ("" === $credentials["access_token"])) {
            throw new ConnectorConfigException("'access_token' parameter is required");
        }
    }

    /**
     * Method that check userId is ok
     * @param $userId
     * @throws ConnectorConfigException
     */
    private function checkUser($userId) {
        if ((null === $userId) || ("" === $userId)) {
            throw new ConnectorConfigException("'userId' parameter is required");
        }
    }

    /**
     * Method that check boardId is ok
     * @param $username
     * @param $boardname
     * @throws ConnectorConfigException
     */
    private function checkBoard($username, $boardname) {
        if ((null === $username) || ("" === $username) ||
            (null === $boardname) || ("" === $boardname)) {
            throw new ConnectorConfigException("'boardId' parameter is required");
        }
    }

    /**
     * Method that check pagination parameters are ok
     * @param $maxResultsPerPage
     * @param $numberOfPages
     * @throws ConnectorConfigException
     */
    private function checkPagination($maxResultsPerPage, $numberOfPages) {
        if (null === $maxResultsPerPage) {
            throw new ConnectorConfigException("'maxResultsPerPage' parameter is required");
        } else if (!is_numeric($maxResultsPerPage)) {
            throw new ConnectorConfigException("'maxResultsPerPage' parameter is not numeric");
        }

        if (null === $maxResultsPerPage) {
            throw new ConnectorConfigException("'numberOfPages' parameter is required");
        } else if (!is_numeric($numberOfPages)) {
            throw new ConnectorConfigException("'numberOfPages' parameter is not numeric");
        }
    }

    /**
     * Method that check search name is ok
     * @param $name
     * @throws ConnectorConfigException
     */
    private function checkName($name) {
        if ((null === $name) || ("" === $name)) {
            throw new ConnectorConfigException("'name' parameter is required");
        }
    }

    /**
     * Method that calls url with GET method
     * @param $url
     * @return array
     * @throws \Exception
     */
    private function curlGet($url) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $data = curl_exec($ch);
        curl_close($ch);

        if (!$data) {
            throw \Exception("Error calling service: ".curl_error($ch), curl_errno($ch));
        }
        return json_decode($data, true);
    }

    /**
     * Method that calls url with POST method
     * @param $url
     * @param $fields
     * @return array
     * @throws \Exception
     */
    private function curlPost($url, $fields) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $data = curl_exec($ch);
        curl_close($ch);

        if (!$data) {
            throw \Exception("Error calling service: ".curl_error($ch), curl_errno($ch));
        }
        return json_decode($data, true);
    }
}