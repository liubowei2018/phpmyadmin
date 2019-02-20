<?php
/**
 * Created by PhpStorm.
 * User: liubowei
 * Date: 2019/2/20
 * Time: 10:21
 */

namespace app\Interactive\model;


use think\Model;

class ArticleModel extends Model
{
    protected $name = 'article';
    /**
     *  获取文章列表
     */
    public function getArticleList($field,$map,$page,$rows){
        return $this->field($field)->where($map)->page($page,$rows)->order('id DESC')->select();
    }
    /**
     * 获取一条文章详情
     */
    public function getArticleInfo($id){
        return $this->where('id',$id)->find();
    }
}