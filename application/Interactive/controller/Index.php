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
use app\Interactive\model\MemberModel;
use think\Cache;
use think\Db;

class Index extends ApiBase
{
    /**
     * 首页信息  文章及图片
     */
    public function home_page(){
        $data = input('post.');
        $validate_res = $this->validate($data,'HomeValidate.whole');
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
    /**
     *升级详情
     */
    public function upgrade_info(){
        $data = input('post.');
        $validate_res = $this->validate($data,'HomeValidate.whole');
        if($validate_res !== true){ return json(['code'=>1015,'msg'=>$validate_res]); } //数据认证
        if(getSign($data) != $data['Sign']){ return json(['code'=>1013,'msg'=>'签名错误']);} //签名认证
        if(Cache::get($data['uuid'].'_token') != $data['token']) return json(['code'=>1004,'msg'=>'用户未登录']);//登陆验证
        $MemberModel = new MemberModel();
        $user_info = $MemberModel->getMemberInfo('',['uuid'=>$data['uuid']]);
        $config = privilege_config_list();
        //1 普通会员 2 vip会员  3 合伙人
        $list = [];
        if($config['vip_state'] == 1){
            //如果是普通会员

            if($user_info['type'] < 2){
                $list[] = [
                    'id'=>2,
                    'title'=>'升级VIP会员',
                    'money'=>$config['vip_money']
                ];
            }
        }

        if($config['partner_state'] == 1){
            switch ($user_info['type']){
                case 1:
                    $list[] = [
                        'id'=>3,
                        'title'=>'升级合伙人',
                        'money'=>$config['partner_money']
                    ];
                    break;
                case 2:
                    $list[] = [
                        'id'=>3,
                        'title'=>'升级合伙人',
                        'money'=>$config['partner_money']-$config['vip_money']
                    ];
                    break;
                case 3:
                    break;
            }
        }
        return json(['code'=>1011,'msg'=>'成功','data'=>$list]);
    }

    /**
     * 单张图片  弹窗   个人中心  红包页面
     */
    public function single_picture(){
        $data = input('post.');
        $validate_res = $this->validate($data,'HomeValidate.whole');
        if($validate_res !== true){ return json(['code'=>1015,'msg'=>$validate_res]); } //数据认证
        if(getSign($data) != $data['Sign']){ return json(['code'=>1013,'msg'=>'签名错误']);} //签名认证
        if(Cache::get($data['uuid'].'_token') != $data['token']) return json(['code'=>1004,'msg'=>'用户未登录']);//登陆验证
        $BannerModel = new BannerModel();
        $url = web_url_str();
        //弹窗
        $tanchuang = Db::name('banner')->field("CONCAT('$url',path) as path,web_url")->where(['group_id'=>3])->order('id DESC')->find();
        $tanchuang = $tanchuang?$tanchuang:'';
        //个人中心
        $geren = Db::name('banner')->field("CONCAT('$url',path) as path,web_url")->where(['group_id'=>2])->order('id DESC')->find();
        $geren = $geren?$geren:'';
        //红包页面
        $hongbao = Db::name('banner')->field("CONCAT('$url',path) as path,web_url")->where(['group_id'=>4])->order('id DESC')->find();
        $hongbao = $hongbao?$hongbao:'';
        return json(['code'=>1011,'msg'=>'成功','tanchuang'=>$tanchuang,'geren'=>$geren,'hongbao'=>$hongbao]);
    }

}