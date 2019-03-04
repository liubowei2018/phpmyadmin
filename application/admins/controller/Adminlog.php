<?php
/**
 * Created by PhpStorm.
 * User: liubowei
 * Date: 2019/3/4
 * Time: 11:13
 */

namespace app\admins\controller;

use think\Db;
class Adminlog extends Base
{
    /**
     * 订单操作记录
     */
    public function admin_order_log(){
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
                $map['o.type'] = $type;
            }
            if(!empty($state)){
                $map['o.state'] = $state;
            }
            if(!empty($stare_time)){
                $stare_time = $stare_time.' 00:00:00';
                $map['o.create_time'] = ['>= time',$stare_time];
            }
            if(!empty($end_time)){
                $end_time = $end_time.' 23:59:59';
                $map['o.create_time'] = ['<= time',$end_time];
            }
            if(!empty($stare_time) && !empty($end_time)){
                $map['o.create_time'] = ['between time',[$stare_time,$end_time]];
            }
            $page = input('post.page');
            $rows = input('post.rows');
            $page = $page?$page:1;
            $count = Db::name('admin_order')->alias('o')->where($map)->join('member m','m.id = o.user_id')->count();
            $list = Db::name('admin_order')->alias('o')->field("o.*,m.username,m.mobile,FROM_UNIXTIME(o.add_time, '%Y-%m-%d %H:%i:%s') as add_time")->where($map)->join('member m','m.id = o.user_id')->page($page,$rows)->order('o.add_time DESC')->select();
            return json(['count'=>$count,'list'=>$list,'page'=>$page]);
        }
        return $this->fetch();
    }
    /**
     * 资金操作记录
     */
    public function admin_money_log(){
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
                $map['o.type'] = $type;
            }
            if(!empty($state)){
                $map['o.state'] = $state;
            }
            if(!empty($stare_time)){
                $stare_time = $stare_time.' 00:00:00';
                $map['o.create_time'] = ['>= time',$stare_time];
            }
            if(!empty($end_time)){
                $end_time = $end_time.' 23:59:59';
                $map['o.create_time'] = ['<= time',$end_time];
            }
            if(!empty($stare_time) && !empty($end_time)){
                $map['o.create_time'] = ['between time',[$stare_time,$end_time]];
            }
            $page = input('post.page');
            $rows = input('post.rows');
            $page = $page?$page:1;
            $count = Db::name('admin_money')->alias('o')->where($map)->join('member m','m.id = o.user_id')->count();
            $list = Db::name('admin_money')->alias('o')->field("o.*,m.username,m.mobile,FROM_UNIXTIME(o.add_time, '%Y-%m-%d %H:%i:%s') as add_time")->where($map)->join('member m','m.id = o.user_id')->page($page,$rows)->order('o.add_time DESC')->select();
            return json(['count'=>$count,'list'=>$list,'page'=>$page]);
        }
        return $this->fetch();
    }
}