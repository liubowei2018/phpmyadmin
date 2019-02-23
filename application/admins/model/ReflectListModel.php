<?php
/**
 * Created by PhpStorm.
 * User: liubowei
 * Date: 2019/2/23
 * Time: 16:51
 */

namespace app\admins\model;


use think\Model;

class ReflectListModel extends Model
{
    protected $name = "reflect_list";

    /**
     * 获取信息列表
     */
    public function getLogList($field,$map,$page,$rows,$order){
        return $this->alias('l')->field($field)->where($map)->join('member m','m.id = l.user_id')->page($page,$rows)->order($order)->select();
    }
}