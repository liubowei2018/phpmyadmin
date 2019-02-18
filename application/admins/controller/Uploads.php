<?php
/**
 * Created by PhpStorm.
 * User: liubowei
 * Date: 2019/2/18
 * Time: 15:49
 */

namespace app\admins\controller;


class Uploads extends Base
{
    public function article(){
        $param = input('param.');
        $file = request()->file('file');
        if($file){
            $info = $file->validate(['size'=>10485760,'ext'=>'jpg,png'])->move(ROOT_PATH . 'public' . DS . 'uploads/article');
            if($info){
                $str= '/uploads/article/'.str_replace("\\",'/',$info->getSaveName());
                return json(['code'=>1011,'msg'=>'上传成功','data'=>$str]);
            }else{
                return json(['code'=>1012,'msg'=>'上传失败','data'=>$file->getError()]);
            }
        }else{
            return json(['code'=>1012,'msg'=>'请选择图片','data'=>'']);
        }
    }
}