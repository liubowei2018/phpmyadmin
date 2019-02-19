<?php
/**
 * Created by PhpStorm.
 * User: liubowei
 * Date: 2019/2/19
 * Time: 15:46
 */

namespace app\Interactive\model;


use think\Model;

class MemberBankModel extends Model
{
    protected $name = "member_bank";

    /**
     * 修改用户银行卡
     */
    public function getEditBank($user_id,$data){
        $user_bank = $this->where('user_id',$user_id)->find();
        $this->startTrans();
        try{
            if($user_bank){
                $this->insert($data);
            }else{
                $this->where('user_id',$user_id)->find();
            }
            $this->commit();
            return ['code'=>1011,'msg'=>'修改成功','data'=>''];
        }catch (\Exception $exception){
            $this->rollback();
            return ['code'=>1012,'msg'=>'修改失败','data'=>''];
        }
    }
}