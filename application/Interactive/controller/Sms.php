<?php
/**
 * Created by PhpStorm.
 * User: liubowei
 * Date: 2019/2/19
 * Time: 9:15
 */

namespace app\Interactive\controller;


use app\Interactive\model\MemberModel;
use think\Cache;
use think\Controller;

class Sms extends Controller
{
    /**
     * 登录短信
     */
    public function entry_sms(){
        $data = input('post.');
        //验证数据
        $validate_res = $this->validate($data,'LoginValidate.entry_sms');
        if($validate_res !== true){ return json(['code'=>1015,'msg'=>$validate_res]); }
        if(getSign($data) != $data['Sign']){ return json(['code'=>1013,'msg'=>'签名错误']); }
        $MemberModel = new MemberModel();
        $map = [
            'mobile'=>$data['phone']
        ];
        $member_info = $MemberModel->getMemberInfo('*',$map);
        if($member_info){
            $number = rand(100000,999999);
            $number = 123456;
            $sms_str =  "【红包】短信验证码为：$number ,短信有效期为5分钟，如非本人操作请忽略此条信息";
            Cache::set($data['phone'].'_entry_sms',$number,300);
            $res = $this->Sending_SMS($data['phone'],$sms_str,'');
            return json($res);
        }else{
            return json(['code'=>1012,'msg'=>'请微信注册后在进行短信登录','data'=>'']);
        }
    }

    /**
     * 补全用户手机号
     */
    public function edit_mobile(){
        $data = input('post.');
        //验证数据
        $validate_res = $this->validate($data,'LoginValidate.edit_mobile');
        if($validate_res !== true){ return json(['code'=>1015,'msg'=>$validate_res]); }
        if(getSign($data) != $data['Sign']){ return json(['code'=>1013,'msg'=>'签名错误']); }
        $MemberModel = new MemberModel();
        $map = [
            'uuid'=>$data['uuid']
        ];
        $member_info = $MemberModel->getMemberInfo('*',$map);
        if($member_info){
            $mobile_info = $MemberModel->getMemberInfo('*',['mobile'=>$data['phone']]);
            if($mobile_info){
                return json(['code'=>1012,'msg'=>'手机号吗已绑定','data'=>'']);
            }
            $config_list = app_config_list();
            if($config_list['app_sms'] == 1){
                $number = rand(100000,999999);
            }else{
                $number = 123456;
            }
            $sms_str =  "【红包】短信验证码为：$number ,短信有效期为5分钟，如非本人操作请忽略此条信息";
            Cache::set($data['uuid'].'_'.$data['phone'].'_edit_mobile',$number,300);
            $res = $this->Sending_SMS($data['phone'],$sms_str,'');
            return json($res);
        }else{
            return json(['code'=>1012,'msg'=>'请微信注册后','data'=>'']);
        }
    }
    /**
     * 发送短信
     */
    private function Sending_SMS($mobile,$str,$type){
        $config_list = app_config_list();
        $url="http://121.42.138.95:8888/sms.aspx";
        $post_data['action']="send";
        $post_data['userid']="610";
        $post_data['account']="rongdian";
        $post_data['password']="rongdian666...";
        $post_data['mobile']=$mobile;
        $post_data['content']=$str;
        $post_data['sendTime']="";
        $post_data['extno']="";
        $o = "";
        foreach ( $post_data as $k => $v )
        {
            $o.= "$k=" . urlencode( $v ). "&" ;
        }
        $post_data = substr($o,0,-1);
        if($config_list['app_sms'] == 1){
            $result = post_curls($url,$post_data);
            $objectxml = simplexml_load_string($result);//将文件转换成 对象
            $xmljson= json_encode($objectxml );//将对象转换个JSON
            $xmlarray=json_decode($xmljson,true);//将json转换成数组
        }else{
            $xmlarray['message'] = 'ok';
        }
        if($xmlarray['message'] == 'ok'){
            return ['code'=>1011,'msg'=>'短信发送成功','data'=>''];
        }else{
            return ['code'=>1012,'msg'=>'短信发送失败','data'=>''];
        }
    }
}