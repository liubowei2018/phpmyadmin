<?php
/**
 * Created by PhpStorm.
 * User: liubowei
 * Date: 2019/2/16
 * Time: 15:55
 */

namespace app\admins\model;


use think\Model;

class MemberMobile extends Model
{
    protected $name='member';

    /**
     * 获取会员列表
     */
    public function getMemberList($field,$map,$page,$rows,$order){
        $list = $this->field($field)->where($map)->page($page,$rows)->order($order)->select();
        foreach ($list as $k=>$v){
            if($v['pid'] != ''){
                $list[$k]['pid'] = $this->where('pid',$v['pid'])->value('mobile');
            }else{
                $list[$k]['pid'] = '未绑定推荐人';
            }
        }
        return $list;
    }
    /**
     * 修改会员状态
     */
    public function getMemberState($user_id){
        $member_state = $this->where('id',$user_id)->value('state');
        try{
            if($member_state == 1){
                $this->where('id',$user_id)->update(['state'=>0]);
                return ['code'=>1011,'msg'=>'会员状态已禁止','date'=>''];
            }else{
                $this->where('id',$user_id)->update(['state'=>1]);
                return ['code'=>1011,'msg'=>'会员状态已开启','date'=>''];
            }
        }catch (\Exception $e){
            return ['code'=>1012,'msg'=>$e->getMessage(),'date'=>''];
        }
    }
}