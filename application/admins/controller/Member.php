<?php
/**
 * Created by PhpStorm.
 * User: liubowei
 * Date: 2019/2/16
 * Time: 15:54
 */

namespace app\admins\controller;


use app\admins\model\MemberMobile;
use think\Db;

class Member extends Base
{
    public function index(){
        $MemberModel = new MemberMobile();
        if(request()->isPost()){
         $map = [];
         $key = input('post.key');
         if(!empty($key)){
            $map['account|mobile|username'] = $key;
         }
         $page = input('get.page') ? input('get.page'):1;
         $rows = input('get.rows');// 获取总条数
         $count = Db::name('member')->where($map)->count();
         $list = $MemberModel->getMemberList('*',$map,$page,$rows,'id DESC');
         return json(['count'=>$count,'list'=>$list,'page'=>$page]);
        }
        return $this->fetch();
    }
    public function member_state(){
        $MemberModel = new MemberMobile();
        $id = input('post.id');
        $res = $MemberModel->getMemberState($id);
        return json($res);
    }
}