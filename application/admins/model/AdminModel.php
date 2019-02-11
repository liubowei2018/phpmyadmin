<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/2/11
 * Time: 14:26
 */

namespace app\admins\model;


use think\Model;

class AdminModel extends Model
{
    protected $name = 'admin';

    /**
     * 获取所有管理员
     */
    public function getUserList($page,$row){
        $count = $this->count();
        $list  = $this->page($page,$row)->select();
        $authType = new UserType();
        foreach ($list as $k=>$v){
            $list[$k]['end_time'] = date('Y-m-d H:i:s',$v['end_time']);
            $group = $authType->getOneRole($v['group_id']);
            $list[$k]['group_id'] = $group['title'];
        }
        return ['count'=>$count,'list'=>$list,'page'=>$page];
    }
}