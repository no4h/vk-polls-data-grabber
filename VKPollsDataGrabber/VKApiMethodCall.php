<?php
/**
 * Vkontante API method call
 *
 * @author n04h (contact@n04h.com)
 */

namespace VKPollsDataGrabber;

class VKApiMethodCall {

    /**
     * Curl instance
     *
     * @var resource
     */
    private $_ch;

    /**
     * Access token
     *
     * @var string
     */
    private $_accessToken;

    /**
     * Constructor
     *
     * @param string $accessToken
     */
    public function __construct($accessToken)
    {
        $this->_accessToken = $accessToken;

        // Init curl
        $this->_ch = curl_init();
        curl_setopt_array(
            $this->_ch,
            array(
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_POST => true,
                CURLOPT_USERAGENT => 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/32.0.1700.107'
            )
        );
    }

    /**
     * Destructor
     */
    public function __destruct()
    {
        curl_close($this->_ch);
    }

    /**
     * Call API method
     *
     * @param string $method
     * @param array $params
     * @return mixed
     */
    public function call($method, array $params)
    {
        $params['v'] = '5.23';
        $params['access_token'] = $this->_accessToken;

        return $this->_sendRequest($method, $params);
    }

    /**
     * Send request
     *
     * @param string $method
     * @param array $params
     * @return mixed
     */
    protected function _sendRequest($method, array $params)
    {
        $paramsAsString = http_build_query($params);
        $uri = 'https://api.vk.com/method/' . $method;

        curl_setopt_array(
            $this->_ch,
            array(
                CURLOPT_URL => $uri,
                CURLOPT_POSTFIELDS => $paramsAsString
            )
        );

        $response = curl_exec($this->_ch);

        return json_decode($response, true);
    }
} 