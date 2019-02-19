<?php
/**
 * Created by PhpStorm.
 * User: liubowei
 * Date: 2019/2/19
 * Time: 11:08
 */
use think\Cache;
use think\Db;
/**
 * app配置信息
 */
function app_config_list(){
    $app_config = Cache::get('app_config_list');
    if($app_config){
        return $app_config;
    }else{
        $app_config = Db::name('web_config')->select();
        $config = [];
        foreach ($app_config as $k => $v) {
            $config[trim($v['name'])]=$v['value'];
        }
        Cache::set('app_config_list',$config,7200);
        return $config;
    }
}