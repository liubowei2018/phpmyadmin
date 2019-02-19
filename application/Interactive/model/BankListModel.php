<?php
/**
 * Created by PhpStorm.
 * User: liubowei
 * Date: 2019/2/19
 * Time: 15:36
 */

namespace app\Interactive\model;


use think\Model;

class BankListModel extends Model
{
    protected $name = 'bank_list';

    /**
     *获取银行卡列表
     */
    public function getBankList(){
        return $this->field('id,bankname')->where('state',1)->select();
    }
}