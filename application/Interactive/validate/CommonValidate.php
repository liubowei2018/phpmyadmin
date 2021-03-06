<?php
/**
 * Created by PhpStorm.
 * User: liubowei
 * Date: 2019/2/19
 * Time: 15:32
 */

namespace app\Interactive\validate;


use think\Validate;

class CommonValidate extends Validate
{
    protected $rule = [
        ['uuid','require','uuid不能为空'],
        ['token','require','令牌不能为空'],
        ['TimeStamp','require','时间不为空'],
        ['Sign','require','签名不能为空'],
        /*分页*/
        ['type','require','分类类型不能为空'],
        ['page','require','列表分页不能为空'],
    ];

    protected $scene = [
        'common '=>['token','TimeStamp','Sign','uuid'],
        'article_list '=>['token','TimeStamp','Sign','uuid','type','page'],
        'synopsis '=>['type'],
    ];
}