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
        $member_info = $MmemberModel->getMemberInfo('id,mobile,pid',['uuid'=>$data['uuid']]);
        if($member_info){
            $money_info = $MoneyModel->getMemberMoney('*', ['user_id' => $member_info['id']]);
            $bonus_close = $money_info['one_bonus_log'] + $money_info['one_bonus_log'];
            $p_mobile = Db::name('member')->where('id',$member_info['pid'])->value('mobile');
            $array = [
                'mobile' => $member_info['mobile'],
                'balance' => $money_info['balance'],//余额
                'bonus' => $money_info['bonus'],//奖金余额
                'one_bonus_log' => $money_info['one_bonus_log'],
                'two_bonus_log' => $money_info['one_bonus_log'],
                'bonus_close' => (string)$bonus_close,
                'p_mobile' => $p_mobile?$p_mobile:'',
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
        $list = $MemeberModel->getPushList('username,user_img,mobile',$map,$data['page'],15);
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
     * 修改用户银行卡
     */
    public function edit_member_bank(){
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
            'bank_name'=>$data['bank_name'],
            'bankcard'=>$data['bankcard'],
            'username'=>$data['username'],
        ];
        $res = $MemberBank->getEditBank($user_info['id'],$array);
        return json($res);
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

        $push_user_info = $MemberModel->getMemberInfo('id,pid',['mobile'=>$data['mobile']]);

        if(!$push_user_info){
            return json(['code'=>1012,'msg'=>'推荐人不存在','data'=>'']);
        }

        $user_info = $MemberModel->getMemberInfo('id,pid,gid',['uuid'=>$data['uuid']]);
        if($user_info){
            if($user_info['pid'] || $user_info['gid']){
                return json(['code'=>1012,'msg'=>'已绑定推荐人，请勿重复提交','data'=>'']);
            }else{
                $param = ['pid'=>$push_user_info['id'],'gid'=>$push_user_info['pid']];

                $res = $MemberModel->getEditInfo($param,['id'=>$user_info['id']],'');
                return json($res);
            }
        }else{
            return json(['code'=>1012,'msg'=>'用户不存在','data'=>'']);
        }
    }
}