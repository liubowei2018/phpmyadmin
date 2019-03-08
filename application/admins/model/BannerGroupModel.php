<?php
/**
 * Created by PhpStorm.
 * User: liubowei
 * Date: 2019/2/20
 * Time: 10:42
 */

namespace app\admins\model;


use think\Model;
use think\Db;
class BannerGroupModel extends Model
{
    protected  $name='banner_group';
    /**
     * 获取一条信息
     */
    public function getGroupOnes($id){
        return $this->where('id',$id)->find();
    }
    /**
     * 获取分类列表
     */
    public function getGroupList($info='',$map=[],$page=1,$row=15){
        if($info == 'all'){
            return $this->where($map)->select();
        }else{
            return $this->where($map)->page($page,$row)->select();
        }
    }
    /**
     *  添加文章分类
     */
    public function getAddGroup($data){
        try{
            $this->insert($data);
            return ['code'=>1011,'msg'=>'添加图片分类成功','data'=>''];
        }catch (\Exception $e){
            return ['code'=>1012,'msg'=>$e->getMessage()];
        }
    }

    /**
     *编辑分类
     */
    public function getEditGroup($data){
        try{
            $this->save($data,['id',$data['id']]);
            return ['code'=>1011,'msg'=>'修改分类成功'];
        }catch (\Exception $e){
            return ['code'=>1012,'msg'=>$e->getMessage()];
        }
    }

    /**
     * 修改分类状态
     */
    public function getStateGroup($id){
        $group_state = $this->where('id',$id)->value('state');
        try{
            if($group_state == 1){
                $this->where('id',$id)->update(['state'=>0]);
                return ['code'=>1011,'msg'=>'状态已禁止','date'=>''];
            }else{
                $this->where('id',$id)->update(['state'=>1]);
                return ['code'=>1011,'msg'=>'状态已开启','date'=>''];
            }
        }catch (\Exception $e){
            return ['code'=>1012,'msg'=>$e->getMessage(),'date'=>''];
        }
    }

    /**
     * 删除当前分类
     */
    public function getDelGroup($id){
        try{
            $this->where(['id'=>['in',$id]])->delete();
            return ['code'=>1011,'msg'=>'删除分类成功'];
        }catch (\Exception $e){
            return ['code'=>1012,'msg'=>$e->getMessage(),'date'=>''];
        }
    }
}