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
    public function index(){
        return $this->fetch();
    }
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

    /**
     * 上传图片
     */
    public function upload_images(){
        $data = input('post.');
        $validate_res = $this->validate($data,'HomeValidate.whole');
        if($validate_res !== true){ return json(['code'=>1015,'msg'=>$validate_res]); } //数据认证
        if(getSign($data) != $data['Sign']){ return json(['code'=>1013,'msg'=>'签名错误']);} //签名认证
        if(Cache::get($data['uuid'].'_token') != $data['token']) return json(['code'=>1004,'msg'=>'用户未登录']);//登陆验证
        $MemberModel = new MemberModel();
        $member_info = $MemberModel->getMemberInfo('id',['uuid'=>$data['uuid']]);
        $files = request()->file('image');
        $array = [];
        if($files){
            foreach($files as $k=>$file){
                $info = $file->validate(['size'=>10485760,'ext'=>'jpg,png'])->move(ROOT_PATH . 'public' . DS . 'uploads/user');
                if($info){
                    $str= str_replace("\\",'/','/uploads/user/'.$info->getSaveName());
                    $array[] = $str;
                }else{
                    return json(['code'=>1012,'msg'=>'第'.$k.'上传失败','data'=>$file->getError()]);
                }
            }
            $array_str = implode(',',$array);
            $this->add_member_img($member_info['id'],$array);
            return json(['code'=>1011,'msg'=>'上传成功','data'=>$array_str]);
        }else{
            return json(['code'=>1012,'msg'=>'请选择图片','data'=>'']);
        }
    }
    /**
     * 保存用户图片
     */
    private function add_member_img($user_id,$array){
        foreach ($array as $k=>$v){
            Db::name('member_img')->insert(['user_id'=>$user_id,'img_path'=>$v,'add_time'=>time()]);
        }
    }

    /**
     * 获取会员图库
     */
    public function member_img_list(){
        $data = input('post.');
        $validate_res = $this->validate($data,'HomeValidate.whole');
        if($validate_res !== true){ return json(['code'=>1015,'msg'=>$validate_res]); } //数据认证
        if(getSign($data) != $data['Sign']){ return json(['code'=>1013,'msg'=>'签名错误']);} //签名认证
        if(Cache::get($data['uuid'].'_token') != $data['token']) return json(['code'=>1004,'msg'=>'用户未登录']);//登陆验证
        $MemberModel = new MemberModel();
        $member_info = $MemberModel->getMemberInfo('id',['uuid'=>$data['uuid']]);
        $page = input('post.page');
        $page = $page?$page:1;
        $url = web_url_str();
        $count = Db::name('member_img')->where('user_id',$member_info['id'])->count();
        $count =(string) ceil($count/15);
        $list = Db::name('member_img')->field("CONCAT('$url',img_path) as img_path,FROM_UNIXTIME(add_time, '%Y-%m-%d') as add_time")->where('user_id',$member_info['id'])->page($page,15)->order('add_time DESC')->select();
        $new_list = [];
        $final_list = [];
        if(count($list) > 0){
            foreach ($list as $k=>$v){
                $keys = $v['add_time'];
                $new_list[$keys]['time'] =  $v['add_time'];
                $new_list[$keys]['value'][] = $v;
            }
            foreach ($new_list as $a => $b){
                $final_list[]=$b;
            }
            return json(['code'=>1011,'msg'=>'成功','data'=>$final_list,'page_count'=>$count]);
        }else{
            return json(['code'=>1012,'msg'=>'暂无数据','data'=>[],'page_count'=>'0']);
        }
    }
}