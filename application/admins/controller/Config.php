<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/1/25
 * Time: 14:01
 */

namespace app\admins\controller;


use app\admins\model\WebConfigModel;

class Config extends Base
{
    public function web_config(){
        $config = new WebConfigModel();
        $lists = $config->getConfigList();
        $this->assign('config',$lists);
        return $this->fetch();
    }
}