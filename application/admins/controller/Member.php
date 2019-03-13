<?php
/**
 * Created by PhpStorm.
 * User: liubowei
 * Date: 2019/2/16
 * Time: 15:54
 */

namespace app\admins\controller;


use app\admins\model\MemberMobile;
use think\Db;

class Member extends Base
{
    public function index(){
        $MemberModel = new MemberMobile();
        if(request()->isPost()){
         $map = [];
         $key = input('post.key');
         $type = input('post.type');
         $state = input('post.state');
         $stare_time = input('post.stare_time');
         $end_time = input('post.end_time');
         if(!empty($key)){
             $map['username|mobile'] = ['like','%'.$key.'%'];
         }
         if(!empty($type)){
             $map['type'] = $type;
         }
         if(!empty($state)){
             $map['state'] = $state;
         }
         if($state === '0'){
                $map['state'] = $state;
            }
         if(!empty($stare_time)){
             $stare_time = $stare_time.' 00:00:00';
             $map['create_time'] = ['>= time',$stare_time];
         }
         if(!empty($end_time)){
             $end_time = $end_time.' 23:59:59';
             $map['create_time'] = ['<= time',$end_time];
         }
         if(!empty($stare_time) && !empty($end_time)){
             $map['create_time'] = ['between time',[$stare_time,$end_time]];
         }
         $page = input('get.page') ? input('get.page'):1;
         $rows = input('get.rows');// 获取总条数
         $count = Db::name('member')->where($map)->count();
         $list = $MemberModel->getMemberList('*',$map,$page,$rows,'id DESC');
         foreach ($list as $k=>$v){
             $money = Db::name('money')->where('user_id',$v['id'])->find();
             $list[$k]['balance'] = $money['balance'];
             $list[$k]['bonus'] =  $money['bonus'];
         }
         return json(['count'=>$count,'list'=>$list,'page'=>$page]);
        }
        return $this->fetch();
    }
    public function member_state(){
        $MemberModel = new MemberMobile();
        $id = input('post.id');
        $res = $MemberModel->getMemberState($id);
        return json($res);
    }

    /**
     * 会员详情信息
     */
    public function member_info(){
        $id = input('get.id');
        $member_info = Db::name('member')->where('id',$id)->find();
        $money_info =  Db::name('money')->where('user_id',$id)->find();
        $this->assign('info',$member_info);
        $this->assign('money',$money_info);
        return $this->fetch();
    }

    /**
     * 推荐一二级列表
     */
    public function recommend_list(){
        $type = input('param.type');
        $user_id = input('param.user_id');
        $map = [];
        if($type == 1){
            $map['pid'] = $user_id;
        }else{
            $map['gid'] = $user_id;
        }
        $list = Db::name('member')->where($map)->order('id DESC')->paginate(20,false,['query'=>input('get.')]);
        $page = $list->render();
        $this->assign('list',$list);
        $this->assign('page',$page);
        return $this->fetch();
    }

    /**
     * 修改用户资金
     */
    public function save_money(){
        $data = input('post.');
        $result = $this->validate($data,'MemberValidate.save_money');
        if($result !== true){
            $this->error($result);
        }
        $money = Db::name('money')->where('user_id',$data['user_id'])->find();
        switch ($data['state']){
            case 1://增加
                Db::startTrans();
                try{
                    Db::name('money')->where('user_id',$data['user_id'])->setInc('balance',$data['save_money']);
                    getAddMoneyLog($data['user_id'],$data['save_money'],$money['balance'],$money['balance'] + $data['save_money'],1,1,$data['info'],'','',time(),'');
                    getAddAdminMoneyLog($data['user_id'],$data['save_money'],1,1,$data['info'],$this->admin_uid,$this->admin_name);
                    Db::commit();
                    return json(['code'=>1011,'msg'=>'增加余额成功']);
                }catch (\Exception $exception){
                    Db::rollback();
                    return json(['code'=>1012,'msg'=>'增加余额失败，请稍后再试']);
                }
                break;
            case 2://减少
                if($money['balance'] < $data['save_money']){
                    $this->error('用户余额不足');
                }else {
                    Db::startTrans();
                    try {
                        Db::name('money')->where('user_id', $data['user_id'])->setDec('balance', $data['save_money']);
                        getAddMoneyLog($data['user_id'], $data['save_money'], $money['balance'], $money['balance'] - $data['save_money'], 1, 2, $data['info'], '', '', time(), '');
                        getAddAdminMoneyLog($data['user_id'], $data['save_money'], 1, 2, $data['info'], $this->admin_uid, $this->admin_name);
                        Db::commit();
                        return json(['code'=>1011,'msg'=>'减少余额成功']);
                    } catch (\Exception $exception) {
                        Db::rollback();
                        return json(['code'=>1012,'msg'=>'减少余额失败，请稍后再试']);
                    }
                }
                break;
            default:
                $this->error('操作类型异常');
        }
    }
}