<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/1/25
 * Time: 13:46
 */

namespace app\admins\controller;


use think\Session;

class Index extends Base
{
    public function index(){
        $webConf = getWebConfigList();
        $this->assign('webConf',$webConf);
        return $this->fetch();
    }
    /**
     * 首页
     */
    public function index_detail(){

        return $this->fetch();
    }

    //退出登录
    public function logout(){
        Session::delete('admin_uid');
        Session::delete('admin_name');
        $this->redirect('admins/Login/index');
    }
}