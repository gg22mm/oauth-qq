<?php
namespace Wll\OauthQq;					//包里会自动省略src

use Illuminate\Support\Facades\Cache;

class Oauth {
   
    
     //授权
    public function oauth($request)
    {       
		//获取QQ配置
        $configServices = config('oauth-qq.qq');		
		$appId = $configServices['client_id'];			//应用的APPID	
		$appSecret = $configServices['client_secret'];	//应用的APPKEY		
		$backUrl = $configServices['redirect'];			//成功授权后的回调地址
		
		//Step1：获取Authorization Code			
		if(empty($request->input('code'))){
		
			//state参数用于防止CSRF攻击，成功授权后回调时会原样带回 //生成key
			$state = md5(uniqid(rand(), TRUE));
			
			//缓存过其它页面的数据
			$data=[];					
			if($request->input('modal')) 		$data['oauthModal'] = 	true;							//是否返回js modal=1是
			if($request->input('bind')) 		$data['bind'] = 		true;							//是否是绑定事件跳转		
			if($request->input('wechat_key')) 	$data['wechat_key'] = 	$request->input('wechat_key');	//是否可回写微信，有key可回写
			if($request->input('inviter_id')) 	$data['inviter_id'] = 	$request->input('inviter_id');	//邀请落地页邀请注册
			if($request->input('type')) 		$data['type'] =  		$request->input('type');		//邀请落地页邀请注册类型
			if($request->input('redirect_url')) $data['redirect_url'] = urlencode($request->input('redirect_url'));//授权回调地址后,再次跳转地址
						
			if($data) Cache::put('qq:oauth:state:' . $state, $data, now()->addMinute(10));//缓存数据10分钟
			 
			//拼接URL
			$oauthUrl = "https://graph.qq.com/oauth2.0/authorize?response_type=code&client_id="
				. $appId . "&redirect_uri=" . urlencode($backUrl) . "&state="
				. $state;
				
			//跳转			
			exit(header('Location: ' . $oauthUrl));
		}
		
    }
	
	//获取用户信息-根据code
	public function getUserInfoByCode($code){
			
		//获取token
		$accessToken=$this->getAccessToken($code);
		
		//获取用户openid
		$openId= $this->getOpenIdByToken($accessToken);
		
		//获取用户信息
		$userInfo= $this->getUserInfoByOpenId($openId->openid,$accessToken);
		
		//附加字段
		$userInfo->openid=$openId->openid;
		$userInfo->id=$openId->openid;
		$userInfo->unionid=$openId->unionid;
		$userInfo->avatar=$userInfo->figureurl_qq_1;
		$userInfo->accessToken=$accessToken;
		
		//返回
		return $userInfo;	
	
	}
	
	
	//获取Access Token
	public function getAccessToken($code){
		
		//Step2：通过Authorization Code获取Access Token
		
		//获取QQ配置
        $configServices = config('oauth-qq.qq');		
		$appId = $configServices['client_id'];			//应用的APPID	
		$appSecret = $configServices['client_secret'];	//应用的APPKEY		
		$backUrl = $configServices['redirect'];			//成功授权后的回调地址
		
		 //拼接URL
		 $getTokenUrl = "https://graph.qq.com/oauth2.0/token?grant_type=authorization_code&"
			 . "client_id=" . $appId . "&redirect_uri=" . urlencode($backUrl)
			 . "&client_secret=" . $appSecret . "&code=" . $code;
		 
		 $response = $this->curlGetContent($getTokenUrl);//access_token=FE04************************CCE2&expires_in=7776000&refresh_token=88E4************************BE14
		 
		 if (strpos($response, "callback") !== false)
		 {
			$lpos = strpos($response, "(");
			$rpos = strrpos($response, ")");
			$response  = substr($response, $lpos + 1, $rpos - $lpos -1);
			$msg = json_decode($response);
			if (isset($msg->error))
			{
			   echo "<h3>error:</h3>" . $msg->error;
			   echo "<h3>msg  :</h3>" . $msg->error_description;
			   exit;
			}
		 }
		 
		$params = [];
		parse_str($response, $params);//把查询字符串解析到变量中//parse_str("name=Bill&age=60"); 	//echo $name."<br>";//Bill 	//echo $age;//60
		
		//返回token	
		return $params['access_token'];	
	
	}
	
	
	
	//获取用户openid-根据token
	public function getOpenIdByToken($accessToken){
	
		 $getOpenIdUrl = "https://graph.qq.com/oauth2.0/me?access_token="	. $accessToken . '&unionid=1';
		 
		 $str  = $this->curlGetContent($getOpenIdUrl);
		 if (strpos($str, "callback") !== false)
		 {
			$lpos = strpos($str, "(");
			$rpos = strrpos($str, ")");
			$str  = substr($str, $lpos + 1, $rpos - $lpos -1);
		 }
		 $user = json_decode($str);
		 if (isset($user->error))
		 {
			echo "<h3>error:</h3>" . $user->error;
			echo "<h3>msg  :</h3>" . $user->error_description;
			exit;
		 }
		 
		 //echo("Hello " . $user->openid);
		 
		 //只能返回open_id
		 return $user;
	
	}
	
	
	//获取用户信息-根据token
	public function getUserInfoByOpenId($openId,$accessToken){
		
		//获取QQ配置
		$configServices = config('oauth-qq.qq');		
		$appId = $configServices['client_id'];			//应用的APPID	
		
		$getUserInfoUrl = "https://graph.qq.com/user/get_user_info?access_token=". $accessToken.'&oauth_consumer_key='.$appId.'&openid='.$openId;
		
		$str  = $this->curlGetContent($getUserInfoUrl);
		
		if (strpos($str, "callback") !== false)
		{
			$lpos = strpos($str, "(");
			$rpos = strrpos($str, ")");
			$str  = substr($str, $lpos + 1, $rpos - $lpos -1);
		}
		
		$user = json_decode($str);
		
		if (isset($user->error))
		{
			echo "<h3>error:</h3>" . $user->error;
			echo "<h3>msg  :</h3>" . $user->error_description;
			exit;
		}
		
		// $user->nickname; $user->figureurl_qq_1;//头像大小为40×40像素的QQ头像URL,不是所有的用户都拥有QQ的100x100的头像，但40x40像素则是一定会有。
		
		//只能返回open_id
		return $user;
	
	}
	
	
	//curl 请求
	private function curlGetContent($url)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_URL, $url);
        //设置超时时间为3s
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
        $result = curl_exec($ch);
        curl_close($ch);
        return $result;
    }
	
	public function test(){
		echo 'dsf';
	}
	
	
}