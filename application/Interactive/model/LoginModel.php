<?php
/**
 * Created by PhpStorm.
 * User: liubowei
 * Date: 2019/2/19
 * Time: 9:56
 */

namespace app\Interactive\model;


use think\Cache;
use think\Model;

class LoginModel extends Model
{
    protected $name = "member";

    /**
     * 用户登录验证
     */
    public function getMemberLogin($data){
        //创建token
        $token = md5($data['uuid'].time().config('auth_key'));
        //验证用户是否存在
        $is_register = $this->where('uuid',$data['uuid'])->find();
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
            Cache::set($data['uuid'].'_token',$token,3600);
            return ['code'=>1011,'msg'=>'登录成功','data'=>$array];
        }else{
            $this->insert(['username'=>$data['username'],'user_img'=>$data['img_path'],'state'=>1,'uuid'=>$data['uuid'],'type'=>1,'create_time'=>time()]);
            $array['is_mobile'] = 0;
            $array['state'] = 1;
            $array['token'] = $token;
            return ['code'=>1011,'msg'=>'登录成功','data'=>$array];
        }
    }


}