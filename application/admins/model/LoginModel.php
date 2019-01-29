<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/1/29
 * Time: 11:26
 */

namespace app\admins\model;


use think\Cache;
use think\Config;
use think\Model;
use think\Session;

class LoginModel extends Model
{
    protected $name='admin';

    public function getLoginInfo($name,$password){
        $key = Config::get('auth_key');
        $info = $this->where('name',$name)->find();
        if(!$info){
            return ['code'=>1012,'msg'=>'账号不存在'];
        }elseif ($info['password'] != md5(md5($password).$key)){
            return ['code'=>1012,'msg'=>'账号或密码错误'];
        }elseif ($info['state'] != 1){
            return ['code'=>1012,'msg'=>'登录账号已冻结'];
        }else{
            Session::set('admin_uid',$info['id']);
            Session::set('admin_name',$info['name']);
            $token = md5(time().$key);
            $ip = request()->ip();
            Cache::set($info['name'].'token',$token,7200);
            $this->where('id',$info['id'])->update(['ip'=>$ip,'end_time'=>time(),'token'=>$token]);
            return ['code'=>1011,'msg'=>'登录成功'];
        }
    }
}