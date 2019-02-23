<?php
/**
 * Created by PhpStorm.
 * User: liubowei
 * Date: 2019/2/23
 * Time: 14:39
 */

namespace app\admins\model;


use think\Model;

class MoneyLogModel extends Model
{
    protected $name = 'money_log';
    /**
     * 获取用户资金记录
     */
    public function getLogList($field,$map,$page,$rows,$order){
        return $this->alias('l')->field($field)->where($map)->join('member m','m.id = l.user_id')->page($page,$rows)->order($order)->select();
    }
}