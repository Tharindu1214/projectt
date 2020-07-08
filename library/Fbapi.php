<?php
require_once 'APIs/fb-graph-sdk-5.4/src/Facebook/autoload.php';
use Facebook\Facebook;

class Fbapi{
	public $fbObj;
	 
	public function __construct($configArr = array(),$accessToken ='') { 
		$config = array(
			'app_id'=>'',
			'app_secret'=>'',
			'default_graph_version'=>'v2.8'
		);	
		if($accessToken !=''){
			$config['default_access_token'] = $accessToken;
			$this->accessToken = $accessToken;
		}
		
		$config = array_merge($config,$configArr);		
		$this->fbObj = new Facebook($config);
	}
	
	public function getInstance(){
		return $this->fbObj;
	}
	
	public function setAccessToken($accessToken){
		$this->accessToken = $accessToken;
	}
	
	public function setRedirectUrl($url){
		$this->redirectUrl = $url;
	}
			
	public function getRedirectLoginHelper(){
		return $helper = $this->fbObj->getRedirectLoginHelper();		
	}
	
	public function getLoginUrl($redirectUrl){
		$helper = $this->fbObj->getRedirectLoginHelper();
		$permissions = array('email','publish_actions','user_about_me','user_friends');
		return $fbLoginUrl = $helper->getLoginUrl($redirectUrl, $permissions);			
	}
	
	/* public function getAccessToken(){
		$helper = $this->fbObj->getRedirectLoginHelper();
		$msg = '';
		$data = array('status'=>false,'msg'=>'','listing'=>'');
		
		try {
		   $accessToken = $helper->getAccessToken();		   	   
		   $data = array('status'=>true,'accessToken'=>$accessToken);	
		} catch(Facebook\Exceptions\FacebookResponseException $e) {
		  $msg = $e->getMessage();
		} catch(Facebook\Exceptions\FacebookSDKException $e) {
		  $msg = $e->getMessage();
		}
		
		$data['msg'] = $msg;
		return $data;
	}
	
	public function getLongLivedAccessToken($accessToken){
		$token = '';
		$data = array('status'=>false,'msg'=>'','listing'=>'');
		
		if (! $accessToken->isLongLived()) {
			// The OAuth 2.0 client handler helps us manage access tokens
			$oAuth2Client = $fb->getOAuth2Client();

			try {
				$accessToken = $oAuth2Client->getLongLivedAccessToken($accessToken);
				$data = array('status'=>true,'accessToken'=>$accessToken->getValue());
			} catch (Facebook\Exceptions\FacebookSDKException $e) {
				$msg = $e->getMessage();				
			}
		}else{
			$token = $accessToken->getValue();
		}
		
		$data['msg'] = $msg;
		$data['accessToken'] = $token;
		
		return $data;
	} */
		
	public function getFriends(){
		$msg = '';
		$friendList = array();
		$data = array('status'=>false,'msg'=>'','listing'=>'');
		
		try {
		  // Returns a `Facebook\FacebookResponse` object
		  $response = $this->fbObj->get('/me/friends?fields=id,name', $this->accessToken);
		  $graphEdge = $response->getGraphEdge();
			foreach ($graphEdge as $graphNode) {
				$friendList[] = $graphNode->asArray(); 
			}
		  $data = array('status'=>true,'listing'=>$friendList);
		} catch(FacebookResponseException $e) {
			$msg = $e->getMessage();
		} catch(FacebookSDKException $e) {
			$msg = $e->getMessage();
		}
		
		$data['msg'] = $msg;
		return $data;
	}
}