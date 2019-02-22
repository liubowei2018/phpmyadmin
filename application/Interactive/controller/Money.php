<?php
/**
 * Created by PhpStorm.
 * User: liubowei
 * Date: 2019/2/21
 * Time: 15:47
 */

namespace app\Interactive\controller;


use app\Interactive\model\MemberModel;
use app\Interactive\model\MoneyModel;
use think\Db;
use think\Cache;
class Money extends ApiBase
{
    /**
     * 用户资金提现
     */
    public function withdraw_cash(){
        $data = input('post.');
        $validate_res = $this->validate($data,'HomeValidate.withdraw_cash');
        if($validate_res !== true){ return json(['code'=>1015,'msg'=>$validate_res]); } //数据认证
        if(getSign($data) != $data['Sign']){ return json(['code'=>1013,'msg'=>'签名错误']);} //签名认证
        if(Cache::get($data['uuid'].'_token') != $data['token']) return json(['code'=>1004,'msg'=>'用户未登录']);//登陆验证
        $MemberModel = new MemberModel();
        $user_info = $MemberModel->getMemberInfo('id',['uuid'=>$data['uuid']]);
        $MoneyModel = new MoneyModel();
        $money_list =$MoneyModel->getMemberMoney('',['user_id'=>$user_info['id']]);
        if($money_list['balance'] < $data['money']){
            return json(['code'=>1012,'msg'=>'账户余额不足','data'=>'']);
        }else{
            Db::startTrans();
            try{
                //减去用户余额
                Db::name('money')->where(['user_id'=>$user_info['id']])->setDec('balance',$data['money']);
                $money_log = [
                    'user_id'=>$user_info['id'],
                    'type'=>'1',
                    'money'=>$data['money'],
                    'original'=>$money_list['balance'],
                    'now'=>$money_list['balance']-$data['money'],
                    'state'=>'2',
                    'info'=>'用户资金提现',
                    'trend'=>'3',
                    'create_time'=>time(),
                ];
                Db::name('money_log')->insert($money_log);
                //创建提现订单
                $reflect_log = [
                    'order_number'=>'T'.time().rand(10000,99999).$user_info['id'],
                    'money'=>$data['money'],
                    'user_id'=>$user_info['id'],
                    'type'=>$data['type'],
                    'create_time'=>time(),
                    'state'=>0,
                ];
                Db::name('reflect_list')->insert($reflect_log);
                Db::commit();
                return json(['code'=>1011,'msg'=>'余额提现申请成功','data'=>'']);
            }catch (\Exception $exception){
                Db::rollback();
                return json(['code'=>1012,'msg'=>'余额提现失败','data'=>'']);
            }
        }
    }
    /**
     * 用户资金提现记录
     */
    public function withdraw_cash_log(){
        $data = input('post.');
        $validate_res = $this->validate($data,'HomeValidate.whole');
        if($validate_res !== true){ return json(['code'=>1015,'msg'=>$validate_res]); } //数据认证
        if(getSign($data) != $data['Sign']){ return json(['code'=>1013,'msg'=>'签名错误']);} //签名认证
        if(Cache::get($data['uuid'].'_token') != $data['token']) return json(['code'=>1004,'msg'=>'用户未登录']);//登陆验证
        $MemberModel = new MemberModel();
        $user_info = $MemberModel->getMemberInfo('id',['uuid'=>$data['uuid']]);
        $page = input('post.page');
        $page = $page?$page:1;
        $list = Db::name('reflect_list')->field('order_number,money,type,state,create_time')->where(['user_id'=>$user_info['id']])->page($page,15)->order('create_time DESC')->select();
        if(count($list) > 0){
            foreach ($list as $k=>$v){
                switch ($v['type']){
                    case 1:
                        $list[$k]['type'] = '微信';
                        break;
                    case 2:
                        $list[$k]['type'] = '银行卡';
                        break;
                }
                switch ($v['state']){
                    case 0:
                        $list[$k]['state'] = '未审核';
                        break;
                    case 1:
                        $list[$k]['state'] = '已完成';
                        break;
                    case 2:
                        $list[$k]['state'] = '已驳回';
                        break;
                }
                $list[$k]['create_time'] = date('Y-m-d H:i:s',$v['create_time']);
            }
        }
        return json(['code'=>1011,'msg'=>'成功','data'=>$list]);

    }
    /**
     * 资金流动列表
     */
    public function capital_record(){
        $data = input('post.');
        $validate_res = $this->validate($data,'HomeValidate.whole');
        if($validate_res !== true){ return json(['code'=>1015,'msg'=>$validate_res]); } //数据认证
        if(getSign($data) != $data['Sign']){ return json(['code'=>1013,'msg'=>'签名错误']);} //签名认证
        if(Cache::get($data['uuid'].'_token') != $data['token']) return json(['code'=>1004,'msg'=>'用户未登录']);//登陆验证
        $MemberModel = new MemberModel();
        $user_info = $MemberModel->getMemberInfo('id',['uuid'=>$data['uuid']]);
        $page = input('post.page');
        $page = $page?$page:1;
        $list = Db::name('money_log')->field('state,money,info,create_time')->where(['user_id'=>$user_info['id'],'type'=>1])->page($page,15)->order('create_time DESC')->select();
        if(count($list) > 0) {
            foreach ($list as $k => $v) {
                switch ($v['state']){
                    case 1:
                        $list[$k]['state'] = '增加';
                        break;
                    case 2:
                        $list[$k]['state'] = '减少';
                        break;
                }
                $list[$k]['create_time'] = date('Y-m-d H:i:s',$v['create_time']);
            }
        }
        return json(['code'=>1011,'msg'=>'成功','data'=>$list]);
    }
    /**
     * 奖励金转余额
     */
    public function transformation(){
        $data = input('post.');
        $validate_res = $this->validate($data,'HomeValidate.whole');
        if($validate_res !== true){ return json(['code'=>1015,'msg'=>$validate_res]); } //数据认证
        if(getSign($data) != $data['Sign']){ return json(['code'=>1013,'msg'=>'签名错误']);} //签名认证
        if(Cache::get($data['uuid'].'_token') != $data['token']) return json(['code'=>1004,'msg'=>'用户未登录']);//登陆验证
        $MemberModel = new MemberModel();
        $user_info = $MemberModel->getMemberInfo('id',['uuid'=>$data['uuid']]);
        $MoneyModel = new MoneyModel();
        $money_list =$MoneyModel->getMemberMoney('',['user_id'=>$user_info['id']]);
        if($money_list['bonus'] > 0){
            Db::startTrans();
            try{
                //增加用户余额
                Db::name('money')->where(['user_id'=>$user_info['id']])->setInc('balance',$money_list['bonus']);
                $money_log = [
                    'user_id'=>$user_info['id'],
                    'type'=>'1',
                    'money'=>$money_list['bonus'],
                    'original'=>$money_list['balance'],
                    'now'=>$money_list['balance']+$money_list['bonus'],
                    'state'=>'1',
                    'info'=>'奖励金转入余额',
                    'trend'=>'4',
                    'create_time'=>time(),
                ];
                Db::name('money_log')->insert($money_log);
                Db::name('money')->where(['user_id'=>$user_info['id']])->setDec('bonus',$money_list['bonus']);
                $money_log = [
                    'user_id'=>$user_info['id'],
                    'type'=>'4',
                    'money'=>$money_list['bonus'],
                    'original'=>$money_list['bonus'],
                    'now'=>0,
                    'state'=>'2',
                    'info'=>'奖励金转入余额',
                    'trend'=>'4',
                    'create_time'=>time(),
                ];
                Db::name('money_log')->insert($money_log);
                Db::commit();
                return json(['code'=>1011,'msg'=>'转入余额成功','data'=>'']);
            }catch (\Exception $exception){
                Db::rollback();
                return json(['code'=>1012,'msg'=>'转入余额失败','data'=>'']);
            }
        }else{
            return json(['code'=>1012,'msg'=>'账户奖励金不足','data'=>'']);
        }
    }
    /**
     * 会员升级
     */
    public function upgrade_member(){
        $data = input('post.');
        $validate_res = $this->validate($data,'HomeValidate.whole');
        if($validate_res !== true){ return json(['code'=>1015,'msg'=>$validate_res]); } //数据认证
        if(getSign($data) != $data['Sign']){ return json(['code'=>1013,'msg'=>'签名错误']);} //签名认证
        if(Cache::get($data['uuid'].'_token') != $data['token']) return json(['code'=>1004,'msg'=>'用户未登录']);//登陆验证
        $MemberModel = new MemberModel();
        $user_info = $MemberModel->getMemberInfo('id',['uuid'=>$data['uuid']]);
        $type = input('post.type');
        $config = privilege_config_list();
        switch ($this){
            case 2://vip
                if($config['vip_state'] == 1){
                    $order_number = $this->upgrade_add_order($user_info['id'],$config['vip_money'],2);
                }else{
                    return json(['code'=>1012,'msg'=>'升级vip暂未开放','data'=>'']);
                }
                break;
            case 3://合伙人
                if($config['vip_state'] == 1){
                    if($user_info['type'] == 2){ //已经是VIP 升级 合伙人补差额
                        $order_number = $this->upgrade_add_order($user_info['id'],$config['partner_money'],3);
                    }else{  //普通会员升级
                        $order_number = $this->upgrade_add_order($user_info['id'],$config['partner_money'] - $config['vip_money'],3);
                    }
                }else{
                    return json(['code'=>1012,'msg'=>'升级合伙人暂未开放','data'=>'']);
                }
                break;
            default:
                return json(['code'=>1012,'msg'=>'请选择购买类型','data'=>'']);
        }
    }

    /**
     * @param $user_id
     * @param $money
     * @param $grade
     */
    private function upgrade_add_order($user_id,$money,$grade){
              $array = [
                  'order_number'=>'S'.time().rand(10000,99999).$user_id,
                  'money'=>$money,
                  'user_id'=>$user_id,
                  'type'=>'0',
                  'create_time'=>time(),
                  'state'=>'0',
                  'grade'=>$grade,
              ];
    }
}