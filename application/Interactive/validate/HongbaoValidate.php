<?php
/**
 * Created by PhpStorm.
 * User: liubowei
 * Date: 2019/2/25
 * Time: 17:47
 */

namespace app\Interactive\validate;


use think\Validate;

class HongbaoValidate extends Validate
{
    protected $rule = [
        ['uuid','require','uuid不能为空'],
        ['token','require','令牌不能为空'],
        ['TimeStamp','require','时间不为空'],
        ['Sign','require','签名不能为空'],
        ['lng','require','纬度不能为空'],
        ['lat','require','经度不能为空'],
        ['type','require','类型不能为空'],
        ['money','require','红包金额不能为空'],
    ];

    protected $scene = [
        'hongbao'=>['uuid','token','TimeStamp','Sign','lng','lat','type'],
        'red_envelope_list'=>['uuid','token','TimeStamp','Sign','lng','lat'],
    ];
}