<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/1/25
 * Time: 13:47
 */

namespace app\admins\controller;


use think\Cache;
use think\Controller;
use think\Db;
use think\Session;

class Base extends Controller
{
    public $admin_uid;
    public $admin_name;
    public function _initialize()
    {
        $this->admin_uid = Session::get('admin_uid');
        $this->admin_name = Session::get('admin_name');
        if($this->admin_uid == '' || $this->admin_name == ''){
            $this->redirect('admins/login/index');
        }
        $cache_token = Cache::get($this->admin_name.'token');
        $token = Db::name('admin')->where('id',$this->admin_uid)->value('token');
        if($cache_token != $token){
            Session::delete('admin_uid');
            Session::delete('admin_name');
            $this->redirect('admins/login/index');
        }
    }
}