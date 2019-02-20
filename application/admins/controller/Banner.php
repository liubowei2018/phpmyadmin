<?php
/**
 * Created by PhpStorm.
 * User: liubowei
 * Date: 2019/2/18
 * Time: 10:32
 */

namespace app\admins\controller;


use app\admins\model\BannerGroupModel;
use app\admins\model\BannerModel;
use think\Db;
class Banner extends Base
{
    public function index(){
         $BannerModel = new BannerModel();
        if(request()->isPost()){
            $map = [];
            $key = input('post.key');
            if(!empty($key)){
                $map['a.title'] = $key;
            }
            $page = input('get.page') ? input('get.page'):1;
            $rows = input('get.rows');// 获取总条数
            $count = Db::name('banner')->alias('b')->field('b.*,g.title as group_title')->where($map)->join('banner_group g','g.id = b.group_id')->count();
            $list = $BannerModel->getArticleList($map,$page,$rows);
            return json(['count'=>$count,'list'=>$list,'page'=>$page]);
        }
        return $this->fetch();
    }
    /**
     * 添加图片
     */
    public function add_article(){
        $BannerModel = new BannerModel();;
        $BannerGroupModel = new BannerGroupModel();
        if(request()->isPost()){
            $data = input('post.');
            $res = $BannerModel->getAddArticle($data);
            return json($res);
        }
        $groupList = $BannerGroupModel->getGroupList('all');
        $this->assign('group_list',$groupList);
        return $this->fetch();
    }
    /**
     * 编辑图片
     */
    public function edit_article(){
        $BannerModel = new BannerModel();;
        $BannerGroupModel = new BannerGroupModel();
        if(request()->isPost()){
            $data = input('post.');
            $res = $BannerModel->getEditArticle($data);
            return json($res);
        }
        $id = input('param.id');
        $groupList = $BannerGroupModel->getGroupList('all');
        $info = $BannerModel->getArticleOnes($id);
        $this->assign('info',$info);
        $this->assign('group_list',$groupList);
        return $this->fetch();
    }
    /**
     * 修改图片状态
     */
    public function state_article(){
        $BannerModel = new BannerModel();;
        if(request()->isPost()){
            $id = input('post.id');
            $res = $BannerModel->getStateArticle($id);
            return json($res);
        }
    }
    /**
     * 删除图片
     */
    public function del_article(){
        $BannerModel = new BannerModel();;
        if(request()->isPost()){
            $id = input('post.id');
            $res = $BannerModel->getDelArticle($id);
            return json($res);
        }
    }



    /*---------------------------------图片分类---------------------------------------------*/
    /**
     * 分类列表
     */
    public function group_index(){
        $BannerGroupModel = new BannerGroupModel();
        if(request()->isPost()){
            $map = [];
            $key = input('psot.key');
            if(!empty($key)){
                $map['title'] = ['like','%'.$key.'%'];
            }
            $page = input('get.page') ? input('get.page'):1;
            $rows = input('get.rows');// 获取总条数
            $count = Db::name('article_group')->where($map)->count();
            $list = $BannerGroupModel->getGroupList('',$map,$page,$rows);
            return json(['count'=>$count,'list'=>$list,'page'=>$page]);
        }
        return $this->fetch();
    }
    /**
     * 添加分类
     */
    public function add_group(){
        $BannerGroupModel = new BannerGroupModel();
        if(request()->isPost()){
            $data = input('post.');
            $result = $this->validate($data,'NewsValidate.article_group');
            if($result !== true){
                return json(['code'=>1012,'msg'=>$result,'data'=>'']);
            }
            $data['create_time'] = time();
            $res = $BannerGroupModel->getAddGroup($data);
            return json($res);
        }
        return $this->fetch();
    }
    /**
     * 编辑分类
     */
    public function edit_group(){
        $BannerGroupModel = new BannerGroupModel();
        if(request()->isPost()){
            $data = input('post.');
            $result = $this->validate($data,'NewsValidate.article_group');
            if($result !== true){
                return json(['code'=>1012,'msg'=>$result,'data'=>'']);
            }
            $res = $BannerGroupModel->getEditGroup($data);
            return json($res);
        }
        $id = input('get.id');
        $info = $BannerGroupModel->getGroupOnes($id);
        $this->assign('info',$info);
        return $this->fetch();
    }
    /**
     * 分类状态
     */
    public function state_group(){
        $BannerGroupModel = new BannerGroupModel();
        $id = input('post.id');
        $res = $BannerGroupModel->getStateGroup($id);
        return json($res);
    }
    /**
     * 删除分类
     */
    public function del_group(){
        $BannerGroupModel = new BannerGroupModel();
        $id = input('post.id');
        $article_count = Db::name('article')->where(['group_id'=>['in',$id]])->count();
        if($article_count > 0 ){
            return json(['code'=>1012,'msg'=>'当前分类下还有图片']);
        }else{
            $res = $BannerGroupModel->getDelGroup($id);
            return json($res);
        }
    }
}