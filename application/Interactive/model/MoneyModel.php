<?php
/**
 * Created by PhpStorm.
 * User: liubowei
 * Date: 2019/2/21
 * Time: 9:11
 */

namespace app\Interactive\model;


use think\Model;

class MoneyModel extends Model
{
    protected $name = "money";

    /**
     * 添加会员信息
     */
    public function getAddInfo($data){
        $this->startTrans();
        try{
            $this->insert($data);
            $this->commit();
            return true;
        }catch (\Exception $exception){
            $this->rollback();
            return false;
        }
    }

    /**
     * 获取用户资金
     */
    public function getMemberMoney($field,$map){
        return $this->field($field)->where($map)->find();
    }
}