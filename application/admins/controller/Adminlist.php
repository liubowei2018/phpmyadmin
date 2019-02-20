<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/2/11
 * Time: 14:25
 */

namespace app\admins\controller;


use app\admins\model\AdminModel;
use app\admins\model\MenuModel;
use app\admins\model\Node;
use app\admins\model\UserType;
use think\Db;

class Adminlist extends Base
{
    /**
     * 用户列表
     */
    public function user_list(){
        if(request()->isPost()){
            $name = input('post.key');
            $map = [];
            if($name){
                $map['name']=$name;
            }
            $AdminModel = new AdminModel();
            $page = input('get.page') ? input('get.page'):1;
            $rows = input('get.rows');// 获取总条数
            $lists = $AdminModel->getUserList($map,$page,$rows);
            return json($lists);
        }
        return $this->fetch();
    }

    /**
     * 添加管理用户
     */
    public function user_add(){
        if(request()->isPost()){
            $data = input('post.');
            dump($data);
        }
        $UserType = new UserType();
        $typelist = $UserType->getRole();
        $this->assign('typelist',$typelist);
        return $this->fetch();
    }

    /**
     * 删除管理用户
     */
    public function user_del(){
        $data = input('post.');
        dump($data);
    }
    /*角色  配置*/
    public function role_list(){
        $UserType = new UserType();
        if(request()->isPost()){
            $map = [];
            $page = input('get.page') ? input('get.page'):1;
            $rows = input('get.rows');// 获取总条数
            $count = Db::name('auth_group')->where($map)->count();
            $list = $UserType->getRoleByWhere($map,$page,$rows);
            return json(['count'=>$count,'list'=>$list,'page'=>$page]);
        }
        return $this->fetch();
    }

    /**
     * 添加角色
     */
    public function add_role(){
        $UserType = new UserType();
        if(request()->isPost()){
            $data = input('post.');
            $count = Db::name('auth_group')->where('title',$data['title'])->count();
            if($count > 0){
                return json(['code'=>1012,'msg'=>'角色已存在']);
            }else{
                $res = $UserType->insertRole($data);
                return json($res);
            }
        }
        return $this->fetch();
    }
    /**
     * 编辑角色
     */
    public function edit_role(){
        $UserType = new UserType();
        if(request()->isPost()){
            $data = input('post.');
            $res = $UserType->editRole($data);
            return json($res);
        }
        $id = input('get.id');
        $info = $UserType->getOneRole($id);
        $this->assign('info',$info);
        return $this->fetch();
    }
    /**
     * 删除角色
     */
    public function del_role(){
        $data = input('post.id');
        $UserType = new UserType();
        $res = $UserType->delRole($data);
        return json($res);
    }
    /**
     * 修改角色状态
     */
    public function state_role(){
        $data = input('post.id');
        $UserType = new UserType();
        $res = $UserType->stateRole($data);
        return json($res);
    }

    /**
     * 权限分配
     */
    public function giveAccess(){
        $param = input('param.');
        $node = new Node();
        //获取现在的权限
        if('get' == $param['type']){
            $nodeStr = $node->getNodeInfo($param['id']);
            return json(['code' => 1, 'data' => $nodeStr, 'msg' => 'success']);
        }
        //分配新权限
        if('give' == $param['type']){

            $doparam = [
                'id' => $param['id'],
                'rules' => $param['rule']
            ];
            $user = new UserType();
            $flag = $user->editAccess($doparam);
            return json(['code' => $flag['code'], 'data' => $flag['data'], 'msg' => $flag['msg']]);
        }
    }
    /*------------------------------------------菜单管理--------------------------------------------------------*/
    /**
     * 菜单列表
     */
    public function menu_list(){
        $MenuModel = new MenuModel();
        if(request()->isPost()){
            $data = input('post.');
            $map = [];
            //$map['pid'] = 0;
            $page = input('get.page') ? input('get.page'):1;
            $rows = input('get.rows');// 获取总条数
            $count = Db::name('auth_rule')->where($map)->count();
            $list = $MenuModel->getAllMenu($map,$page,$rows);
            return json(['count'=>$count,'list'=>$list,'page'=>$page]);
        }
        return $this->fetch();
    }
    /**
     * 添加菜单
     */
    public function add_menu(){
        $MenuModel = new MenuModel();
        if(request()->isPost()){
            $data = input('post.');
            $res = $MenuModel->insertMenu($data);
            return json($res);
        }
        $list = $MenuModel->getAllMenu([],1,1000);
        $this->assign('list',$list);
        return $this->fetch();
    }
    /**
     * 编辑菜单
     */
    public function edit_menu(){
        $MenuModel = new MenuModel();
        if(request()->isPost()){
            $data = input('post.');
            $res = $MenuModel->editMenu($data);
            return json($res);
        }
        $id = input('get.id');
        $menu_info = $MenuModel->getOneMenu($id);
        $list = $MenuModel->getAllMenu([],1,1000);
        $this->assign('info',$menu_info);
        $this->assign('list',$list);
        return $this->fetch();
    }
    /**
     * 菜单状态
     */
    public function state_menu(){
        $data = input('post.id');
        $MenuModel = new MenuModel();
        $res = $MenuModel->statusMenu($data);
        return json($res);
    }
    /**
     *删除菜单
     */
    public function del_menu(){
        $data = input('post.id');
        $MenuModel = new MenuModel();
        $res = $MenuModel->delMenu($data);
        return json($res);
    }
}