<?php
/**
 * Created by PhpStorm.
 * User: liubowei
 * Date: 2019/2/19
 * Time: 16:15
 */

namespace app\Interactive\validate;


use think\Validate;

class MemberValidate extends Validate
{
    protected $rule = [
        /*公共*/
        ['uuid','require','用户微信uuid不能为空'],
        ['token','require','令牌不能为空'],
        ['TimeStamp','require','令牌不能为空'],
        ['Sign','require','签名不能为空'],
        /*银行卡*/
        ['bankname','require','开户行不能为空'],
        ['bankcard','require','银行卡号不能为空'],
        ['username','require','姓名不能为空'],

    ];

    protected $scene = [
        'edit_member_bank'=>['uuid','token','TimeStamp','Sign','bankname','bankcard','username'],

    ];
}