<?php

namespace VKPollsDataGrabber;

final class Application
{
    /**
     * Pdo
     *
     * @var \PDO
     */
    private $_db;

    /**
     * Configuration
     *
     * @var array
     */
    private $_config;

    /**
     * Logger
     *
     * @var Logger
     */
    private $_logger;

    /**
     * Authorization
     *
     * @return bool|string
     */
    public function auth()
    {
        $accessToken = $this->_getValueFromStorage('access_token');

        if (!$accessToken) {

            // Access token is empty, need to get it
            $params = array(
                'client_id' => $this->_getConfig()['app']['id'],
                'scope' => 'wall',
                'display' => 'page',
                'v' => '5.23',
                'response_type' => 'token'
            );

            $uri = 'https://oauth.vk.com/authorize?' . http_build_query($params);

            return $uri;
        }

        return true;
    }

    /**
     * Get access token
     *
     * @return string
     */
    public function getAccessToken()
    {
        return $this->_getValueFromStorage('access_token');
    }

    /**
     * Set access token
     *
     * @param $accessToken
     * @return string $this
     */
    public function setAccessToken($accessToken)
    {
        $this->_getLogger()->write('New access token: ' . $accessToken . ', and old is: ' . $this->getAccessToken());
        $this->_storeValueIntoStorage('access_token', $accessToken);
        return $this;
    }

    /**
     * Get polls
     *
     * @return array|null
     */
    public function getPolls()
    {

        $this->_getLogger()->write('Downloading posts');

        $apiMethodCall = new VKApiMethodCall($this->getAccessToken());
        $result = $apiMethodCall->call(
            'wall.get',
            array()
        );

        return $this->_processPollsRequestResult($result);
    }

    /**
     * Get poll data
     *
     * @param string $pollId
     * @return mixed|null
     */
    public function getPollData($pollId)
    {
        $this->_getLogger()->write('Downloading poll data, poll id: ' . $pollId);

        $apiMethodCall = new VKApiMethodCall($this->getAccessToken());
        $result = $apiMethodCall->call(
            'polls.getById',
            array(
                'poll_id' => $pollId
            )
        );

        if ($this->_isErrorOccurred($result)) {
            return null;
        }

        return $result;
    }

    public function getPollVoters($pollId)
    {

    }

    public function run()
    {
        $this->_getLogger()->write('Hello!');

        var_dump($this->getPolls());

        $this->_getLogger()->write('Goodbye.');
    }

    /**
     * Process polls request result
     *
     * @param array $result
     * @return array|null
     */
    private function _processPollsRequestResult($result)
    {
        if (!$result) {
            return null;
        }

        if ($this->_isErrorOccurred($result)) {
            return null;
        }

        if ($result['response']['count'] == 0) {
            $this->_getLogger()->write('Items count is 0');
            return null;
        }

        $this->_getLogger()->write($result['response']['count'] . ' posts downloaded');

        $polls = array();
        foreach ($result['response']['items'] as $item) {

            if (!isset($item['attachments']) || count($item['attachments']) == 0) {
                continue;
            }

            foreach ($item['attachments'] as $attachment) {
                if ($attachment['type'] == 'poll') {
                    $polls[] = $item;
                    break 2;
                }
            }

        }

        $this->_getLogger()->write(count($polls) . ' posts with one or more polls found');

        return $polls;
    }

    /**
     * Is error occurred
     *
     * @param array $result
     * @return bool
     */
    private function _isErrorOccurred(array $result)
    {
        if (array_key_exists('error', $result)) {
            $this->_getLogger()->write("Error occurred! Code: {$result['error']['error_code']}, message: {$result['error']['error_msg']}");
            return true;
        } else {
            return false;
        }
    }

    /**
     * Get value from kv storage
     *
     * @param string $key
     * @return string
     */
    private function _getValueFromStorage($key)
    {
        $pdo = $this->_getPdo();
        $stmt = $pdo->prepare('SELECT `value` FROM `key_value_storage` WHERE `key` = :key');
        $stmt->execute(array('key' => $key));
        return $stmt->fetchColumn(0);
    }

    /**
     * Store value into storage
     *
     * @param string $key
     * @param string $value
     * @return bool
     */
    private function _storeValueIntoStorage($key, $value)
    {
        $pdo = $this->_getPdo();

        $stmt = $pdo->prepare('SELECT COUNT(`id`) AS `cnt` FROM `key_value_storage` WHERE `key` = :key');
        $stmt->execute(array('key' => $key));
        $exists = $stmt->fetchColumn(0) > 0;

        if ($exists) {
            $stmt = $pdo->prepare('UPDATE `key_value_storage` SET `value` = :value WHERE `key` = :key');
        } else {
            $stmt = $pdo->prepare('INSERT INTO `key_value_storage` (`key`, `value`) VALUES (:key, :value)');
        }

        return $stmt->execute(
            array(
                'key' => $key,
                'value' => $value
            )
        );
    }

    /**
     * Get pdo
     *
     * @return \PDO
     */
    private function _getPdo()
    {
        if ($this->_db === null) {
            $cf = $this->_getConfig()['database'];
            $this->_db = new \PDO("mysql:host={$cf['host']};dbname={$cf['name']}", $cf['login'], $cf['password']);
            $this->_db->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        }

        return $this->_db;
    }

    /**
     * Get configuration
     *
     * @return mixed
     */
    private function _getConfig()
    {
        if ($this->_config === null) {

            $this->_config = require_once(
                __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'config.php'
            );

        }

        return $this->_config;
    }

    /**
     * Get logger
     *
     * @return Logger
     */
    private function _getLogger()
    {
        if ($this->_logger === null) {
            $this->_logger = new Logger(__DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'log.txt');
        }

        return $this->_logger;
    }

} 