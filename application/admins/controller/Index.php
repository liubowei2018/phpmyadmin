<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/1/25
 * Time: 13:46
 */

namespace app\admins\controller;


use think\Db;
use think\Session;

class Index extends Base
{
    public function index(){
        $webConf = getWebConfigList();
        $group = Db::name('auth_group')->alias('g')->field('g.title')->where('a.uid',$this->admin_uid)->join('auth_group_access a','a.group_id = g.id')->find();
        $this->assign('name',$this->admin_name);
        $this->assign('title',$group['title']);
        $this->assign('webConf',$webConf);
        return $this->fetch();
    }
    /**
     * 首页
     */
    public function index_detail(){
        $webConf = getWebConfigList();

        $hb_yue_money_count = Db::name('red_order_list')->where('money_type',1)->sum('original_money');
        $hb_wx_money_count = Db::name('red_order_list')->where('money_type',2)->sum('original_money');

        //会员总数
        $member_today = Db::name('member')->whereTime('create_time','D')->count();
        $member_VIP = Db::name('member')->where('type',2)->count();
        $member_hehuoren = Db::name('member')->where('type',3)->count();
        //红包数量 年/月/日
        $hb_year_number_count = Db::name('red_order_list')->whereTime('add_time','Y')->count();
        $hb_month_number_count = Db::name('red_order_list')->whereTime('add_time','M')->count();
        $hb_day_number_count = Db::name('red_order_list')->whereTime('add_time','D')->count();

        $this->assign('hb_yue_money_count',$hb_yue_money_count);
        $this->assign('hb_wx_money_count',$hb_wx_money_count);
        //会员总数
        $this->assign('member_today',$member_today);
        $this->assign('member_VIP',$member_VIP);
        $this->assign('member_hehuoren',$member_hehuoren);
        //红包数量 年/月/日
        $this->assign('hb_year_number_count',$hb_year_number_count);
        $this->assign('hb_month_number_count',$hb_month_number_count);
        $this->assign('hb_day_number_count',$hb_day_number_count);
        $this->assign('webConf',$webConf);
        return $this->fetch();
    }

    //退出登录
    public function logout(){
        Session::delete('admin_uid');
        Session::delete('admin_name');
        $this->redirect('admins/Login/index');
    }
}