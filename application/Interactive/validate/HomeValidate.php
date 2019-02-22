<?php
/**
 * Created by PhpStorm.
 * User: liubowei
 * Date: 2019/2/21
 * Time: 11:30
 */

namespace app\Interactive\validate;


use think\Validate;

class HomeValidate extends Validate
{
    protected $rule = [
        ['uuid','require','uuid不能为空'],
        ['token','require','令牌不能为空'],
        ['TimeStamp','require','时间不为空'],
        ['Sign','require','签名不能为空'],
        /*分页*/
        ['type','require','分类类型不能为空'],
        ['page','require','列表分页不能为空'],
        /*资金*/
        ['money','require','操作资金不能为空'],
        /*信息*/
        ['phone','require','联系电话不能为空'],
        ['content','require','详情信息不能为空'],
    ];

    protected $scene = [
        'synopsis'=>['type'],
        'whole'=>['uuid','token','TimeStamp','Sign'],
        'article'=>['uuid','token','TimeStamp','Sign','type','page'],
        'withdraw_cash'=>['uuid','token','TimeStamp','Sign','type','money'],
        'proposal'=>['uuid','token','TimeStamp','Sign','phone','content'],
    ];
}