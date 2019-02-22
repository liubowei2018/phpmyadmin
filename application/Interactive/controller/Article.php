<?php
/**
 * Created by PhpStorm.
 * User: liubowei
 * Date: 2019/2/20
 * Time: 10:19
 */

namespace app\Interactive\controller;

use app\Interactive\model\ArticleModel;
use app\Interactive\model\MemberModel;
use think\Cache;
use think\Db;

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
        $list = Db::name('article')->field("title,remark,CONCAT('$url',img_path) as img_path,CONCAT('$web_url_info',id) as web_url,FROM_UNIXTIME(create_time, '%Y-%m-%d') as create_time")->where($map)->page($data['page'],15)->order('create_time DESC')->select();
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
    /**
     * 帮助中心新闻列表
     */
    public function help_center(){
        $data = input('post.');
        $validate_res = $this->validate($data,'HomeValidate.whole');
        if($validate_res !== true){ return json(['code'=>1015,'msg'=>$validate_res]); } //数据认证
        if(getSign($data) != $data['Sign']){ return json(['code'=>1013,'msg'=>'签名错误']);} //签名认证
        if(Cache::get($data['uuid'].'_token') != $data['token']) return json(['code'=>1004,'msg'=>'用户未登录']);//登陆验证
        $page = input('post.page');
        $page = $page?$page:1;
        $url = web_url_str();
        $web_url_info = $url.'/Interactive/Article/article_info.html?id=';
        $map = [
            'group_id'=>6,
            'state'=>1
        ];
        $list = Db::name('article')->field("title,remark,CONCAT('$url',img_path) as img_path,CONCAT('$web_url_info',id) as web_url,FROM_UNIXTIME(create_time, '%Y-%m-%d') as create_time")->where($map)->page($page,15)->order('create_time DESC')->select();
        $new_list = [];
        $final_list = [];
        if(count($list) > 0){
            foreach ($list as $k=>$v){
                $keys = $v['create_time'];
                $new_list[$keys]['time'] = $v['create_time'];
                $new_list[$keys]['value'][] = $v;
            }
            foreach ($new_list as $a => $b){
                $final_list[]=$b;
            }
            return json(['code'=>1011,'msg'=>'成功','data'=>$final_list]);
        }else{
            return json(['code'=>1012,'msg'=>'暂无数据','data'=>""]);
        }
    }

    /**
     * 添加建议
     */
    public function proposal(){
        $data = input('post.');
        $validate_res = $this->validate($data,'HomeValidate.proposal');
        if($validate_res !== true){ return json(['code'=>1015,'msg'=>$validate_res]); } //数据认证
        if(getSign($data) != $data['Sign']){ return json(['code'=>1013,'msg'=>'签名错误']);} //签名认证
        if(Cache::get($data['uuid'].'_token') != $data['token']) return json(['code'=>1004,'msg'=>'用户未登录']);//登陆验证
        $MemberModel = new MemberModel();
        $member_info = $MemberModel->getMemberInfo('id',['uuid'=>$data['uuid']]);
        $array = [
            'user_id'=>$member_info['id'],
            'phone'=>$data['phone'],
            'content'=>$data['content'],
            'add_time'=>time(),
            'state'=>0,
        ];
        try{
            Db::name('proposal')->insert($array);
            return json(['code'=>1011,'msg'=>'提交成功','data'=>'']);
        }catch (\Exception $exception){
            return json(['code'=>1012,'msg'=>'提交失败','data'=>'']);
        }
    }
}