<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/1/25
 * Time: 14:03
 */
use think\Cache;
function getWebConfigList(){
    $list = Cache::get('WebConfigList');
    if(!$list){
        $WebConfig = new \app\admins\model\WebConfigModel();
        $list = $WebConfig->getConfigList();
        Cache::set('WebConfigList',$list,7200);
    }
    return $list;
}