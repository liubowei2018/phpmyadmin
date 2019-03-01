<?php
/**
 * Created by PhpStorm.
 * User: liubowei
 * Date: 2019/2/28
 * Time: 15:29
 */

namespace app\admins\controller;

use think\Db;
class Hongbao extends Base
{
    /**
     * 主红包列表
     */
    public function order_list(){
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
            $count = Db::name('red_order_list')->alias('l')->where($map)->join('member m','m.id = l.user_id')->count();
            $list = Db::name('red_order_list')->alias('l')->field("l.*,m.username,m.mobile,FROM_UNIXTIME(l.add_time, '%Y-%m-%d %H:%i:%s') as add_time")->where($map)->join('member m','m.id = l.user_id')->page($page,$rows)->order('l.add_time DESC')->select();
            return json(['count'=>$count,'list'=>$list,'page'=>$page]);
        }
        return $this->fetch();
    }
    /**
     * 详情列表
     */
    public function order_info(){
        $id = input('get.id');
        $order_list = Db::name('red_order_list')->alias('l')->field("l.*,m.username,m.mobile,FROM_UNIXTIME(l.add_time, '%Y-%m-%d %H:%i:%s') as add_time")->where(['l.id'=>$id])->join('member m','m.id = l.user_id')->find();
        $order_info = Db::name('red_order_info')->alias('i')->field("i.*,m.username,m.mobile,FROM_UNIXTIME(i.add_time, '%Y-%m-%d %H:%i:%s') as add_time")->where(['i.order_id'=>$id])->join('member m','m.id = i.member_id')->select();
        $count = Db::name('red_order_info')->where(['order_id'=>$id,'state'=>1])->count();
        $img_list = explode(',',$order_list['img_path']);
        $this->assign('list',$order_list);
        $this->assign('info',$order_info);
        $this->assign('img_list',$img_list);
        $this->assign('count',$count);

        return $this->fetch();
    }
    /**
     * 详细信息
     */
    public function order_detail(){

        return $this->fetch();
    }
}