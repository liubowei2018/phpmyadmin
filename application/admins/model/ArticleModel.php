<?php
/**
 * Created by PhpStorm.
 * User: liubowei
 * Date: 2019/2/18
 * Time: 10:33
 */

namespace app\admins\model;


use think\Model;

class ArticleModel extends Model
{
    protected $name = 'article';

    /**
     * 获取一条文章信息
     */
    public function getArticleOnes($id){
        return $this->where('id',$id)->find();
    }
    /**
     * 获取文章列表
     */
    public function getArticleList($map,$page,$row){
        return $this->alias('a')->field('a.*,g.title as group_title')->where($map)->join('article_group g','g.id = a.group_id')->page($page,$row)->order('group_id ASC,create_time DESC')->select();
    }
    /**
     * 添加文章
     */
    public function getAddArticle($data){
        try{
            if(isset($data['file'])){
                unset($data['file']);
            }
            $data['create_time'] = time();
            $this->insert($data);
            return ['code'=>1011,'msg'=>'添加文章成功'];
        }catch (\Exception $e){
            return ['code'=>1012,'msg'=>$e->getMessage()];
        }
    }

    /**
     * 编辑文章
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
            return ['code'=>1011,'msg'=>'编辑文章成功'];
        }catch (\Exception $e){
            return ['code'=>1012,'msg'=>$e->getMessage()];
        }
    }

    /**
     * 修改分类状态
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
     * 删除文章列表
     */
    public function getDelArticle($id){
        try{
            $this->where(['id'=>['in',$id]])->delete();
            return ['code'=>1011,'msg'=>'删除分类成功'];
        }catch (\Exception $e){
            return ['code'=>1012,'msg'=>$e->getMessage(),'date'=>''];
        }
    }
}