<?php
/**
 * Created by PhpStorm.
 * User: liubowei
 * Date: 2019/2/19
 * Time: 9:12
 */

namespace app\Interactive\controller;


use app\Interactive\model\MemberModel;
use think\Cache;
use think\Controller;


class Login extends Controller
{
    /**
     * 登入系统
     */
    public function entry(){
        $data = input('post.');
        $key = config('auth_key');
        $config_list = app_config_list();
        //验证系统是否使用
        if($config_list['app_state'] != 1){
            return json(['code'=>1014,'msg'=>'系统维护中']);
        }
        //验证数据
        $validate_res = $this->validate($data,'LoginValidate.entry');
        if($validate_res !== true){
            return json(['code'=>1015,'msg'=>$validate_res]);
        }
        if(getSign($data) != $data['Sign']){
            return json(['code'=>1013,'msg'=>'签名错误']);
        }
        $MemberModel = new MemberModel();
        return json($MemberModel->getMemberLogin($data,1));
    }
    /**
     * 手机号登录
     */
    public function mobile_entry(){
        $data = input('post.');
        $key = config('auth_key');
        $config_list = app_config_list();
        //验证系统是否使用
        if($config_list['app_state'] != 1){ return json(['code'=>1014,'msg'=>'系统维护中']); }
        //验证数据
        $validate_res = $this->validate($data,'LoginValidate.mobile_entry');
        if($validate_res !== true){ return json(['code'=>1015,'msg'=>$validate_res]); }
        if(getSign($data) != $data['Sign']){ return json(['code'=>1013,'msg'=>'签名错误']);}
        if(Cache::get($data['phone'].'_entry_sms') != $data['sms_code']){
            return json(['code'=>1013,'msg'=>'短信验证码错误']);
        }
        $MemberModel = new MemberModel();
        $uuid = $MemberModel->getMemberInfo('uuid',['mobile'=>$data['phone']]);
        $data['uuid'] = $uuid['uuid'];
        return json($MemberModel->getMemberLogin($data,2));
    }
}