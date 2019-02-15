<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/1/25
 * Time: 13:43
 */

namespace app\admins\controller;


use app\admins\model\LoginModel;
use think\Controller;
use think\captcha\Captcha;
use think\Config;
use think\Session;
class Login extends Controller
{
    public function _initialize()
    {
        if(Session::get('admin_uid') &&  Session::get('admin_name') ){
            $this->redirect('admins/index/index');
        }
    }
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
        $result = $this->validate($param,'LoginValidate.entry');
        if($result !== true){
            return json(['code'=>1012,'msg'=>$result]);
        }
        if(!captcha_check($param['pin'])){
            return json(['code'=>1012,'msg'=>'验证码错误']);
        }
        //查询账户是否存在
        $LoginModel = new LoginModel();
        $res = $LoginModel->getLoginInfo($param['adm'],$param['pw']);
        return json($res);
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