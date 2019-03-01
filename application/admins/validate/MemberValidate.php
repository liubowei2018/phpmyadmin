<?php
/**
 * Created by PhpStorm.
 * User: liubowei
 * Date: 2019/3/1
 * Time: 18:16
 */

namespace app\admins\validate;


use think\Validate;

class MemberValidate extends Validate
{
    protected $rule = [
        ['save_money','require','请输入操作金额'],
        ['state','require','请选择操作类型'],
        ['info','require','请输入修改原因'],
    ];

    protected $scene = [
        'save_money'=>['save_money','state','info'],
    ];
}