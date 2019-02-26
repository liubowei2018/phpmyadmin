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
     * 添加银行卡
     */
    public function getAddBank($data){
        $this->startTrans();
        try{
            $this->insert($data);
            $this->commit();
            return ['code'=>1011,'msg'=>'添加成功','data'=>''];
        }catch (\Exception $exception){
            $this->rollback();
            return ['code'=>1012,'msg'=>'添加失败','data'=>''];
        }
    }
    /**
     * 修改用户银行卡
     */
    public function getEditBank($id,$data){
        $this->startTrans();
        try{
            $this->save($data,['id'=>$id]);
            $this->commit();
            return ['code'=>1011,'msg'=>'修改成功','data'=>''];
        }catch (\Exception $exception){
            $this->rollback();
            return ['code'=>1012,'msg'=>'修改失败','data'=>''];
        }
    }

    /**
     * 删除银行卡
     */
    public function getDelBank($id){
        $this->startTrans();
        try{
            $this->where("id",$id)->delete();
            $this->commit();
            return ['code'=>1011,'msg'=>'修改成功','data'=>''];
        }catch (\Exception $exception){
            $this->rollback();
            return ['code'=>1012,'msg'=>'修改失败','data'=>''];
        }
    }
}