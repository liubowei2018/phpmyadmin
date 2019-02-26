<?php
/**
 * Created by PhpStorm.
 * User: liubowei
 * Date: 2019/2/26
 * Time: 13:54
 */

namespace app\Interactive\controller;

use app\Interactive\model\MemberModel;
use think\Cache;
use think\Db;

class Wechat extends ApiBase
{
    /**
     * 分享朋友圈增加红包次数
     */
    public function share(){
        $data = input('post.');
        $validate_res = $this->validate($data,'HomeValidate.whole');
        if($validate_res !== true){ return json(['code'=>1015,'msg'=>$validate_res]); } //数据认证
        if(getSign($data) != $data['Sign']){ return json(['code'=>1013,'msg'=>'签名错误']);} //签名认证
        if(Cache::get($data['uuid'].'_token') != $data['token']) return json(['code'=>1004,'msg'=>'用户未登录']);//登陆验证
        $privilege_config = privilege_config_list();
        $app_config       = app_config_list();
        $MemberModel = new MemberModel();
        $user_info = $MemberModel->getMemberInfo('id,pid,gid,type',['uuid'=>$data['uuid']]);
        $money_info = Db::name('money')->where('user_id',$user_info['id'])->find();
        $number = 0;
        switch ($user_info['type']){
            case 1:
                switch ($data['state']){
                    case 1://朋友圈
                        $number = $privilege_config['ordinary_quan_hongbao_number'];
                        break;
                    case 2://微信群
                        $number = $privilege_config['ordinary_qun_hongbao_number'];
                        break;
                }
                break;
            case 2:
                switch ($data['state']){
                    case 1://朋友圈
                        $number = $privilege_config['vip_quan_hongbao_number'];
                        break;
                    case 2://微信群
                        $number = $privilege_config['vip_qun_hongbao_number'];
                        break;
                }
                break;
            case 3:
                switch ($data['state']){
                    case 1://朋友圈
                        $number = $privilege_config['partner_quan_hongbao_number'];
                        break;
                    case 2://微信群
                        $number = $privilege_config['partner_qun_hongbao_number'];
                        break;
                }
                break;
        }
        if($app_config['sharing_upper_limit'] > 0){
            if($money_info['red_today_number']+$number > $app_config['sharing_upper_limit']){
                return json(['code'=>1012,'msg'=>'今天分享已达上限，请明日在继续分享','data'=>'']);
            }
        }
        Db::startTrans();
        try{
            Db::name('money')->where('user_id',$user_info['id'])->setInc('red_today_number',$number);
            Db::name('money')->where('user_id',$user_info['id'])->setInc('total_red_number',$number);
            Db::commit();
            return json(['code'=>1011,'msg'=>'分享成功','data'=>'']);
        }catch (\Exception $exception){
            Db::rollback();
            return json(['code'=>1012,'msg'=>'数据开小差了，请稍后再试','data'=>'']);
        }
    }
}