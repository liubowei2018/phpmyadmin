<?php
namespace app\index\controller;

use think\Controller;

class Index extends Controller
{
    public function index()
    {
        $data = request()->get();
        $token="rongdian";
        $sigArr=[$token,$data['timestamp'],$data['nonce']];
        sort($sigArr,SORT_STRING);
        $sigArr = implode($sigArr);
        $enctystr = sha1($sigArr);
        if ($data['signature'] === $enctystr) {
            echo $data['echostr'];
        }else{
            return false;
        }
        return $this->fetch();
     }

    /**
     * 会员注册
     */
     public function register(){
         $phone = input('param.phone');
         $code = request()->get('code');
         $wechatConfig = config('WeChatConfig');
         if(empty($code)){
             $web_url = web_url_str()."Index/Index/register/phone/".$phone;
            $curUrl = urlencode($web_url);
             $url = "https://open.weixin.qq.com/connect/oauth2/authorize?appid={$wechatConfig['app_id']}&redirect_uri={$curUrl}&response_type=code&scope=snsapi_userinfo&state=STATE#wechat_redirect ";
             return redirect($url);
         }
         //使用code 换去accesstoken
         $url = "https://api.weixin.qq.com/sns/oauth2/access_token?appid={$wechatConfig['app_id']}&secret={$wechatConfig['app_security']}&code={$code}&grant_type=authorization_code";
         $result = get_request($url);
         $resultArr = json_decode($result,true);
         $accessToken = $resultArr['access_token'];
         $openId = $resultArr['openid'];
         //请求用户信息
         $userUrl = "https://api.weixin.qq.com/sns/userinfo?access_token={$accessToken}&openid={$openId}&lang=zh_CN";
         $user_info = get_request($userUrl);
         $userInfoArr = json_decode($user_info,true);
         dump($userInfoArr);
         return $this->fetch();
     }
}
