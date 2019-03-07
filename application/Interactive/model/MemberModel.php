<?php
/**
 * Created by PhpStorm.
 * User: liubowei
 * Date: 2019/2/19
 * Time: 13:07
 */

namespace app\Interactive\model;


use think\Model;
use think\Cache;
class MemberModel extends Model
{
    protected $name = "member";

    /**
     * 用户登录验证
     * state 1 微信登录  2 手机登录
     */
    public function getMemberLogin($data,$state){
        //创建token
        $token = md5($data['uuid'].time().config('auth_key'));
        $config = privilege_config_list();
        //验证用户是否存在
        if($state == 1){
            $is_register = $this->where('uuid',$data['uuid'])->find();
        }else{
            $is_register = $this->where('mobile',$data['phone'])->find();
        }
        $array = [];
        if($is_register){
            //验证时候绑定手机号
            if(empty($is_register['mobile'])){
                $array['is_mobile'] = 0;
            }else{
                $array['is_mobile'] = 1;
            }
            if($is_register['state'] == 1){
                $array['state'] = 1;
            }else{
                $array['state'] = 0;
            }
            $array['token'] = $token;
            $MoneyModel = new MoneyModel();
            $money_info = $MoneyModel->where('user_id',$is_register['id'])->find();
            if($money_info['today_state'] != 1){
                $total_red_number = $money_info['red_number'] + $money_info['red_push_number'];
                $MoneyModel->where('user_id',$is_register['id'])->update(['total_red_number'=>$total_red_number,'today_state'=>1]);
            }
            if($state == 1){
                $array['uuid'] = $data['uuid'];
                $array['username'] = $data['username'];
                $array['user_img'] = $data['user_img'];
                $array['sex'] = $data['sex'];
                $this->save(['username'=>$data['username'],'user_img'=>$data['user_img'],'sex'=>$is_register['sex']],['uuid'=>$data['uuid']]);
            }else{
                $array['uuid'] = $is_register['uuid'];
                $array['username'] = $is_register['username'];
                $array['user_img'] = $is_register['user_img'];
                $array['sex'] = $is_register['sex'];
            }
            Cache::set($data['uuid'].'_token',$token,3600);
            return ['code'=>1011,'msg'=>'登录成功','data'=>$array];
        }else{
            $MoneyModel = new MoneyModel();
            $user_id = $this->insertGetId(['username'=>$data['username'],'user_img'=>$data['user_img'],'state'=>1,'uuid'=>$data['uuid'],'type'=>1,'create_time'=>time()]);
            $MoneyModel->getAddInfo(['user_id' => $user_id,'red_number'=>$config['ordinary_today_hongbao_number'],'total_red_number'=>$config['ordinary_today_hongbao_number'],'today_state'=>1]);
            $array['is_mobile'] = 0;
            $array['state'] = 1;
            $array['token'] = $token;
            $array['uuid'] = $data['uuid'];
            $array['username'] = $data['username'];
            $array['user_img'] = $data['user_img'];
            $array['sex'] = $data['sex'];
            Cache::set($data['uuid'].'_token',$token,3600);
            return ['code'=>1011,'msg'=>'登录成功','data'=>$array];
        }
    }

    /**
     * 获取用户个人信息
     */
    public function getMemberInfo($field,$map){
        return $this->field($field)->where($map)->find();
    }
    /**
     *修改用户手机号
     */
    public function getEditMobile($data,$uuid){
        $this->startTrans();
        try{
            $this->where('uuid',$uuid)->update($data);
            $this->commit();
            return ['code'=>1011,'msg'=>'修改成功','data'=>''];
        }catch (\Exception $e){
            $this->rollback();
            return ['code'=>1012,'msg'=>'修改失败','data'=>''];
        }
    }

    /**
     * 查询推荐人列表
     */
    public function getPushList($field,$map,$page,$rows){
        return $this->field($field)->where($map)->page($page,$rows)->select();
    }

    /**
     * 修改会员信息
     * @param $param
     * @param $map
     * @param $data
     * @return array
     * @throws \think\exception\PDOException
     */
    public function getEditInfo($param,$map,$data){
        $this->startTrans();
        try{
            $this->save($param,$map);
            $this->commit();
            return ['code'=>1011,'msg'=>'修改推荐人成功','data'=>$data];
        }catch (\Exception $exception){
            $this->rollback();
            return ['code'=>1012,'msg'=>'修改推荐人失败','data'=>$data];
        }
    }
}