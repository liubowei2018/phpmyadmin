<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/1/25
 * Time: 13:46
 */

namespace app\admins\controller;


use think\Db;
use think\Session;

class Index extends Base
{
    public function index(){
        $webConf = getWebConfigList();
        $group = Db::name('auth_group')->alias('g')->field('g.title')->where('a.uid',$this->admin_uid)->join('auth_group_access a','a.group_id = g.id')->find();
        $this->assign('name',$this->admin_name);
        $this->assign('title',$group['title']);
        $this->assign('webConf',$webConf);
        return $this->fetch();
    }
    /**
     * 首页
     */
    public function index_detail(){
        $webConf = getWebConfigList();
        $this->assign('webConf',$webConf);
        return $this->fetch();
    }

    //退出登录
    public function logout(){
        Session::delete('admin_uid');
        Session::delete('admin_name');
        $this->redirect('admins/Login/index');
    }
}