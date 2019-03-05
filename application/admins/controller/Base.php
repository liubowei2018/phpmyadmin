<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/1/25
 * Time: 13:47
 */

namespace app\admins\controller;


use app\admins\model\Node;
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
        //验证权限
        $auth = new \com\Auth();
        $module     = strtolower(request()->module());
        $controller = strtolower(request()->controller());
        $action     = strtolower(request()->action());
        $url        = $module."/".$controller."/".$action;
        //跳过检测以及主页权限
        if($this->admin_uid!=1){
            if(!in_array($url, ['admins/index/index','admins/index/index_detail','admins/uploads/article','admins/index/logout'])){
                if(!$auth->check($url,session('admin_uid'))){
                    $this->error('抱歉，您没有操作权限');
                }
            }
        }
        $node = new Node();
        $this->assign([
            'username'=>$this->admin_name,
            'rolename'=>Session::get('rolename'),
            'menu'=>$node->getMenu(Session::get('rule'))
        ]);
    }
}