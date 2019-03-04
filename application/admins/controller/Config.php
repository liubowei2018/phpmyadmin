<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/1/25
 * Time: 14:01
 */

namespace app\admins\controller;


use app\admins\model\PrivilegeConfigModel;
use app\admins\model\WebConfigModel;
use think\Cache;
class Config extends Base
{
    /**
     * 常规设置
     * @return mixed
     */
    public function web_config(){
        $config = new WebConfigModel();
        $lists = $config->getConfigList();
        $this->assign('config',$lists);
        return $this->fetch();
    }
    /**
     *特权设置
     */
    public function privilege_config(){
        $config= new PrivilegeConfigModel();
        $lists = $config->getConfigList();
        $this->assign('config',$lists);
        return $this->fetch();
    }

    /**
     * 批量保存配置
     * @author
     */
    public function save($config){
        $WebConfigModel = new WebConfigModel();
        if($config && is_array($config)){
            foreach ($config as $name => $value) {
                $map = array('name' => $name);
                $WebConfigModel->SaveConfig($map,$value);
            }
        }
        Cache::rm('WebConfigList');
        $this->success('保存成功！');
    }

    /**
     * 批量保存配置
     * @author
     */
    public function privilege_save($config){
        $PrivilegeConfigModel = new PrivilegeConfigModel();
        if($config && is_array($config)){
            foreach ($config as $name => $value) {
                $map = array('name' => $name);
                $res = $PrivilegeConfigModel->SaveConfig($map,$value);
            }
        }
        //Cache::rm('app_config_list');
        $this->success('保存成功！');
    }
}