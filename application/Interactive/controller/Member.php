<?php
/**
 * Created by PhpStorm.
 * User: liubowei
 * Date: 2019/2/19
 * Time: 14:04
 */

namespace app\Interactive\controller;


use app\Interactive\model\BankListModel;
use app\Interactive\model\MemberBankModel;
use app\Interactive\model\MemberModel;
use app\Interactive\model\MoneyModel;
use think\Cache;
use think\Db;

class Member extends ApiBase
{
    /**
     * 用户个人信息
     */
    public function member_info(){
        $data = input('post.');
        $validate_res = $this->validate($data,'HomeValidate.whole');
        if($validate_res !== true){ return json(['code'=>1015,'msg'=>$validate_res]); } //数据认证
        if(getSign($data) != $data['Sign']){ return json(['code'=>1013,'msg'=>'签名错误']);} //签名认证
        if(Cache::get($data['uuid'].'_token') != $data['token']) return json(['code'=>1004,'msg'=>'用户未登录']);//登陆验证
        //获取用户信息
        $MmemberModel = new MemberModel();
        $MoneyModel = new MoneyModel();
        $member_info = $MmemberModel->getMemberInfo('id,mobile,username,sex,user_img,pid,synopsis,type',['uuid'=>$data['uuid']]);
        if($member_info){
            $money_info = $MoneyModel->getMemberMoney('*', ['user_id' => $member_info['id']]);
            $bonus_close = $money_info['one_bonus_log'] + $money_info['two_bonus_log'];
            $p_mobile = Db::name('member')->where('id',$member_info['pid'])->value('mobile');
            if($member_info['type'] == 3){
                $group = '0';
            }else{
                $group = '1';
            }
            //二维码
            $url = web_url_str();
            $qr_code = Db::name('banner')->field("CONCAT('$url',path) as path,web_url")->where(['group_id'=>6,'state'=>1])->order('id DESC')->select();
            $array = [
                'mobile' => $member_info['mobile'],
                'group' => $group,
                'username' => $member_info['username'],
                'sex' => $member_info['sex'],
                'user_img' => $member_info['user_img'],
                'synopsis' => $member_info['synopsis'],
                'balance' => $money_info['balance'],//余额
                'bonus' => $money_info['bonus'],//奖金余额
                'one_bonus_log' => $money_info['one_bonus_log'],
                'two_bonus_log' => $money_info['two_bonus_log'],
                'bonus_close' => (string)$bonus_close,
                'p_mobile' => $p_mobile?$p_mobile:'',
                'total_push' => Db::name('member')->where('pid',$member_info['id'])->count(),
                'share_web'=>web_url_str().'/dowload/',
                'share_qrcode'=>"",
                'share_qrcode_array'=>$qr_code,
            ];
            return json(['code'=>1011,'msg'=>'查询成功','data'=>$array]);
        }else{
            return json(['code'=>1012,'msg'=>'用户不存在','data'=>""]);
        }
    }
    /**
     * 我的推荐人
     */
    public function recommend_list(){
        $data = input('post.');
        $validate_res = $this->validate($data,'HomeValidate.article');
        if($validate_res !== true){ return json(['code'=>1015,'msg'=>$validate_res]); } //数据认证
        if(getSign($data) != $data['Sign']){ return json(['code'=>1013,'msg'=>'签名错误']);} //签名认证
        if(Cache::get($data['uuid'].'_token') != $data['token']) return json(['code'=>1004,'msg'=>'用户未登录']);//登陆验证
        $MemeberModel = new MemberModel();
        $user_info = $MemeberModel->getMemberInfo('id',['uuid'=>$data['uuid']]);
        $map = [];
        switch ($data['type']){
            case 1:
                $map = ['pid'=>$user_info['id']];
                break;
            case 2:
                $map = ['gid'=>$user_info['id']];
                break;
            default:
                return json(['code'=>1015,'msg'=>'类型不存在']);
        }
        $list = $MemeberModel->getPushList('id,username,user_img,mobile',$map,$data['page'],15);
        if(count($list) > 0){
            foreach ($list as $k=>$v){
                $list[$k]['unclaimed'] = "0.00";
                $list[$k]['total_money'] = (string) Db::name('money_log')->where(['user_id'=>$v['id'],'state'=>1,'type'=>1,'trend'=>'1'])->sum('money');
            }
        }
        return json(['code'=>1011,'msg'=>'成功','data'=>$list]);
    }

    /*-----------------------------------修改信息----------------------------------------------*/
    /**
     * 修改用户手机号
     */
    public function edit_mobile(){
        $data = input('post.');
        $validate_res = $this->validate($data,'LoginValidate.member_edit_mobile');
        if($validate_res !== true){ return json(['code'=>1015,'msg'=>$validate_res]); }
        if(getSign($data) != $data['Sign']){ return json(['code'=>1013,'msg'=>'签名错误']);}
        if(Cache::get($data['uuid'].'_'.$data['phone'].'_edit_mobile') != $data['sms_code']){
            return json(['code'=>1013,'msg'=>'短信验证码错误']);
        }
        $MemberModel = new MemberModel();
        $map = [
            'uuid'=>$data['uuid']
        ];
        $member_info = $MemberModel->getMemberInfo('*',$map);
        $phone_count = Db::name('member')->where('mobile',$data['phone'])->count();
        if($phone_count){
            return json(['code'=>1012,'msg'=>'手机号码已绑定微信','data'=>'']);
        }
        if(!empty($member_info['mobile'])){
            return json(['code'=>1012,'msg'=>'手机号码已绑定，请勿重复提交','data'=>'']);
        }
        $res = $MemberModel->getEditMobile(['mobile'=>$data['phone']],$data['uuid']);
        return json($res);
    }

    /**
     * 银行卡列表
     */
    public function bank_list(){
        $data = input('post.');
        $validate_res = $this->validate($data,'HomeValidate.whole');
        if($validate_res !== true){ return json(['code'=>1015,'msg'=>$validate_res]); } //数据认证
        if(getSign($data) != $data['Sign']){ return json(['code'=>1013,'msg'=>'签名错误']);} //签名认证
        if(Cache::get($data['uuid'].'_token') != $data['token']) return json(['code'=>1004,'msg'=>'用户未登录']);//登陆验证
        $BankList = new BankListModel();
        $list = $BankList->getBankList();
        return json(['code'=>1011,'msg'=>'获取成功','data'=>$list]);
    }

    /**
     * 会员银行卡列表
     */
    public function member_bank_list(){
        $data = input('post.');
        $validate_res = $this->validate($data,'HomeValidate.whole');
        if($validate_res !== true){ return json(['code'=>1015,'msg'=>$validate_res]); } //数据认证
        if(getSign($data) != $data['Sign']){ return json(['code'=>1013,'msg'=>'签名错误']);} //签名认证
        if(Cache::get($data['uuid'].'_token') != $data['token']) return json(['code'=>1004,'msg'=>'用户未登录']);//登陆验证
        $MemberModel = new MemberModel();
        $user_info = $MemberModel->getMemberInfo('*',['uuid'=>$data['uuid']]);
        $list = Db::name('member_bank')->field('id,bank_name,bankcard,username')->where("user_id",$user_info['id'])->select();
        return json(['code'=>1011,'msg'=>'成功','data'=>$list]);
    }
    /**
     * 添加用户银行卡
     */
    public function add_member_bank(){
        $data = input('post.');
        $validate_res = $this->validate($data,'MemberValidate.edit_member_bank');
        if($validate_res !== true){ return json(['code'=>1015,'msg'=>$validate_res]); } //数据认证
        if(getSign($data) != $data['Sign']){ return json(['code'=>1013,'msg'=>'签名错误']);} //签名认证
        if(Cache::get($data['uuid'].'_token') != $data['token']) return json(['code'=>1004,'msg'=>'用户未登录']);//登陆验证
        $MemberModel = new MemberModel();
        $user_info = $MemberModel->getMemberInfo('*',['uuid'=>$data['uuid']]);
        $MemberBank = new MemberBankModel();
        $array = [
            'user_id'=>$user_info['id'],
            'bank_name'=>$data['bankname'],
            'bankcard'=>$data['bankcard'],
            'username'=>$data['username'],
        ];
        $res = $MemberBank->getAddBank($array);
        return json($res);
    }
    /**
     * 修改用户银行卡
     */
    public function edit_member_bank(){
        $data = input('post.');
        $validate_res = $this->validate($data,'MemberValidate.edit_member_bank');
        if($validate_res !== true){ return json(['code'=>1015,'msg'=>$validate_res]); } //数据认证
        if(getSign($data) != $data['Sign']){ return json(['code'=>1013,'msg'=>'签名错误']);} //签名认证
        if(Cache::get($data['uuid'].'_token') != $data['token']) return json(['code'=>1004,'msg'=>'用户未登录']);//登陆验证
        $MemberBank = new MemberBankModel();
        $array = [
            'bank_name'=>$data['bankname'],
            'bankcard'=>$data['bankcard'],
            'username'=>$data['username'],
        ];
        $res = $MemberBank->getEditBank($data['id'],$array);
        return json($res);
    }
    /**
     * 删除银行卡信息
     */
    public function del_member_del(){
        $data = input('post.');
        $validate_res = $this->validate($data,'HomeValidate.whole');
        if($validate_res !== true){ return json(['code'=>1015,'msg'=>$validate_res]); } //数据认证
        if(getSign($data) != $data['Sign']){ return json(['code'=>1013,'msg'=>'签名错误']);} //签名认证
        if(Cache::get($data['uuid'].'_token') != $data['token']) return json(['code'=>1004,'msg'=>'用户未登录']);//登陆验证
        $count = Db::name('member_bank')->where('id',$data['id'])->count();
        if($count > 0 ){
            Db::name('member_bank')->where('id',$data['id'])->delete();
            return json(['code'=>1011,'msg'=>'信息删除成功','data'=>'']);
        }else{
            return json(['code'=>1012,'msg'=>'信息已删除，或不存在','data'=>'']);
        }
    }
    /**
     * 修改我的推荐人
     */
    public function edit_recommender(){
        $data = input('post.');
        $validate_res = $this->validate($data,'MemberValidate.edit_recommender');
        if($validate_res !== true){ return json(['code'=>1015,'msg'=>$validate_res]); } //数据认证
        if(getSign($data) != $data['Sign']){ return json(['code'=>1013,'msg'=>'签名错误']);} //签名认证
        if(Cache::get($data['uuid'].'_token') != $data['token']) return json(['code'=>1004,'msg'=>'用户未登录']);//登陆验证
        $MemberModel = new MemberModel();

        $push_user_info = $MemberModel->getMemberInfo('id,gid,pid,type',['mobile'=>$data['mobile']]);
        $user_info = $MemberModel->getMemberInfo('id,pid,gid,mobile',['uuid'=>$data['uuid']]);
        if(!$push_user_info){
            return json(['code'=>1012,'msg'=>'推荐人不存在','data'=>'']);
        }
        if($push_user_info['gid'] == $user_info['id'] || $push_user_info['pid'] == $user_info['id']){
            return json(['code'=>1012,'msg'=>'账号为推荐人手机号的父级，请更换推荐人','data'=>'']);
        }
        if($user_info['mobile'] == $data['mobile']){
            return json(['code'=>1012,'msg'=>'推荐人手机号不能为自己','data'=>'']);
        }
        if($user_info){
            if($user_info['pid'] || $user_info['gid']){
                return json(['code'=>1012,'msg'=>'已绑定推荐人，请勿重复提交','data'=>'']);
            }else{
                $param = ['pid'=>$push_user_info['id'],'gid'=>$push_user_info['pid']];
                $res = $MemberModel->getEditInfo($param,['id'=>$user_info['id']],'');
                $config = privilege_config_list();
                $count = Db::name('member')->where('pid',$push_user_info['id'])->count();
                $number = 0;
                switch ($push_user_info['type']){
                    case 1://注册会员
                        switch ($count){
                            case 1:
                                $number = $config['ordinary_pushone_hongbao_number'];
                                break;
                            case 5:
                                $number = $config['ordinary_pushfive_hongbao_number'];
                                break;
                            case 10:
                                $number = $config['ordinary_pushfive_hongbao_number'];
                                break;
                        }
                        break;
                    case 2://VIP会员
                        switch ($count){
                            case 1:
                                $number = $config['vip_pushone_hongbao_number'];
                                break;
                            case 5:
                                $number = $config['vip_pushfive_hongbao_number'];
                                break;
                            case 10:
                                $number = $config['vip_pushTen_hongbao_number'];
                                break;
                        }
                        break;
                    case 3://合伙人
                        switch ($count){
                            case 1:
                                $number = $config['partner_pushone_hongbao_number'];
                                break;
                            case 5:
                                $number = $config['partner_pushfive_hongbao_number'];
                                break;
                            case 10:
                                $number = $config['partner_pushTen_hongbao_number'];
                                break;
                        }
                        break;
                }
                if($number > 0){
                    Db::name('money')->where('user_id',$push_user_info['id'])->setInc('red_push_number',$number);
                }
                return json($res);
            }
        }else{
            return json(['code'=>1012,'msg'=>'用户不存在','data'=>'']);
        }
    }

    /**
     * 修改我的简介
     */
    public function edit_synopsis(){
        $data = input('post.');
        $validate_res = $this->validate($data,'HomeValidate.whole');
        if($validate_res !== true){ return json(['code'=>1015,'msg'=>$validate_res]); } //数据认证
        if(getSign($data) != $data['Sign']){ return json(['code'=>1013,'msg'=>'签名错误']);} //签名认证
        if(Cache::get($data['uuid'].'_token') != $data['token']) return json(['code'=>1004,'msg'=>'用户未登录']);//登陆验证
        $MemberModel = new MemberModel();
        $member_info = $MemberModel->getMemberInfo('id',['uuid'=>$data['uuid']]);
        $content = input('post.content');
        $content_str = $this->match_chinese($content);
        try{
            Db::name('member')->where('id',$member_info['id'])->update(['synopsis'=>$content_str]);
            return json(['code'=>1011,'msg'=>'编辑成功','data'=>'']);
        }catch (\Exception $exception){
            return json(['code'=>1012,'msg'=>'编辑失败','data'=>'']);
        }

    }
    public function match_chinese($chars,$encoding='utf8')
    {
        $pattern =($encoding=='utf8')?'/[\x{4e00}-\x{9fa5}a-zA-Z0-9]/u':'/[\x80-\xFF]/';
        preg_match_all($pattern,$chars,$result);
        $temp =join('',$result[0]);
        return $temp;
    }
}