<?php
/**
 * Created by PhpStorm.
 * User: liubowei
 * Date: 2019/2/20
 * Time: 10:19
 */

namespace app\Interactive\controller;

use app\Interactive\model\ArticleModel;
use think\Cache;
class Article extends ApiBase
{
    /**
     * 文章列表
     */
    public function article_list(){
        $data = input('post.');
        $validate_res = $this->validate($data,'HomeValidate.article');
        if($validate_res !== true){ return json(['code'=>1015,'msg'=>$validate_res]); } //数据认证
        if(getSign($data) != $data['Sign']){ return json(['code'=>1013,'msg'=>'签名错误']);} //签名认证
        if(Cache::get($data['uuid'].'_token') != $data['token']) return json(['code'=>1004,'msg'=>'用户未登录']);//登陆验证
        $ArticleModel = new ArticleModel();
        $map = [];
        $map['state']='1';
        switch ($data['type']){
            case 'home_page':
                $map['group_id']='8';
                break;
            case 'help':
                $map['group_id']='6';
                break;
            case 'notice':
                $map['group_id']='5';
                break;
        }
        $url = web_url_str();
        $web_url_info =$url.'/Interactive/Article/article_info.html?id=';
        $list = $ArticleModel->getArticleList("title,remark,CONCAT('$url',img_path) as img_path,CONCAT('$web_url_info',id) as web_url",$map,$data['page'],15);
        return json(['code'=>1011,'msg'=>'获取成功','data'=>$list]);
    }
    /**
     * 文章详情
     */
    public function article_info(){
        $id = input('param.id');
        $ArticleModel = new ArticleModel();
        $info = $ArticleModel->getArticleInfo($id);
        $this->assign('info',$info);
        return $this->fetch();
    }

    /**
     * 文章简介
     */
    public function synopsis(){
        $data = input('param.');
        $validate_res = $this->validate($data,'HomeValidate.synopsis');
        if($validate_res !== true){ return json(['code'=>1015,'msg'=>$validate_res]); } //数据认证
        $ArticleModel = new ArticleModel();
        $map = [];
        switch ($data['type']){
            case 'vip': //升级vip
                $map = ['state'=>1,'id'=>7];
                break;
            case 'partner': //升级 合伙人
                $map = ['state'=>1,'id'=>7];
                break;
            case 'task': //任务简介
                $map = ['state'=>1,'id'=>7];
                break;
            case 'company': //公司协议
                $map = ['state'=>1,'id'=>7];
                break;
            default:
                return json(['code'=>1013,'msg'=>'参数异常']);
        }
        $info = $ArticleModel->getArticleDetail($map);
        $this->assign('info',$info);
        return $this->fetch();
    }
}