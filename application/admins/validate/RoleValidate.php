<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/1/25
 * Time: 16:25
 */

namespace app\admins\validate;


use think\Validate;

class RoleValidate extends Validate
{
    protected $rule = [
        ['title','require','请输入角色名称'],
        ['status','require','请选择角色状态'],
    ];

    protected $scene = [
        'add_role'=>['title','status'],
    ];
}