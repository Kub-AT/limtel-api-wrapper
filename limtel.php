<?php

/**
 * Simple Limtel API wrapper.
 *
 * @author  Jakub Stawowy <Kub-AT@users.noreply.github.com>
 * @license The MIT License (MIT)
 * @version 0.5
 * @link    https://github.com/Kub-AT/limtel-api-wrapper
 */
class Limtel
{
    private $url = 'http://www.limtel.pl/api/api_listener.php';
    private $authkey;
    private $email;
    private $pass;
    private $curlHdl;

    /**
     * Create a new instance.
     *
     * @param string $email   Your Limtel email
     * @param string $pass    MD5 hash of a password for your account Limtel
     * @param string $authkey Your Limtel authkey
     */
    function __construct($email, $pass, $authkey)
    {
        $this->email = $email;
        $this->pass = $pass;
        $this->authkey = $authkey;
        $this->curlHdl = null;
        $this->userId = null;
    }

    /**
     * Get API Url with autkey.
     *
     * @return string API url with authkey in query string
     */
    protected function getUrl()
    {
        return $this->url . '?key=' . $this->authkey;
    }

    /**
     * Generating XML
     * This method does not use the SimpleXMLElement class because 
     * it must be a structure without a tag <XML>.
     *
     * @param array $params Params array
     *
     * @return string XML
     */
    protected function generateXML($params)
    {
        $xml = '<limtel>';
        foreach ($params as $tag => $val)
            $xml .= "<{$tag}>{$val}</{$tag}>";
        $xml .= '</limtel>';
        return $xml;
    }

    /**
     * Curl request.
     *
     * @param array $xml XML - POST date
     *
     * @return string XML API response.
     */
    protected function curl($xml)
    {
        if ($this->curlHdl === null) {
            $this->curlHdl = curl_init();
            curl_setopt($this->curlHdl, CURLOPT_URL, $this->getUrl());
            curl_setopt($this->curlHdl, CURLOPT_HEADER, 0); 
            curl_setopt($this->curlHdl, CURLOPT_TIMEOUT, 10);
            curl_setopt($this->curlHdl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($this->curlHdl, CURLOPT_POST, true);
        }
        curl_setopt($this->curlHdl, CURLOPT_POSTFIELDS, 'dane=' . $xml);

        $response = curl_exec($this->curlHdl);

        return $response;

    }

    /**
     *  Interprets a string of XML into an object.
     *
     * @param string $response XML
     *
     * @return object Object of class SimpleXMLElement, FALSE on failure.
     */
    protected function parseResponse($response)
    {
        return simplexml_load_string($response);
    }

    /**
     * Basic parameters that are added to each request.
     *
     * @return array Basic parameters
     */
    protected function getBasicParams()
    {
        $basicParams = array('authkey' => $this->authkey, 'email' => $this->email, 'pass' => $this->pass);
        if ($this->userid) {
            $staticParams['idu'] = $this->userId; 
        }
        
        return $basicParams;
    }

    /**
     * HTTP request.
     *
     * @param string $action The API action to be called
     * @param array  $params Assoc array of parameters to be passed
     *
     * @return array Object of class SimpleXMLElement
     */
    protected function doRequest($action, $params=array())
    {
        $parameters = array('akcja' => $action) + $this->getBasicParams() + $params;

        $xml = $this->generateXML($parameters);
        $xmlResp = $this->curl($xml);
        $response = $this->parseResponse($xmlResp);

        if (!empty($response) 
            AND (($response->system AND $response->system->status == 1) 
            OR $response->status ==1)
        ) {
            return $response;
        }

        throw new Exception("Response API Error, Action: [$action]");
    }

    /**
     * Call an API action/method.
     *
     * @param string $action The API method to call, e.g. 'history'
     * @param array  $args   An array of arguments to pass to the method.
     *
     * @return array Object of class SimpleXMLElement
     */
    public function __call($action, $args=array()) 
    {
        $params = (isset($args[0])) ? $args[0] : array();
        return $this->doRequest($action, $params);
    }

    /**
     * Login Action.
     * After this action <idu> tag with user id
     * will be added to all the other actions.
     *
     * @return boolean
     */
    public function login()
    {
        $response = $this->doRequest('login');
        if ($response) {
            $this->userId = $response->data->userid;
            return true;
        } else {
            $this->userId = null;
            return false;
        }
    }
}