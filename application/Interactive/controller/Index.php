<?php
/**
 * Created by PhpStorm.
 * User: liubowei
 * Date: 2019/2/20
 * Time: 11:37
 */

namespace app\Interactive\controller;


use app\admins\model\BannerModel;
use app\Interactive\model\ArticleModel;
use think\Cache;
class Index extends ApiBase
{
    /**
     * 首页信息  文章及图片
     */
    public function home_page(){
        $data = input('post.');
        $validate_res = $this->validate($data,'CommonValidate.common');
        if($validate_res !== true){ return json(['code'=>1015,'msg'=>$validate_res]); } //数据认证
        if(getSign($data) != $data['Sign']){ return json(['code'=>1013,'msg'=>'签名错误']);} //签名认证
        if(Cache::get($data['uuid'].'_token') != $data['token']) return json(['code'=>1004,'msg'=>'用户未登录']);//登陆验证
        $BannerModel = new BannerModel();
        $ArticleModel = new ArticleModel();
        $url = web_url_str();
        $banner = $BannerModel->getArticleList(['a.group_id'=>1,'a.state'=>1],1,100,"a.id,CONCAT('$url',a.path) as path,a.web_url");
        $article = $ArticleModel->getArticleList('id,title',['group_id'=>8,'state'=>1],1,10);
        return json(['code'=>1011,'msg'=>'获取成功','banner'=>$banner,'article'=>$article]);
    }

}