<?php
/**
 * Created by PhpStorm.
 * User: liubowei
 * Date: 2019/2/18
 * Time: 10:33
 */

namespace app\admins\controller;


use app\admins\model\ArticleGroupModel;
use app\admins\model\ArticleModel;
use app\admins\model\BannerGroupModel;
use think\Db;

class Article extends Base
{
    public function index(){
        $ArticleModel = new ArticleModel();
        if(request()->isPost()){
            $map = [];
            $key = input('post.key');
            if(!empty($key)){
                $map['a.title'] = $key;
            }
            $page = input('get.page') ? input('get.page'):1;
            $rows = input('get.rows');// 获取总条数
            $count = Db::name('article')->alias('a')->field('a.*,g.title as group_title')->where($map)->join('article_group g','g.id = a.group_id')->count();
            $list = $ArticleModel->getArticleList($map,$page,$rows);
            return json(['count'=>$count,'list'=>$list,'page'=>$page]);
        }
        return $this->fetch();
    }

    /**
     * 添加文章
     */
    public function add_article(){
        $ArticleModel = new ArticleModel();
        $ArticleGroupModel = new ArticleGroupModel();
        if(request()->isPost()){
            $data = input('post.');
            $res = $ArticleModel->getAddArticle($data);
            return json($res);
        }
        $groupList = $ArticleGroupModel->getGroupList('all');
        $this->assign('group_list',$groupList);
        return $this->fetch();
    }
    /**
     * 编辑文章
     */
    public function edit_article(){
        $ArticleModel = new ArticleModel();
        $ArticleGroupModel = new ArticleGroupModel();
        if(request()->isPost()){
            $data = input('post.');
            $res = $ArticleModel->getEditArticle($data);
            return json($res);
        }
        $id = input('param.id');
        $groupList = $ArticleGroupModel->getGroupList('all');
        $info = $ArticleModel->getArticleOnes($id);
        $this->assign('info',$info);
        $this->assign('group_list',$groupList);
        return $this->fetch();
    }
    /**
     * 修改文章状态
     */
    public function state_article(){
        $ArticleModel = new ArticleModel();
        if(request()->isPost()){
            $id = input('post.id');
            $res = $ArticleModel->getStateArticle($id);
            return json($res);
        }
    }
    /**
     * 删除文章
     */
    public function del_article(){
        $ArticleModel = new ArticleModel();
        if(request()->isPost()){
            $id = input('post.id');
            $res = $ArticleModel->getDelArticle($id);
            return json($res);
        }
    }
    /*---------------------------------文章分类---------------------------------------------*/
    /**
     * 分类列表
     */
    public function group_index(){
        $ArticleGroupModel = new ArticleGroupModel();
        if(request()->isPost()){
            $map = [];
            $key = input('psot.key');
            if(!empty($key)){
                $map['title'] = ['like','%'.$key.'%'];
            }
            $page = input('get.page') ? input('get.page'):1;
            $rows = input('get.rows');// 获取总条数
            $count = Db::name('article_group')->where($map)->count();
            $list = $ArticleGroupModel->getGroupList('',$map,$page,$rows);
            return json(['count'=>$count,'list'=>$list,'page'=>$page]);
        }
        return $this->fetch();
    }
    /**
     * 添加分类
     */
    public function add_group(){
        $ArticleGroupModel = new ArticleGroupModel();
        if(request()->isPost()){
            $data = input('post.');
            $result = $this->validate($data,'NewsValidate.article_group');
            if($result !== true){
                return json(['code'=>1012,'msg'=>$result,'data'=>'']);
            }
            $data['create_time'] = time();
            $res = $ArticleGroupModel->getAddGroup($data);
            return json($res);
        }
        return $this->fetch();
    }
    /**
     * 编辑分类
     */
    public function edit_group(){
        $ArticleGroupModel = new ArticleGroupModel();
        if(request()->isPost()){
            $data = input('post.');
            $result = $this->validate($data,'NewsValidate.article_group');
            if($result !== true){
                return json(['code'=>1012,'msg'=>$result,'data'=>'']);
            }
            $res = $ArticleGroupModel->getEditGroup($data);
            return json($res);
        }
        $id = input('get.id');
        $info = $ArticleGroupModel->getGroupOnes($id);
        $this->assign('info',$info);
        return $this->fetch();
    }
    /**
     * 分类状态
     */
    public function state_group(){
        $ArticleGroupModel = new ArticleGroupModel();
        $id = input('post.id');
        $res = $ArticleGroupModel->getStateGroup($id);
        return json($res);
    }
    /**
     * 删除分类
     */
    public function del_group(){
        $ArticleGroupModel = new ArticleGroupModel();
        $id = input('post.id');
        $article_count = Db::name('article')->where(['group_id'=>['in',$id]])->count();
        if($article_count > 0 ){
            return json(['code'=>1012,'msg'=>'当前分类下还有文章列表']);
        }else{
            $res = $ArticleGroupModel->getDelGroup($id);
            return json($res);
        }
    }

}