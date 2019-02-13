<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/2/11
 * Time: 14:25
 */

namespace app\admins\controller;


use app\admins\model\AdminModel;
use app\admins\model\UserType;

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
}