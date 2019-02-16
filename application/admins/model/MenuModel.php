<?php

namespace app\admins\model;
use think\Model;
use think\Db;
class MenuModel extends Model
{
    protected $name = 'auth_rule';
    
    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = true;


    /**
     * [getAllMenu 获取全部菜单]
     */
    public function getAllMenu($map,$page,$rows)
    {
        $list = $this->where($map)->page($page,$rows)->order('id asc')->select()->toArray();
        $menu_array = $this->menu_rule($list);

        return $menu_array;
    }

    /**
     * 排序
     */
    private function menu_rule($cate , $lefthtml = '— — ' , $pid=0 , $lvl=0, $leftpin=0 ){
        $arr=array();
        foreach ($cate as $v){
            if($v['pid']==$pid){
                $v['lvl']=$lvl + 1;
                $v['leftpin']=$leftpin + 0;//左边距
                $v['title']=str_repeat($lefthtml,$lvl).$v['title'];
                $arr[]=$v;
                $arr= array_merge($arr,self::menu_rule($cate,$lefthtml,$v['id'],$lvl+1 , $leftpin+20));
            }
        }
        return $arr;
    }

    /**
     * [insertMenu 添加菜单]
     */
    public function insertMenu($param)
    {
        try{
            $result = $this->save($param);
            if(false === $result){
                return ['code' => 1012, 'data' => '', 'msg' => $this->getError()];
            }else{
                return ['code' => 1011, 'data' => '', 'msg' => '添加菜单成功'];
            }
        }catch( \PDOException $e){
            return ['code' => 1012, 'data' => '', 'msg' => $e->getMessage()];
        }
    }



    /**
     * [editMenu 编辑菜单]
     */
    public function editMenu($param)
    {
        try{
            $result =  $this->save($param, ['id' => $param['id']]);
            if(false === $result){
                return ['code' => 1012, 'data' => '', 'msg' => $this->getError()];
            }else{
                return ['code' => 1011, 'data' => '', 'msg' => '编辑菜单成功'];
            }
        }catch( PDOException $e){
            return ['code' => 1012, 'data' => '', 'msg' => $e->getMessage()];
        }
    }



    /**
     * [getOneMenu 根据菜单id获取一条信息]
     */
    public function getOneMenu($id)
    {
        return $this->where('id', $id)->find();
    }



    /**
     * [delMenu 删除菜单]
     */
    public function delMenu($id)
    {
        try{
            $this->where(['id'=>['in',$id]])->delete();
            return ['code' => 1011, 'data' => '', 'msg' => '删除菜单成功'];
        }catch( PDOException $e){
            return ['code' => 1012, 'data' => '', 'msg' => $e->getMessage()];
        }
    }


    /**
     * [stateRole 修改菜单状态]
     */
    public function statusMenu($id){
        try{
            $state = $this->where('id',$id)->value('status');
            if($state == 1){
                $this->where('id',$id)->update(['status'=>0]);
            }else{
                $this->where('id',$id)->update(['status'=>1]);
            }
            return ['code' => 1011, 'data' => '', 'msg' => '菜单状态修改成功'];
        }catch (\Exception $e){
            return ['code' => 1012, 'data' => '', 'msg' => $e->getMessage()];
        }
    }

}