<?php
namespace Adcuz\FatSecret;

class Client {

	static public $base = 'http://platform.fatsecret.com/rest/server.api?';
	
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
	function GetKey(){
		return $this->_consumerKey;
	}
	
	function SetKey($consumerKey){
		$this->_consumerKey = $consumerKey;
	}

	function GetSecret(){
		return $this->_consumerSecret;
	}
	
	function SetSecret($consumerSecret){
		$this->_consumerSecret = $consumerSecret;
	}
	
	/* Public Methods */
	/* Create a new profile with a user specified ID
	* @param userID {string} Your ID for the newly created profile (set to null if you are not using your own IDs)
	* @param token {string} The token for the newly created profile is returned here
	* @param secret {string} The secret for the newly created profile is returned here
	*/
	function ProfileCreate($userID, &$token, &$secret){
		$url = Client::$base . 'method=profile.create';
		
		if(!empty($userID)){
			$url = $url . '&user_id=' . $userID;
		}

		$oauth = new OAuthBase();

		$normalizedUrl;
		$normalizedRequestParameters;
		
		$signature = $oauth->GenerateSignature($url, $this->_consumerKey, $this->_consumerSecret, null, null, $normalizedUrl, $normalizedRequestParameters);
		
		$doc = new SimpleXMLElement($this->GetQueryResponse($normalizedUrl, $normalizedRequestParameters . '&' . OAuthBase::$OAUTH_SIGNATURE . '=' . urlencode($signature)));

		$this->ErrorCheck($doc);

		$token = $doc->auth_token;
		$secret = $doc->auth_secret;
	}
	
	/* Get the auth details of a profile
	* @param userID {string} Your ID for the profile
	* @param token {string} The token for the profile is returned here
	* @param secret {string} The secret for the profile is returned here
	*/
	function ProfileGetAuth($userID, &$token, &$secret){
		$url = Client::$base . 'method=profile.get_auth&user_id=' . $userID;
		
		$oauth = new OAuthBase();
		
		$normalizedUrl;
		$normalizedRequestParameters;
		
		$signature = $oauth->GenerateSignature($url, $this->_consumerKey, $this->_consumerSecret, null, null, $normalizedUrl, $normalizedRequestParameters);
		
		$doc = new SimpleXMLElement($this->GetQueryResponse($normalizedUrl, $normalizedRequestParameters . '&' . OAuthBase::$OAUTH_SIGNATURE . '=' . urlencode($signature)));
		
		$this->ErrorCheck($doc);
		
		$token = $doc->auth_token;
		$secret = $doc->auth_secret;
	}
	
	/* Create a new session for JavaScript API users
	* @param auth {array} Pass user_id for your own ID or the token and secret for the profile. E.G.: array(user_id=>"user_id") or array(token=>"token", secret=>"secret")
	* @param expires {int} The number of minutes before a session is expired after it is first started. Set this to 0 to never expire the session. (Set to any value less than 0 for default)
	* @param consumeWithin {int} The number of minutes to start using a session after it is first issued. (Set to any value less than 0 for default)
	* @param permittedReferrerRegex {string} A domain restriction for the session. (Set to null if you do not need this)
	* @param cookie {bool} The desired session_key format
	* @param sessionKey {string} The session key for the newly created session is returned here
	*/
	function ProfileRequestScriptSessionKey($auth, $expires, $consumeWithin, $permittedReferrerRegex, $cookie, &$sessionKey){
		$url = Client::$base . 'method=profile.request_script_session_key';
		
		if(!empty($auth['user_id'])){
			$url = $url . '&user_id=' . $auth['user_id'];
		}
		
		if($expires > -1){
			$url = $url . '&expires=' . $expires;
		}

		if($consumeWithin > -1){
			$url = $url . '&consume_within=' . $consumeWithin;
		}

		if(!empty($permittedReferrerRegex)){
			$url = $url . '&permitted_referrer_regex=' . $permittedReferrerRegex;
		}

		if($cookie == true)
			$url = $url . "&cookie=true";
			
		$oauth = new OAuthBase();
		
		$normalizedUrl;
		$normalizedRequestParameters;
		
		$signature = $oauth->GenerateSignature($url, $this->_consumerKey, $this->_consumerSecret, $auth['token'], $auth['secret'], $normalizedUrl, $normalizedRequestParameters);
		
		$doc = new SimpleXMLElement($this->GetQueryResponse($normalizedUrl, $normalizedRequestParameters . '&' . OAuthBase::$OAUTH_SIGNATURE . '=' . urlencode($signature)));
				
		$this->ErrorCheck($doc);
				
		$sessionKey = $doc->session_key;
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
		
		return $response;
	}
	
	private function ErrorCheck($doc){
		if($doc->getName() == 'error')
		{
			throw new FatSecretException((int)$doc->code, $doc->message);
		}
	}
}