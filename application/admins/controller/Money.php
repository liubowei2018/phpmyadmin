<?php
/**
 * Created by PhpStorm.
 * User: liubowei
 * Date: 2019/2/23
 * Time: 14:38
 */

namespace app\admins\controller;


use app\admins\model\MoneyLogModel;
use app\admins\model\ReflectListModel;
use think\Db;

class Money extends Base
{
    /**
     * 资金列表
     */
    public function money_log(){
        $MoneyLogModel = new MoneyLogModel();
        if(request()->isPost()){
            $data = input('post.');
            $key = input('post.key');
            $type = input('post.type');
            $state = input('post.state');
            $stare_time = input('post.stare_time');
            $end_time = input('post.end_time');
            $map = [];
            if(!empty($key)){
                $map['m.username|m.mobile'] = ['like','%'.$key.'%'];
            }
            if(!empty($type)){
                $map['l.type'] = $type;
            }
            if(!empty($state)){
                $map['l.state'] = $state;
            }
            if(!empty($stare_time)){
                $stare_time = $stare_time.' 00:00:00';
                $map['l.create_time'] = ['>= time',$stare_time];
            }
            if(!empty($end_time)){
                $end_time = $end_time.' 23:59:59';
                $map['l.create_time'] = ['<= time',$end_time];
            }
            if(!empty($stare_time) && !empty($end_time)){
                $map['l.create_time'] = ['between time',[$stare_time,$end_time]];
            }
            $page = input('post.page');
            $rows = input('post.rows');
            $page = $page?$page:1;
            $count = Db::name('money_log')->alias('l')->where($map)->join('member m','m.id = l.user_id')->count();
            $list= $MoneyLogModel->getLogList('l.*,m.username,m.mobile',$map,$page,$rows,'create_time DESC');
            return json(['count'=>$count,'list'=>$list,'page'=>$page]);
        }
        return $this->fetch();
    }

    /*----------------------------------------资金提现---------------------------------------------------*/
    /**
     * 资金提现申请表
     */
    public function propose(){
        $ReflectListModel = new ReflectListModel();
        if(request()->isPost()){
            $key = input('post.key');
            $type = input('post.type');
            $state = input('post.state');
            $stare_time = input('post.stare_time');
            $end_time = input('post.end_time');
            $map = [];
            if(!empty($key)){
                $map['m.username|m.mobile'] = ['like','%'.$key.'%'];
            }
            if(!empty($type)){
                $map['l.type'] = $type;
            }
            if(!empty($state)){
                $map['l.state'] = $state;
            }
            if($state === '0'){
                $map['l.state'] = $state;
            }
            if(!empty($stare_time)){
                $stare_time = $stare_time.' 00:00:00';
                $map['l.create_time'] = ['>= time',$stare_time];
            }
            if(!empty($end_time)){
                $end_time = $end_time.' 23:59:59';
                $map['l.create_time'] = ['<= time',$end_time];
            }
            if(!empty($stare_time) && !empty($end_time)){
                $map['l.create_time'] = ['between time',[$stare_time,$end_time]];
            }
            $page = input('post.page');
            $rows = input('post.rows');
            $page = $page?$page:1;
            $count = Db::name('reflect_list')->alias('l')->where($map)->join('member m','m.id = l.user_id')->count();
            $list= $ReflectListModel->getLogList('l.*,m.username,m.mobile',$map,$page,$rows,'create_time DESC');
            return json(['count'=>$count,'list'=>$list,'page'=>$page]);
        }
        return $this->fetch();
    }
    /**
     * 确认提现
     */
    public function confirm_propose(){
         $id = input('post.id');
         $info = Db::name('reflect_list')->where('id',$id)->find();
        if($info['state'] > 0){
            return json(['code'=>1012,'msg'=>'订单不是未审核状态']);
        }
        switch ($info['type']){
            case 1://微信提现
                return json(['code'=>1012,'msg'=>'微信到账暂未开通']);
                break;
            case 2://银行卡提现
                Db::startTrans();
                try{
                    //修改订单状态
                    Db::name('reflect_list')->where('id',$id)->update(['state'=>1]);
                    //管理员操作记录
                    getAddAdminOrderLog($info['user_id'],'提现订单确认',$info['order_number'],$this->admin_uid,$this->admin_name);
                    Db::commit();
                    return json(['code'=>1011,'msg'=>'确认提现成功']);
                }catch (\Exception $exception){
                    Db::rollback();
                    return json(['code'=>1012,'msg'=>'确认提现失败']);
                }
                break;
            default:
                return json(['code'=>1012,'msg'=>'提现方式暂未开放']);
        }
    }
    /**
     * 驳回提现
     */
    public function reject_propose(){
        $id = input('post.id');
        $info = Db::name('reflect_list')->where('id',$id)->find();
        if($info['state'] > 0){
            return json(['code'=>1012,'msg'=>'订单不是未审核状态']);
        }
        Db::startTrans();
        try{
            //修改申请信息
            Db::name('reflect_list')->where('id',$id)->update(['state'=>2]);
            //查询用户资金
            $money = Db::name('money')->where('user_id',$info['user_id'])->find();
            //修改用户资金
            Db::name('money')->where('user_id',$info['user_id'])->setInc('balance',$info['money']);
            //资金记录
            getAddMoneyLog($info['user_id'],$info['money'],$money['balance'],$money['balance']+$info['money'],'1','1','提现申请驳回',$info['order_number'],3,time(),'');
            //管理员操作记录
            getAddAdminOrderLog($info['user_id'],'提现订单驳回',$info['order_number'],$this->admin_uid,$this->admin_name);
            Db::commit();
            return json(['code'=>1011,'msg'=>'驳回提申请成功']);
        }catch (\Exception $exception){
            Db::rollback();
            return json(['code'=>1012,'msg'=>'驳回提现申请失败']);
        }
    }
    /*----------------------------------------资金提现----------------------------------------------------*/
    /**
     * 会员或合伙人发放奖励
     */
    public function reward(){
        $vip_count = Db::name('member')->where('type',2)->count();
        $hehuoren_count = Db::name('member')->where('type',3)->count();
        $this->assign('vip_count',$vip_count);
        $this->assign('hehuoren_count',$hehuoren_count);
        return $this->fetch();
    }

    /**
     * 发放奖励
     */
    public function grant_reward(){
        $data = input('post.');
        switch ($data['type']){
            case 2: //发放VIP奖励
                $res = $this->vip_reward($data['money']);
                return json($res);
                break;
            case 3: //发放合伙人奖励
                $res = $this->hehuoren_reward($data['money']);
                return json($res);
                break;
            default:
                return json(['code'=>1012,'msg'=>'发放类型不存在']);
        }
    }

    /**
     * 发放VIP奖励
     */
    private function vip_reward($money){
        Db::startTrans();
        try{
            Db::query("INSERT INTO think_money_log (user_id,money,original,now,type,state,info,create_time) (SELECT u.id as user_id,$money as moeny,m.balance as original,(m.balance+$money) as now_money,'1' as type,'1' as state,'VIP会员奖励' as info,unix_timestamp(now()) as create_time
FROM think_member as u INNER JOIN think_money as m ON m.user_id = u.id WHERE u.type = 2 AND u.state = 1);");
            Db::query("UPDATE think_money as m INNER JOIN think_member as u ON u.id = m.user_id SET m.balance = m.balance + $money WHERE u.type = 2 AND u.state = 1");
            $admin_id = $this->admin_uid;
            $admin_name = $this->admin_name;
            Db::query("INSERT INTO think_admin_money (user_id,money,type,state,info,admin_id,admin_name,add_time) 
(SELECT id as user_id,$money as money,'1' as type,'1' as state,'发放VIP奖励' as info,$admin_id as admin_id,'$admin_name' as name,unix_timestamp(now()) as add_time FROM think_member WHERE type = 2 AND state = 1)");
            Db::commit();
            return ['code'=>1011,'msg'=>'发放VIP奖励完毕'];
        }catch (\Exception $exception){
            Db::rollback();
            return ['code'=>1012,'msg'=>$exception->getMessage()];
        }
    }

    /**
     * 发放合伙人奖励
     */
    private function hehuoren_reward($money){
        Db::startTrans();
        try{
            Db::query("INSERT INTO think_money_log (user_id,money,original,now,type,state,info,create_time) (SELECT u.id as user_id,$money as moeny,m.balance as original,(m.balance+$money) as now_money,'1' as type,'1' as state,'VIP会员奖励' as info,unix_timestamp(now()) as create_time
FROM think_member as u INNER JOIN think_money as m ON m.user_id = u.id WHERE u.type = 3 AND u.state = 1);");
            Db::query("UPDATE think_money as m INNER JOIN think_member as u ON u.id = m.user_id SET m.balance = m.balance + $money WHERE u.type = 3 AND u.state = 1");
            $admin_id = $this->admin_uid;
            $admin_name = $this->admin_name;
            Db::query("INSERT INTO think_admin_money (user_id,money,type,state,info,admin_id,admin_name,add_time) 
(SELECT id as user_id,$money as money,'1' as type,'1' as state,'发放合伙人奖励' as info,$admin_id as admin_id,'$admin_name' as name,unix_timestamp(now()) as add_time FROM think_member WHERE type = 3 AND state = 1)");
            Db::commit();
            return ['code'=>1011,'msg'=>'发放合伙人奖励完毕'];
        }catch (\Exception $exception){
            Db::rollback();
            return ['code'=>1012,'msg'=>'发放合伙人奖励失败'];
        }
    }
}