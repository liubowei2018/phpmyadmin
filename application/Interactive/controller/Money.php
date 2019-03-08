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
        $bank_list = Db::name('member_bank')->where(['user_id'=>$user_info['id'],'id'=>$data['bank_id']])->find();
        if($data['type'] == 2){
            if(!$bank_list){
                return json(['code'=>1012,'msg'=>'银行卡不存在','data'=>'']);
            }
        }
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
                if($data['type'] == 2){
                    $reflect_log['bank_name'] = $bank_list['bank_name'];
                    $reflect_log['bank_card'] = $bank_list['bankcard'];
                    $reflect_log['bank_user'] = $bank_list['username'];
                }
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
                        $list[$k]['state'] = '+';
                        break;
                    case 2:
                        $list[$k]['state'] = '-';
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
        $user_info = $MemberModel->getMemberInfo('id,type',['uuid'=>$data['uuid']]);
        $type = input('post.type');
        $state = input('post.state');
        $config = privilege_config_list();
        switch ($type){
            case 2://vip
                if($config['vip_state'] == 1){
                    if($user_info['type'] == 2){
                        return json(['code'=>1012,'msg'=>'您已经是VIP会员，请勿重复提交','data'=>'']);
                    }
                    $order_number = $this->upgrade_add_order($user_info['id'],$config['vip_money'],2);
                    $str = '升级VIP';
                }else{
                    return json(['code'=>1012,'msg'=>'升级vip暂未开放','data'=>'']);
                }
                break;
            case 3://合伙人
                if($config['vip_state'] == 1){
                    if($user_info['type'] == 3){
                        return json(['code'=>1012,'msg'=>'您已经是合伙人，请勿重复提交','data'=>'']);
                    }
                    if($user_info['type'] == 2){ //已经是VIP 升级 合伙人补差额
                        $order_number = $this->upgrade_add_order($user_info['id'],$config['partner_money'] - $config['vip_money'],3);
                        $str = '升级合伙人';
                    }else{  //普通会员升级
                        $order_number = $this->upgrade_add_order($user_info['id'],$config['partner_money'],3);
                        $str = '升级合伙人';
                    }
                }else{
                    return json(['code'=>1012,'msg'=>'升级合伙人暂未开放','data'=>'']);
                }
                break;
            default:
                return json(['code'=>1012,'msg'=>'请选择购买类型','data'=>'']);
        }
        if($order_number['code'] == 1011){ //预下单成功
            switch ($state){
                case 1://微信支付
                    $Wxpay = new Wxpay();
                    $url = web_url_str().'/Interactive/money/pay_notify';
                    $wx_pay_one = $Wxpay->getPrePayOrder('购买会员',$order_number['data'],0.01*100,$url);
                    $res = $Wxpay->getOrder($wx_pay_one['prepay_id']);
                    return json(['code'=>1011,'msg'=>'成功','data'=>$res]);
                    break;
                case 2://余额支付
                    $res = $this->upgrade_pay_balance($order_number['data'],$user_info['id'],$str);
                    return json($res);
                    break;
                default:
                    return json(['code'=>1012,'msg'=>'请选择支付类型','data'=>'']);
            }
        }else{
            return json(['code'=>1012,'msg'=>'预下单失败，请稍后再试','data'=>'']);
        }
    }

    /**
     * 支付回调地址
     */
    public function pay_notify(){
        //获取返回的xml
        $testxml = file_get_contents("php://input");
        //将xml转化为json格式
        $jsonxml = json_encode(simplexml_load_string($testxml, 'SimpleXMLElement', LIBXML_NOCDATA));
        //转成数组
        $result = json_decode($jsonxml, true);
        //验签
        $Wxpay = new Wxpay();
        $sign = $Wxpay->getSign($result);
        if($result['sign'] == $sign){
            //验证回调
            if($result['result_code'] == 'SUCCESS' || $result['return_code'] == 'SUCCESS'){
                //处理业务逻辑
                Db::startTrans();
                try{
                    //查询订单
                    $order_info = Db::name('upgrade_list')->where('order_number',$result['out_trade_no'])->find();
                    if($order_info['state'] == 1){
                        //订单已处理
                        Db::rollback();
                        echo '<xml><return_code><![CDATA[SUCCESS]]></return_code><return_msg><![CDATA[OK]]></return_msg></xml>';
                    }else{
                        //未处理
                        //修改订单状态
                       Db::name('upgrade_list')->where('id',$order_info['id'])->update(['state'=>1,'wx_order_number'=>$result['transaction_id']]);
                       //修改用户等级
                        //进行二级分润
                        $this->two_level_award($order_info['user_id'],$order_info['money']);
                        //修改可以领取的红包个数
                        $config = privilege_config_list();
                        switch ($order_info['grade']){
                            case 2:
                                Db::name('member')->where('id',$order_info['user_id'])->update(['type'=>2]);
                                $today_red_number = $config['vip_today_hongbao_number'];
                                Db::name('money')->where('user_id',$order_info['user_id'])->update(['red_number'=>$config['vip_today_hongbao_number']]);
                                break;
                            case 3:
                                Db::name('member')->where('id',$order_info['user_id'])->update(['type'=>3]);
                                $today_red_number = $config['partner_today_hongbao_number'];
                                Db::name('money')->where('user_id',$order_info['user_id'])->update(['red_number'=>$config['partner_today_hongbao_number']]);
                                break;
                        }
                    }
                    Db::name('money')->where('user_id',$order_info['user_id'])->setInc('total_red_number',$today_red_number);
                    Db::commit();
                    echo '<xml><return_code><![CDATA[SUCCESS]]></return_code><return_msg><![CDATA[OK]]></return_msg></xml>';
                }catch (\Exception $exception){
                    Db::rollback();
                    echo 'error';
                }
            }else{
                echo 'error';
            }
        }else{
            echo 'error';
        }

    }


    /**
     * 余额 升级 支付
     * $order_number  订单号
     * $user_id       用户id
     * $str           备注信息
     */
    private function upgrade_pay_balance($order_number,$user_id,$str){

        $MoneyModel = new MoneyModel();
        $money_list =$MoneyModel->getMemberMoney('balance',['user_id'=>$user_id]);
        $order_info = Db::name('upgrade_list')->where(['order_number'=>$order_number,'user_id'=>$user_id])->find();
        if($order_info){
            if($order_info['state'] != 1){
                //验证资金是否充足
                if($money_list['balance'] >= $order_info['money']){
                    /*-----------------修改订单  更新用户等级-------------------*/
                        Db::startTrans();
                        try{
                            //修改用户资金
                            Db::name('money')->where('user_id',$user_id)->setDec('balance',$order_info['money']);
                            $money_log = [
                                'user_id'=>$user_id,
                                'type'=>'1',
                                'money'=>$order_info['money'],
                                'original'=>$money_list['balance'],
                                'now'=>$money_list['balance']-$order_info['money'],
                                'state'=>'2',
                                'info'=>$str,
                                'trend'=>'2',
                                'create_time'=>time(),
                            ];
                            Db::name('money_log')->insert($money_log);
                            //修改订单状态
                            Db::name('upgrade_list')->where('order_number',$order_number)->update(['state'=>1,'type'=>2]);
                            //进行二级分润
                            $this->two_level_award($user_id,$order_info['money']);
                            //修改可以领取的红包个数
                            $config = privilege_config_list();
                            switch ($order_info['grade']){
                                case 2:
                                    Db::name('member')->where('id',$user_id)->update(['type'=>2]);
                                    $today_red_number = $config['vip_today_hongbao_number'];
                                    Db::name('money')->where('user_id',$user_id)->update(['red_number'=>$config['vip_today_hongbao_number']]);
                                    break;
                                case 3:
                                    Db::name('member')->where('id',$user_id)->update(['type'=>3]);
                                    $today_red_number = $config['partner_today_hongbao_number'];
                                    Db::name('money')->where('user_id',$user_id)->update(['red_number'=>$config['partner_today_hongbao_number']]);
                                    break;
                            }
                            Db::name('money')->where('user_id',$order_info['user_id'])->setInc('total_red_number',$today_red_number);
                            Db::commit();
                            return ['code'=>1011,'msg'=>'购买成功','data'=>''];
                        }catch (\Exception $exception){
                            Db::rollback();
                            return ['code'=>1011,'msg'=>'购买失败','data'=>$exception->getMessage()];
                        }
                    /*-----------------修改订单  更新用户等级-------------------*/

                }else{
                    return ['code'=>1012,'msg'=>'账户余额不足','data'=>''];
                }
            }else{
                return ['code'=>1012,'msg'=>'订单已支付，请勿重复提交','data'=>''];
            }
        }else{
            return ['code'=>1012,'msg'=>'订单不存在','data'=>''];
        }
    }

    /**
     * 创建升级订单
     * @param $user_id
     * @param $money
     * @param $grade
     * @return array
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
        try{
            Db::name('upgrade_list')->insert($array);
            return ['code'=>1011,'data'=>$array['order_number'],'money'=>$money];
        }catch (\Exception $exception){
            return ['code'=>1012,'data'=>''];
        }
    }

    /**
     * 会员升级二级分润
     * $user_id 推荐人账号
     * $money 分润金额
     */
    private function two_level_award($user_id,$money)
    {
        $config = privilege_config_list();
        $user_info = Db::name('member')->where('id',$user_id)->find();
        if($user_id['pid'] > 0){  //一级
            $p_user_info = Db::name('member')->where('id',$user_info['pid'])->find();
            $p_money_list = Db::name('money')->where('user_id',$p_user_info['id'])->find();
            if($p_user_info){
                switch ($p_user_info['type']){
                    case 1: //普通会员
                        $p_bonus = $money*$config['ordinary_one_upgrade']/100;
                        break;
                    case 2: //vip会员
                        $p_bonus = $money*$config['vip_one_upgrade']/100;
                        break;
                    case 3: //合伙人
                        $p_bonus = $money*$config['partner_one_upgrade']/100;
                        break;
                }
            }
            Db::name('money')->where('user_id',$p_user_info['id'])->setInc('bonus',$p_bonus);
            $money_log = [
                'user_id'=>$user_id,
                'type'=>'4',
                'money'=>$p_bonus,
                'original'=>$p_money_list['bonus'],
                'now'=>$p_money_list['bonus']+$p_bonus,
                'state'=>'1',
                'info'=>$user_info['mobile'].'升级奖励',
                'trend'=>'2',
                'create_time'=>time(),
            ];
            Db::name('money_log')->insert($money_log);
        }
        if($user_id['gid'] > 0){  //二级
            $g_user_info = Db::name('member')->where('id',$user_info['gid'])->find();
            $g_money_list = Db::name('money')->where('user_id',$g_user_info['id'])->find();
            if($g_user_info){
                switch ($g_user_info['type']){
                    case 1: //普通会员
                        $g_bonus = $money*$config['ordinary_two_upgrade']/100;
                        break;
                    case 2: //vip会员
                        $g_bonus = $money*$config['vip_two_upgrade']/100;
                        break;
                    case 3: //合伙人
                        $g_bonus = $money*$config['partner_two_upgrade']/100;
                        break;
                }
                Db::name('money')->where('user_id',$g_user_info['id'])->setInc('bonus',$g_bonus);
                $money_log = [
                    'user_id'=>$user_id,
                    'type'=>'4',
                    'money'=>$g_bonus,
                    'original'=>$g_money_list['bonus'],
                    'now'=>$g_money_list['bonus']+$g_bonus,
                    'state'=>'1',
                    'info'=>$user_info['mobile'].'升级奖励',
                    'trend'=>'2',
                    'create_time'=>time(),
                ];
                Db::name('money_log')->insert($money_log);
            }
        }
    }

}