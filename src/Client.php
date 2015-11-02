<?php

namespace Adcuz\FatSecret;

class Client {

    static public $base = 'http://platform.fatsecret.com/rest/server.api?format=json&';

    /* Private Data */
    private $_consumerKey;
    private $_consumerSecret;

    /* Constructors */

    function __construct($consumerKey = false, $consumerSecret = false) {
        $this->_consumerKey = $consumerKey;
        $this->_consumerSecret = $consumerSecret;
        return $this;
    }

    /* Properties */

    function GetKey() {
        return $this->_consumerKey;
    }

    function SetKey($consumerKey) {
        $this->_consumerKey = $consumerKey;
    }

    function GetSecret() {
        return $this->_consumerSecret;
    }

    function SetSecret($consumerSecret) {
        $this->_consumerSecret = $consumerSecret;
    }

    function SearchFood($query, $region = false, $language = false) {
        
        $url = Client::$base . 'method=foods.search';

        $url = $url . '&search_expression=' . urlencode($query);
        
        if ($region !== false) {
            $url = $url . '&region=' . urlencode($region);
        }
        
        if ($language !== false) {
            $url = $url . '&language=' . urlencode($language);
        }

        $oauth = new OAuthBase();

        $normalizedUrl;
        $normalizedRequestParameters;

        $signature = $oauth->GenerateSignature($url, $this->_consumerKey, $this->_consumerSecret, ''/* token */, ''/* secret */, $normalizedUrl, $normalizedRequestParameters);

        $response = $this->GetQueryResponse($normalizedUrl, $normalizedRequestParameters . '&' . OAuthBase::$OAUTH_SIGNATURE . '=' . urlencode($signature));
        
        return $response;
        
    }

    function GetFood($foodId, $subCats = false, $flagDefaultServing = false) {
        $url = Client::$base . 'method=food.get';

        $url = $url . '&food_id=' . $foodId;

        if ($subCats) {
            $url = $url . '&include_sub_categories=true';
        }

        if ($flagDefaultServing) {
            $url = $url . '&flag_default_serving=true';
        }

        $oauth = new OAuthBase();

        $normalizedUrl;
        $normalizedRequestParameters;

        $signature = $oauth->GenerateSignature($url, $this->_consumerKey, $this->_consumerSecret, ''/* token */, ''/* secret */, $normalizedUrl, $normalizedRequestParameters);

        $response = $this->GetQueryResponse($normalizedUrl, $normalizedRequestParameters . '&' . OAuthBase::$OAUTH_SIGNATURE . '=' . urlencode($signature));
        
        return $response;
    }

    /* Private Methods */

    private function GetQueryResponse($requestUrl, $postString) {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $requestUrl);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postString);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($ch);

        curl_close($ch);

        $response = json_decode($response);
        
        $this->ErrorCheck($response);
        
        return $response;
    }

    private function ErrorCheck($response) {
        if (isset($response->error)) {
            throw new FatSecretException($response->error->message, (int) $response->error->code);
        }
    }

}
