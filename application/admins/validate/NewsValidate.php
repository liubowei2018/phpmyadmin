<?php
/**
 * Created by PhpStorm.
 * User: liubowei
 * Date: 2019/2/18
 * Time: 11:04
 */

namespace app\admins\validate;


use think\Validate;

class NewsValidate extends Validate
{
    protected $rule = [
        ['title','require','请输入信息标题'],
        ['content','require','请输入文章详情'],
        ['path','require','请选择图片'],
    ];

    protected $scene = [
        'article'=>['title','content'],
        'article_group'=>['title'],
    ];
}