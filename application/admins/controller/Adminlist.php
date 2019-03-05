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
use think\Config;

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
            $count = Db::name('admin')->where('name',$data['username'])->count();
            if($count > 0){
                return json(['code'=>1012,'msg'=>'登录账号已存在']);
            }
            $key = Config::get('auth_key');
            $admin_data = [
                'name'=>$data['username'],
                'password'=>md5(md5($data['password']).$key),
                'check_password'=>md5(md5($data['password']).$key),
                'end_time'=>time(),
                'group_id'=>$data['group_id'],
                'state'=>$data['status'],
            ];
            Db::startTrans();
            try{
                $admin_id =  Db::name('admin')->insertGetId($admin_data);
                Db::name('auth_group_access')->insert(['uid'=>$admin_id,'group_id'=>$data['group_id']]);
                Db::commit();
                return json(['code'=>1011,'msg'=>'添加管理员成功']);
            }catch (\Exception $exception){
                Db::rollback();
                return json(['code'=>1012,'msg'=>'添加管理员失败']);
            }
        }
        $UserType = new UserType();
        $typelist = $UserType->getRole();
        $this->assign('typelist',$typelist);
        return $this->fetch();
    }

    /**
     * 编辑用户
     */
    public function user_edit(){
        if(request()->isPost()){
            $data = input('post.');
            $admin_info = Db::name('admin')->where('id',$data['id'])->find();
            $edit_date = [];
            Db::startTrans();
            try{
                $key = Config::get('auth_key');
                //账号修改
                if($data['username'] != $admin_info['name']){
                    $count = Db::name('admin')->where('name',$data['username'])->count();
                    if($count > 0){
                        return json(['code'=>1012,'msg'=>'登录账号已存在']);
                    }else{
                        $edit_date['name'] = $data['username'];
                    }
                }
                //权限修改
                if($data['group_id'] != $admin_info['group_id']){
                    $edit_date['group_id'] = $data['group_id'];
                    Db::name('auth_group_access')->where('uid',$data['id'])->update(['group_id'=>$data['group_id']]);
                }
                //修改密码
                if(!empty($data['password'])){
                    $edit_date['password'] =md5(md5($data['password']).$key);
                }
                //修改状态
                if($data['status'] != $admin_info['state']){
                    $edit_date['state'] = $data['status'];
                }
                if(count($edit_date) > 0){
                    Db::name('admin')->where('id',$data['id'])->update($edit_date);
                }
                Db::commit();
                return json(['code'=>1011,'msg'=>'修改管理员成功']);
            }catch (\Exception $exception){
                Db::rollback();
                return json(['code'=>1012,'msg'=>'修改管理员失败']);
            }
        }
        $id = input('param.id');
        $info = Db::name('admin')->where('id',$id)->find();
        $UserType = new UserType();
        $typelist = $UserType->getRole();
        $this->assign('typelist',$typelist);
        $this->assign('info',$info);
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
            $page = input('post.page') ? input('post.page'):1;
            $rows = input('post.rows');// 获取总条数
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
    /*-----------------------------------用户反馈-----------------------------------*/
    /**
     * 反馈列表
     */
    public function proposal_list(){
        if(request()->isPost()){
            $data = input('post.');
            $key = input('post.key');
            $type = input('post.type');
            $state = input('post.state');
            $stare_time = input('post.stare_time');
            $end_time = input('post.end_time');
            $map = [];
            if(!empty($key)){
                $map['m.username|m.mobile'] = ['like','%'.$key.'%'];
            }
            if(!empty($type)){
                $map['l.type'] = $type;
            }
            if(!empty($state)){
                $map['l.state'] = $state;
            }
            if($state === '0'){
                $map['l.state'] = $state;
            }
            if(!empty($stare_time)){
                $stare_time = $stare_time.' 00:00:00';
                $map['l.create_time'] = ['>= time',$stare_time];
            }
            if(!empty($end_time)){
                $end_time = $end_time.' 23:59:59';
                $map['l.create_time'] = ['<= time',$end_time];
            }
            if(!empty($stare_time) && !empty($end_time)){
                $map['l.create_time'] = ['between time',[$stare_time,$end_time]];
            }
            $page = input('post.page');
            $rows = input('post.rows');
            $page = $page?$page:1;
            $count = Db::name('proposal')->alias('l')->where($map)->join('member m','m.id = l.user_id')->count();
            $list = Db::name('proposal')->alias('l')->field("l.*,m.username,m.mobile,FROM_UNIXTIME(l.add_time, '%Y-%m-%d %H:%i:%s') as add_time")->where($map)->join('member m','m.id = l.user_id')->page($page,$rows)->order('l.add_time DESC')->select();
            return json(['count'=>$count,'list'=>$list,'page'=>$page]);
        }
        return $this->fetch();
    }
    public function proposal_state(){
        $id = input('post.id');
        try{
            Db::name('proposal')->where('id',$id)->update(['state'=>1,'admin_id'=>$this->admin_uid,'admin_name'=>$this->admin_name]);
            return json(['code'=>1011,'msg'=>'确认成功']);
        }catch (\Exception $exception){
            return json(['code'=>1012,'msg'=>'确认失败']);
        }
    }
}