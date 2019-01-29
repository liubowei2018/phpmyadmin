<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/1/25
 * Time: 13:46
 */

namespace app\admins\controller;


class Index extends Base
{
    public function index(){
        $webConf = getWebConfigList();
        $this->assign('webConf',$webConf);
        return $this->fetch();
    }
}