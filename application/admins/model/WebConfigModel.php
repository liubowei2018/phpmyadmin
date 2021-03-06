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

    //保存信息
    public function SaveConfig($map,$value)
    {
        try{
            $result = $this->allowField(true)->where($map)->setField('value', $value);
            if(false === $result){
                return ['code' => -1, 'msg' => $this->getError()];
            }else{
                return ['code' => 1, 'msg' => '保存成功'];
            }
        }catch( \PDOException $e){
            return ['code' => -2, 'data' => '', 'msg' => $e->getMessage()];
        }
    }
}