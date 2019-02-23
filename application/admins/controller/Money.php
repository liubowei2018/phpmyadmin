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
            $stare_time = input('post.stare_time');
            $end_time = input('post.end_time');
            $map = [];
            if(!empty($key)){
                $map['m.username|m.mobile'] = ['like','%'.$key.'%'];
            }
            if(!empty($type)){
                $map['l.type'] = $type;
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

    }
    /**
     * 驳回提现
     */
    public function reject_propose(){

    }
    /*----------------------------------------资金提现----------------------------------------------------*/
}