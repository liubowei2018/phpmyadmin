<?php
/**
 * Created by PhpStorm.
 * User: liubowei
 * Date: 2019/2/28
 * Time: 15:29
 */

namespace app\admins\controller;


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
            $count = Db::name('money_log')->alias('l')->where($map)->join('member m','m.id = l.user_id')->count();
            return json(['count'=>$count,'list'=>'','page'=>$page]);
        }
        return $this->fetch();
    }
    /**
     * 详情列表
     */
    public function order_info(){

        return $this->fetch();
    }
    /**
     * 详细信息
     */
    public function order_detail(){

        return $this->fetch();
    }
}