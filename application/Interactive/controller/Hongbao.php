<?php
/**
 * Created by PhpStorm.
 * User: liubowei
 * Date: 2019/2/25
 * Time: 11:10
 */

namespace app\Interactive\controller;

use think\Cache;
use think\Db;
class Hongbao extends ApiBase
{
    /**
     * 红包发放距离
     */
    public function distance(){
        $data = input('post.');
        $validate_res = $this->validate($data,'HomeValidate.whole');
        if($validate_res !== true){ return json(['code'=>1015,'msg'=>$validate_res]); } //数据认证
        if(getSign($data) != $data['Sign']){ return json(['code'=>1013,'msg'=>'签名错误']);} //签名认证
        if(Cache::get($data['uuid'].'_token') != $data['token']) return json(['code'=>1004,'msg'=>'用户未登录']);//登陆验证
        $array = [
            array('id'=>1,'title'=>'一公里','number'=>1),
            array('id'=>2,'title'=>'五公里','number'=>5),
            array('id'=>3,'title'=>'城市','number'=>0),
            array('id'=>4,'title'=>'全国','number'=>0),
        ];
        return json(['code'=>1011,'msg'=>'获取成功','data'=>$array]);
    }
    /**
     * 添加红包
     */
    public function add_red(){
        $data = input('post.');
        $validate_res = $this->validate($data,'HongbaoValidate.hongbao');
        if($validate_res !== true){ return json(['code'=>1015,'msg'=>$validate_res]); } //数据认证
        if(getSign($data) != $data['Sign']){ return json(['code'=>1013,'msg'=>'签名错误']);} //签名认证
        if(Cache::get($data['uuid'].'_token') != $data['token']) return json(['code'=>1004,'msg'=>'用户未登录']);//登陆验证
        $member_info = Db::name('member')->where('uuid',$data['uuid'])->find();
        $money_info = Db::name('money')->where('user_id',$member_info['id'])->find();
        $config = app_config_list();
        if($data['money'] > $money_info['balance']){
            return json(['code'=>1012,'msg'=>'账户余额不足，请充值','data'=>'']);
        }
        $type = input('post.type');
        Db::startTrans();
        try{
            $order_number = "H".time().rand(10000,99999).$member_info['id'];
            $money = $data['money'] * $config['app_hongbao']/100;
            $order_data = [
                'order_number'=>$order_number,
                'lng'=>$data['lng'],
                'lat'=>$data['lat'],
                'distance'=>$data['distance'],
                'user_id'=>$member_info['id'],
                'money'=>$money,
                'number'=>$data['number'],
                'type'=>$data['type'],
                'add_time'=>time(),
                'state'=>1,
                'citycode'=>current_city($data['lng'],$data['lat'],$member_info['id'])
            ];

            switch ($type){
                case 1://详情红包
                    $files = request()->file('image');
                    $array = [];
                    $array_str = '';
                    if($files){
                        foreach($files as $k=>$file){
                            $info = $file->validate(['size'=>10485760,'ext'=>'jpg,png'])->move(ROOT_PATH . 'public' . DS . 'uploads/user');
                            if($info){
                                $str= str_replace("\\",'/','/uploads/user/'.$info->getSaveName());
                                $array[] = $str;
                            }else{
                                Db::rollback();
                                return json(['code'=>1012,'msg'=>'第'.$k.'上传失败','data'=>$file->getError()]);
                            }
                        }
                        $array_str = implode(',',$array);
                        $this->add_member_img($member_info['id'],$array);
                    }
                    $order_data['content'] = $data['content'];
                    $order_data['img_path'] = $array_str;
                    break;
                case 2://链接红包
                    $order_data['web_url'] = $data['web_url'];
                    break;
                default:
                    Db::rollback();
                    return json(['code'=>1012,'msg'=>'请选择红包类型','data'=>'']);
            }
            //减去红包支付金额
            Db::name('money')->where('user_id',$member_info['id'])->setDec('balance',$data['money']);
            //创建红包
            $order_id = Db::name('red_order_list')->insertGetId($order_data);
            //添加扣款记录
            $money_log = [
                'user_id'=>$member_info['id'],
                'money'=>$data['money'],
                'original'=>$money_info['balance'],
                'now'=>$money_info['balance']-$data['money'],
                'type'=>'1',
                'state'=>2,
                'info'=>'发放红包',
                'source'=>$order_number,
                'trend'=>'1',
                'create_time'=>time()
            ];
            Db::name('money_log')->insert($money_log);
            $user_money = Db::name('money')->where('user_id',$member_info['id'])->value('balance');
            if($user_money < 0){
                Db::rollback();
                return json(['code'=>1012,'msg'=>'账户余额不足，请充值','data'=>'']);
            }
            Db::commit();
            return json(['code'=>1011,'msg'=>'红包发送成功','data'=>'']);
        }catch (\Exception $exception){
            Db::rollback();
            return json(['code'=>1012,'msg'=>'红包发放失败','data'=>$exception->getMessage()]);
        }
    }

    /**
     * 创建子红包
     */
    public function splitting_red_packets($order_id){
        $order_list_info = Db::name('red_order_list')->where('id',$order_id)->find();
        if($order_list_info){
            $count = Db::name('red_order_info')->where('order_id',$order_list_info['id'])->count();
            if($count < 1){
                $res = hongbao_group($order_list_info['money'],600);
            }
        }
    }

    /**
     * 红包列表
     * lng  lat
     */
    public function red_envelope_list(){
        $data = input('post.');
        $validate_res = $this->validate($data,'HongbaoValidate.red_envelope_list');
        if($validate_res !== true){ return json(['code'=>1015,'msg'=>$validate_res]); } //数据认证
        if(getSign($data) != $data['Sign']){ return json(['code'=>1013,'msg'=>'签名错误']);} //签名认证
        if(Cache::get($data['uuid'].'_token') != $data['token']) return json(['code'=>1004,'msg'=>'用户未登录']);//登陆验证
        $lng = $data['lng'];
        $lat = $data['lat'];
        $page = input('post.page');
        $page = $page?$page:1;
        $map = [];
        $distance = $this->getAround($lat,$lng,10000);
        dump($distance );
        $list = Db::name('red_order_list')->where($map)->select();
        return json(['code'=>1011,'msg'=>'红包发送成功','data'=>'']);
    }

    /**
     * @param $latitude  纬度
     * @param $longitude 经度
     * @param $raidus    半径范围(单位：米)
     * @return array
     */
    public function getAround($latitude,$longitude,$raidus){
        $PI = 3.14159265;
        $degree = (24901*1609)/360.0;
        $dpmLat = 1/$degree;
        $radiusLat = $dpmLat*$raidus;
        $minLat = $latitude - $radiusLat;
        $maxLat = $latitude + $radiusLat;
        $mpdLng = $degree*cos($latitude * ($PI/180));
        $dpmLng = 1 / $mpdLng;
        $radiusLng = $dpmLng*$raidus;
        $minLng = $longitude - $radiusLng;
        $maxLng = $longitude + $radiusLng;
        return ['minLat'=>$minLat, 'maxLat'=>$maxLat, 'minLng'=>$minLng, 'maxLng'=>$maxLng];
    }

    /**
     * 抢红包
     */
    public function grab_red_envelope(){
        $data = input('post.');
        $validate_res = $this->validate($data,'HomeValidate.whole');
        if($validate_res !== true){ return json(['code'=>1015,'msg'=>$validate_res]); } //数据认证
        if(getSign($data) != $data['Sign']){ return json(['code'=>1013,'msg'=>'签名错误']);} //签名认证
        if(Cache::get($data['uuid'].'_token') != $data['token']) return json(['code'=>1004,'msg'=>'用户未登录']);//登陆验证
        $order_number = input('post.order_number');
        $order_info = Db::name('red_order_list')->where(['order_number'=>$order_number])->find();
        if(!$order_info){
            return json(['code'=>1012,'msg'=>'红包不存在','data'=>'']);
        }elseif ($order_info['number'] <= 0){
            return json(['code'=>1012,'msg'=>'红包已抢完','data'=>'']);
        }else{
            Db::startTrans();
            try{


                Db::commit();
                return json(['code'=>1012,'msg'=>'红包领取成功','data'=>'']);
            }catch (\Exception $exception){
                Db::rollback();
                return json(['code'=>1012,'msg'=>'红包领取失败','data'=>'']);
            }
        }
    }

    /**
     * 红包领取规则
     */
    private function rules_collection(){

    }
    /**
     * 保存用户图片
     */
    private function add_member_img($user_id,$array){
        foreach ($array as $k=>$v){
            Db::name('member_img')->insert(['user_id'=>$user_id,'img_path'=>$v,'add_time'=>time()]);
        }
    }

    /**
     * 发放红包列表
     */
    public function issue_red_packets(){

    }
    /**
     * 领取红包列表
     */
    public function receive_red_packets(){

    }
    /**
     * 红包详情
     */
    public function red_packets_info(){

    }
}