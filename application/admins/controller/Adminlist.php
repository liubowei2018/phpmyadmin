<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/2/11
 * Time: 14:25
 */

namespace app\admins\controller;


use app\admins\model\AdminModel;

class Adminlist extends Base
{
    /**
     * 用户列表
     */
    public function user_list(){
        if(request()->isPost()){
            $AdminModel = new AdminModel();
            $page = input('get.page') ? input('get.page'):1;
            $rows = input('get.rows');// 获取总条数
            $lists = $AdminModel->getUserList($page,$rows);
            return json($lists);
        }
        return $this->fetch();
    }
}