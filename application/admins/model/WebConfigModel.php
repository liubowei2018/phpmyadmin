<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/1/25
 * Time: 14:02
 */

namespace app\admins\model;


use think\Model;

class WebConfigModel extends Model
{
    protected $name = 'web_config';

    public function getConfigList(){
        $list = $this->select();
        $config = [];
        foreach ($list as $k => $v) {
            $config[trim($v['name'])] = $v['value'];
        }
        return $config;
    }
}