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
         $config = config('');
         if(empty($code)){
             $web_url = web_url_str()."Index/Index/register/phone/".$phone;
            $curUrl = urlencode($web_url);
            dump($curUrl);
         }
         return $this->fetch();
     }
}
