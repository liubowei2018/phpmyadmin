<?php
/**
 * Created by PhpStorm.
 * User: liubowei
 * Date: 2019/3/14
 * Time: 10:47
 */

namespace app\admins\validate;


use think\Validate;

class BankValidate extends Validate
{
    protected $rule = [
        ['bankname','require','请输入银行名称'],
        ['state','require','请选择状态'],
    ];

}