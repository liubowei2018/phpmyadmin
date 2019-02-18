<?php
/**
 * Created by PhpStorm.
 * User: liubowei
 * Date: 2019/2/18
 * Time: 10:32
 */

namespace app\admins\controller;


class Banner extends Base
{
    public function index(){

    }




    /*---------------------------------图片分类---------------------------------------------*/
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