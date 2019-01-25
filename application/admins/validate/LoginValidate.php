<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/1/25
 * Time: 16:25
 */

namespace app\admins\validate;


use think\Validate;

class LoginValidate extends Validate
{
    protected $rule = [
        ['adm','require','请输入账号'],
        ['pw','require','请输入密码'],
        ['pin','require','请输入验证码'],
    ];

    protected $scene = [
        'entry'=>['adm','pw','pin'],
    ];
}