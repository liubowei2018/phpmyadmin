<?php
/**
 * Created by PhpStorm.
 * User: liubowei
 * Date: 2019/2/19
 * Time: 9:48
 */

namespace app\Interactive\validate;


use think\Validate;

class LoginValidate extends Validate
{
    protected $rule = [
        ['username','require','用户姓名不能为空'],
        ['phone','require','手机号不能为空'],
        ['sms_code','require','短信验证码不能为空'],
        ['img_path','require','用户头像不能为空'],
        ['uuid','require','用户微信uuid不能为空'],
        ['Sign','require','签名不能为空'],
    ];

    protected $scene = [
        'entry'=>['username','img_path','uuid','Sign'],
        'edit_mobile'=>['phone','uuid','Sign'],
        'entry_sms'=>['phone','Sign'],
        'mobile_entry'=>['phone','sms_code','Sign'],
        'member_edit_mobile'=>['phone','uuid','sms_code','Sign'],
    ];
}