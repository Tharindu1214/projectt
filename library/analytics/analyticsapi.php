<?php
require_once "config.php";
Class Ykart_analytics
{
	public function __construct($config = array()) 
	{
		global $analyticsApiConfig;
		$analyticsApiConfig = array_merge($analyticsApiConfig, $config);	
		$this->analyticsApiConfig=$analyticsApiConfig;
		
		if($analyticsApiConfig['googleAnalyticsID']=='' || $analyticsApiConfig['clientSecretKey']=='' ||$analyticsApiConfig['clientId']==''){
			throw new Exception('You must provide the Analytic Id,ClientId and Secret Key');
		}
		require_once('GoogleAnalyticsAPI.class.php');
		$this->ga = new GoogleAnalyticsAPI();
		$this->ga->auth->setClientId($analyticsApiConfig['clientId']); 
		// From the APIs console
		$this->ga->auth->setClientSecret($analyticsApiConfig['clientSecretKey']); // From the APIs console
		$this->ga->auth->setRedirectUri($analyticsApiConfig['redirectUri']); // Url to your app, must match one in the APIs console
		$this->endDate=date('Y-m-d');
		$this->todayDate=$this->endDate;
		$this->weekDate=date('Y-m-d',date(strtotime($this->endDate .'-7 days')));
		$this->monthDate=date('Y-m-d',date(strtotime($this->endDate .'-1 month')));
		$this->yearDate=date('Y-m-d',date(strtotime($this->endDate .'-1 year')));
	}
	
		
	public function buildAuthUrl()
	{
		try{
			return $url = $this->ga->auth->buildAuthUrl();
		}catch(exception $e){	
			//throw new Exception($e->getMessage());
		}		
	}
	
	function getAccessToken($code)
	{
		$auth = $this->ga->auth->getAccessToken($code);
		$arr=array();
		if ($auth['http_code'] == 200) {			
			$arr['accessToken'] = $auth['access_token'];
			$arr['refreshToken'] = $auth['refresh_token'];
			$arr['tokenExpires'] = $auth['expires_in'];
			$arr['tokenCreated'] = time();
			$arr['status']=true;			
		}else{
			$arr['status']=false;
			$arr['msg']=$auth['error']['message'];
		}
		return $arr;
	}
	
	function getRefreshToken($token)
	{
		$auth = $this->ga->auth->refreshAccessToken($token);
		
		$arr = array();
		if ($auth['http_code'] == 200) {			
			$arr['accessToken'] = $auth['access_token'];
			$arr['refreshToken'] = isset($auth['refresh_token'])?$auth['refresh_token']:null;
			$arr['tokenExpires'] = $auth['expires_in'];
			$arr['tokenCreated'] = time();
			$arr['status'] = true;			
		}else{
			$arr['status'] = false;
			$arr['msg'] = isset($auth['error']['message'])?$auth['error']['message']:'';
		}	
		
		return $arr;
	}
	
	function getAccessTokenExpiration($token)
	{
		return $res=file_get_contents('https://www.googleapis.com/oauth2/v1/tokeninfo?access_token='.$token);		
	}
	
	public function setAccessToken($accessToken)
	{
		$this->ga->setAccessToken($accessToken);
	}	
			
	public function setAccountId($gooleAnalyticsID)
	{		
		$profiles = $this->getProfiles();
		$exist=false;	
		if($profiles['status']==true){
			foreach ($profiles['result']['items'] as $item) {
				if($item['webPropertyId']==$gooleAnalyticsID){
					$id = "ga:{$item['id']}";
					$exist=true;
					break;	
				}
			}			
			if($exist){ 
				$this->ga->setAccountId($id);
				return true;
			}else{
				return false;
			}
		}		
	}
	
	public function getProfiles()
	{
		$profiles = $this->ga->getProfiles();
		$row=array('status'=>false);		
		if($profiles['http_code']==200){
			$row['status']=true;
			$row['result']=$profiles;
		}else{			
			$row['msg']=$profiles['error']['message'];
		}
		return $row;
	}
	
	public function setDefaultQueryParams(array $params)
	{
		return $query=$this->ga->setDefaultQueryParams($params);			
	}
	
	public function query($params=array()) {
		$query= $this->ga->query($params);
		$row=array('status'=>false);		
		if($query['http_code']==200){
			$row['status']=true;
			$row['result']=$query;
		}else{			
			$row['msg']=$query['error']['message'];
		}
		return $row;
	}
	
	public function getVisitsByDate($defaults=array())
	{
		$resultArr=array();
		$params = array(
			'metrics' => 'ga:visits',
			'dimensions' => 'ga:date',
		);
		
		if(!empty($defaults)){
			$this->setDefaultQueryParams($defaults);
			return $visits = $this->query($params);
		}else{
			$endDate=date('Y-m-d');
			$todayStartDate=date('Y-m-d');
			$thisWeekStartDate=date('Y-m-d',date(strtotime($endDate .'-7 days')));
			$thisMonthStartDate=date('Y-m-d',date(strtotime($endDate .'-1 Month')));
			$last3MonthStartDate=date('Y-m-d',date(strtotime($endDate .'-3 Month')));			
			$statArr=array('todayStartDate'=>'today','thisWeekStartDate'=>'weekly','thisMonthStartDate'=>'lastMonth','last3MonthStartDate'=>'last3Month');
			$stats=array();	
			foreach($statArr as $key=>$res){
				$defaults = array('start-date' => $$key,'end-date' => $endDate);
				$this->setDefaultQueryParams($defaults);
				$visits = $this->query($params);
				
				$result = array();
				if($visits['status'] == true && $visits['result']['http_code'] == 200){
					$result['totalsForAllResults'] = $visits['result']['totalsForAllResults']['ga:visits'];	
					if(isset($visits['result']['rows'])){
						
						foreach($visits['result']['rows'] as $val){
							$result['rows'][$val[0]]['visit'] = $val[1];
							$result['rows'][$val[0]]['%age'] = ($result['totalsForAllResults'] > 0)?round(($val[1]*100)/$result['totalsForAllResults'],2):0;
							$stats[$val[0]][$res]['visit'] = $val[1];
							$stats[$val[0]][$res]['%age'] = ($result['totalsForAllResults'] > 0)? round(($val[1]*100)/$result['totalsForAllResults'],2):0;
						}			
					}
				}
				$resultArr['result'][$res] = $result;								
			}
			$resultArr['stats'] = $stats;	
		}		
		return $resultArr;
	}
	
	public function getTopCountries($type='TODAY',$limit=10)
	{
		$params = array(
			'metrics' => 'ga:visits',
			'dimensions' => 'ga:country',
			'sort' => '-ga:visits',
			'max-results' => $limit			 
		); 
		switch(strtoupper($type)){
			case 'TODAY':
				$params['start-date']=$this->todayDate;				
			break;
			case 'WEEKLY':
				$params['start-date']=$this->weekDate;
			break;
			case 'MONTHLY':
				$params['start-date']=$this->monthDate;				
			break;
			case 'YEARLY':
				$params['start-date']=$this->yearDate;				
			break;
		}		
		$visitsByCountry = $this->query($params);
		$result=array();
		if($visitsByCountry['status']==true && $visitsByCountry['result']['http_code']==200){
			$result['totalsForAllResults']=$visitsByCountry['result']['totalsForAllResults']['ga:visits'];
			if(isset($visitsByCountry['result']['rows'])){
				
				foreach($visitsByCountry['result']['rows'] as $val){
					$result['rows'][$val[0]]['visit']=$val[1];
					$result['rows'][$val[0]]['%age']=round(($val[1]*100)/$result['totalsForAllResults'],2);
				}			
			}
		}		
		return $result;
	}
	
	public function getTopReferrers($type='TODAY',$limit=10)
	{
		$params = array(
			'metrics' => 'ga:visits',
			'dimensions' => 'ga:source',
			'sort' => '-ga:visits',
			'max-results' => $limit			 
		); 
		switch(strtoupper($type)){
			case 'TODAY':
				$params['start-date']=$this->todayDate;				
			break;
			case 'WEEKLY':
				$params['start-date']=$this->weekDate;
			break;
			case 'MONTHLY':
				$params['start-date']=$this->monthDate;				
			break;
			case 'YEARLY':
				$params['start-date']=$this->yearDate;				
			break;
		}
		$topReferrers = $this->query($params);
		$result=array();
		if($topReferrers['status']==true && $topReferrers['result']['http_code']==200){
			$result['totalsForAllResults']=$topReferrers['result']['totalsForAllResults']['ga:visits'];
			if(isset($topReferrers['result']['rows'])){
				foreach($topReferrers['result']['rows'] as $val){
					$result['rows'][$val[0]]['visit']=$val[1];
					$result['rows'][$val[0]]['%age']=round(($val[1]*100)/$result['totalsForAllResults'],2);
				}	
			}
		}else{
			$result=$topReferrers;
		}
		return $result;
	}
	
	public function getTrafficSource($type='TODAY')
	{
		$arr=array('(none)'=>'Direct','organic'=>'Search Engine','referral'=>'Referral','cpc'=>'cpc');
		$params = array(
			'metrics' => 'ga:visits',
			'dimensions' => 'ga:medium',
			'sort' => '-ga:visits'	 
		); 
		switch(strtoupper($type)){
			case 'TODAY':
				$params['start-date']=$this->todayDate;				
			break;
			case 'WEEKLY':
				$params['start-date']=$this->weekDate;
			break;
			case 'MONTHLY':
				$params['start-date']=$this->monthDate;				
			break;
			case 'YEARLY':
				$params['start-date']=$this->yearDate;				
			break;
		}
		$trafficSource = $this->query($params);
		$result=array();
		if($trafficSource['status']==true && $trafficSource['result']['http_code']==200){
			$result['totalsForAllResults']=$trafficSource['result']['totalsForAllResults']['ga:visits'];
			if(isset($trafficSource['result']['rows'])){
				foreach($trafficSource['result']['rows'] as $val){
					if(!array_key_exists($val[0],$arr)){continue;}
					$key = str_replace($val[0],$arr[$val[0]],$val[0]);
					$result['rows'][$key]['visit']=$val[1];
					$result['rows'][$key]['%age']=round(($val[1]*100)/$result['totalsForAllResults'],2);
				}	
			}
		}else{
			$result=$trafficSource;
		}
		return $result;
	}
	
	public function getSocialVisits()
	{		
		$params = array(
			'metrics' => 'ga:visits',
			'dimensions' => 'ga:socialNetwork',
			'sort' => '-ga:visits'	 
		); 		
		$socialVisits = $this->query($params);
		$result=array();
		if($socialVisits['status']==true && $socialVisits['result']['http_code']==200){
			$result['totalsForAllResults']=$socialVisits['result']['totalsForAllResults']['ga:visits'];
			if(isset($socialVisits['result']['rows'])){
				
				foreach($socialVisits['result']['rows'] as $val){				
					$result['rows'][$val[0]]['visit']=$val[1];
					$result['rows'][$val[0]]['%age']=round(($val[1]*100)/$result['totalsForAllResults'],2);
				}			
			}
		}else{
			$result=$socialVisits;
		}
		return $result;
	}	
	
	public function getSearchTerm($type='TODAY',$limit=10)
	{
		$params = array(
			'metrics' => 'ga:searchResultViews',
			'dimensions' => 'ga:searchKeyword',
			'sort' => '-ga:searchResultViews',
			'max-results' => $limit			
		);
		switch(strtoupper($type)){
			case 'TODAY':
				$params['start-date']=$this->todayDate;				
			break;
			case 'WEEKLY':
				$params['start-date']=$this->weekDate;
			break;
			case 'MONTHLY':
				$params['start-date']=$this->monthDate;				
			break;
			case 'YEARLY':
				$params['start-date']=$this->yearDate;				
			break;
		}
		$searchTerm = $this->query($params);			
		$result=array();
		if($searchTerm['status']==true && $searchTerm['result']['http_code']==200){
			$result['totalsForAllResults']=$searchTerm['result']['totalsForAllResults']['ga:searchResultViews'];
			foreach($searchTerm['result']['rows'] as $val){				
				$result['rows'][$val[0]]['count']=$val[1];
				$result['rows'][$val[0]]['%age']=round(($val[1]*100)/$result['totalsForAllResults'],2);
			}			
		}else{
			$result=$searchTerm;
		}
		return $result;
	}
}