<?php
/**
 * Created by PhpStorm.
 * User: liubowei
 * Date: 2019/2/18
 * Time: 10:34
 */

namespace app\admins\model;


use think\Model;

class BannerModel extends Model
{
    protected $name = 'banner';

    /**
     * 获取一条图片信息
     */
    public function getArticleOnes($id){
        return $this->where('id',$id)->find();
    }
    /**
     * 获取图片列表
     */
    public function getArticleList($map,$page,$row,$field="a.*,g.title as group_title"){
        return $this->alias('a')->field($field)->where($map)->join('banner_group g','g.id = a.group_id')->page($page,$row)->order('group_id ASC,a.create_time DESC')->select();
    }
    /**
     * 添加图片
     */
    public function getAddArticle($data){
        try{
            if(isset($data['file'])){
                unset($data['file']);
            }
            $data['create_time'] = time();
            $this->insert($data);
            return ['code'=>1011,'msg'=>'添加图片成功'];
        }catch (\Exception $e){
            return ['code'=>1012,'msg'=>$e->getMessage()];
        }
    }

    /**
     * 编辑图片
     * @param $data
     * @return array
     */
    public function getEditArticle($data){
        try{
            if(isset($data['file'])){
                unset($data['file']);
            }
            $data['create_time'] = time();
            $this->save($data,['id'=>$data['id']]);
            return ['code'=>1011,'msg'=>'编辑图片成功'];
        }catch (\Exception $e){
            return ['code'=>1012,'msg'=>$e->getMessage()];
        }
    }

    /**
     * 修改图片状态
     */
    public function getStateArticle($id){
        $article_state = $this->where('id',$id)->value('state');
        try{
            if($article_state == 1){
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
     * 删除图片列表
     */
    public function getDelArticle($id){
        try{
            $this->where(['id'=>['in',$id]])->delete();
            return ['code'=>1011,'msg'=>'删除图片成功'];
        }catch (\Exception $e){
            return ['code'=>1012,'msg'=>$e->getMessage(),'date'=>''];
        }
    }


}