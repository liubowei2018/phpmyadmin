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
use think\Cache;
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
}