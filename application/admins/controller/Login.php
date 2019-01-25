<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/1/25
 * Time: 13:43
 */

namespace app\admins\controller;


use think\Controller;
use think\captcha\Captcha;
class Login extends Controller
{
    /**
     * 登陆页面
     * @return mixed
     */
    public function index(){
        $webConf = getWebConfigList();
        $this->assign('webConf',$webConf);
        if($webConf['web_login_view'] == 1){
            return $this->fetch();
        }else{
            return $this->fetch('index_v1');
        }
    }

    public function entry(){
        $param = input('post.');
        $result = $this->validate($param,'');
        if($result !== true){
            return json(['code'=>1012,'msg'=>$result]);
        }
    }

    public function verification_code(){
        $captcha = new Captcha();
        $captcha->codeSet = '234567890QWERTYUIOPASDFGHJKLZXCVBNM';
        $captcha->fontSize = 15;
        $captcha->useCurve   = false;
        $captcha->length   = 4;
        $captcha->fontttf   = '5.ttf';
        $captcha->useNoise = false;
        return $captcha->entry();

    }
}