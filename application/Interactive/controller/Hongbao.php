<?php
/**
 * Created by PhpStorm.
 * User: liubowei
 * Date: 2019/2/25
 * Time: 11:10
 */

namespace app\Interactive\controller;

use app\Interactive\model\MemberModel;
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
                'original_money'=>$data['money'],
                'number'=>$data['number'],
                'type'=>$data['type'],
                'add_time'=>time(),
                'state'=>1,
                'citycode'=>current_city($data['lng'],$data['lat'],$member_info['id'])
            ];
            if($money * 100 < $data['number'] ){
                Db::rollback();
                return json(['code'=>1012,'msg'=>'红包领取数量不能大于'.$money*100,'data'=>'']);
            }
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
            //拆分红包

            $this->splitting_red_packets($order_id);

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
    private function splitting_red_packets($order_id){
        $order_list_info = Db::name('red_order_list')->where('id',$order_id)->find();
        if($order_list_info){
            $count = Db::name('red_order_info')->where('order_id',$order_list_info['id'])->count();
            if($count < 1){
                $list = $this->sendHB($order_list_info['money'],$order_list_info['number']);
                foreach ($list as $k=>$v){
                    $array = [];
                    switch ($order_list_info['distance']){
                        case 1: //一公里
                            $position = $this->getAround1($order_list_info['lat'],$order_list_info['lng'],10000);
                            $array['lat'] = $position['lat'];
                            $array['lng'] = $position['lng'];
                            break;
                        case 2://五公里
                            $position = $this->getAround1($order_list_info['lat'],$order_list_info['lng'],50000);
                            $array['lat'] = $position['lat'];
                            $array['lng'] = $position['lng'];
                            break;
                        case 3://市

                            break;
                        case 4://全国

                            break;
                    }
                    $array['order_id'] = $order_list_info['id'];
                    $array['money'] = $v;
                    $array['citycode'] = $order_list_info['citycode'];
                    $array['state'] = 0;
                    $array['type'] = $order_list_info['distance'];
                    $array['add_time'] = time();
                    Db::name('red_order_info')->insert($array);
                }

            }
        }
    }
    public function test(){
        $slat = '113.703929';
        $slng = '34.719809';

        $sql = "SELECT *, ROUND(6378.138*2*ASIN(SQRT(POW(SIN(($slat*PI()/180-lat*PI()/180)/2),2)+COS($slat*PI()/180)*COS(lat*PI()/180)*POW(SIN(($slng*PI()/180-lng*PI()/180)/2),2)))) AS juli  
        FROM think_red_order_info where state=0 GROUP BY HAVING juli <= 1,order_id ";
        $red_list = Db::query($sql);
        dump($red_list);
    }

    public function getdistance($lng1 ='34.719809', $lat1='113.703929', $lng2='34.719809', $lat2='113.703929') {
        // 将角度转为狐度
        $radLat1 = deg2rad($lat1); //deg2rad()函数将角度转换为弧度
        $radLat2 = deg2rad($lat2);
        $radLng1 = deg2rad($lng1);
        $radLng2 = deg2rad($lng2);
        $a = $radLat1 - $radLat2;
        $b = $radLng1 - $radLng2;
        $s = 2 * asin(sqrt(pow(sin($a / 2), 2) + cos($radLat1) * cos($radLat2) * pow(sin($b / 2), 2))) * 6378.137 * 1000;
        return $s;
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
        $MemberModel = new MemberModel();
        $member_info = $MemberModel->getMemberInfo('id',['uuid'=>$data['uuid']]);
        $lng = $data['lng'];
        $lat = $data['lat'];
        $citycode = current_city($lng,$lat,$member_info['id']);
        $page = input('post.page');
        $page = $page?$page:1;
        $map = [];
        $distance = $this->getAround($lat,$lng,5000);
        dump($distance );
        //先查询区域红包
        $max_lng = $distance['maxLng'];
        $min_lng = $distance['minLng'];
        $max_lat = $distance['maxLat'];
        $min_lat = $distance['minLat'];
        $quyu_red_list = Db::query("SELECT * FROM think_red_order_info WHERE state = 0 AND citycode = $citycode AND lng > $min_lng AND lng < $max_lng AND lat < $max_lat AND lat > $min_lat GROUP BY order_id");
        dump($quyu_red_list);
        return json(['code'=>1011,'msg'=>'成功','data'=>'']);
    }

    /**
     * @param $latitude  纬度
     * @param $longitude 经度
     * @param $raidus    半径范围(单位：米)
     * @return array
     */
    private function getAround($latitude,$longitude,$raidus){
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
        return ['minLat'=>sprintf("%.6f",$minLat), 'maxLat'=>sprintf("%.6f",$maxLat), 'minLng'=>sprintf("%.6f",$minLng), 'maxLng'=>sprintf("%.6f",$maxLng)];
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
    public function receive_red_packets(){
        $data = input('post.');
        $validate_res = $this->validate($data,'HomeValidate.whole');
        if($validate_res !== true){ return json(['code'=>1015,'msg'=>$validate_res]); } //数据认证
        if(getSign($data) != $data['Sign']){ return json(['code'=>1013,'msg'=>'签名错误']);} //签名认证
        if(Cache::get($data['uuid'].'_token') != $data['token']) return json(['code'=>1004,'msg'=>'用户未登录']);//登陆验证
        $MemberModel = new MemberModel();
        $member_info = $MemberModel->getMemberInfo('id',['uuid'=>$data['uuid']]);
        $page = input('post.page')?input('post.page'):1;
        $total_money = Db::name('red_order_list')->where(['user_id'=>$member_info['id']])->sum('money');
        $today_money = Db::name('red_order_list')->where(['user_id'=>$member_info['id']])->whereTime('add_time','d')->sum('money');
        $list = Db::name('red_order_list')->field('id,img_path,content,add_time')->where(['user_id'=>$member_info['id']])->page($page,15)->order('add_time DESC')->select();
        if(count($list) > 0){
            foreach ($list as $k=>$v){
                $img_path = explode(",", $v['img_path']);
                if(count($img_path) > 0){
                    $url = web_url_str();
                    if(!empty($img_path['0'])){
                        $list[$k]['img_path'] = $url.$img_path['0'];
                    }
                }
                $list[$k]['add_time'] = date('Y-m-d H:i:s',$v['add_time']);
            }
        }
        return json(['code'=>1011,'msg'=>'成功','data'=>$list,'total_money'=>(string)$total_money,'today_money'=>(string)$today_money]);
    }
    /**
     * issue_red_packets
     * 领取红包列表
     */
    public function issue_red_packets(){
        $data = input('post.');
        $validate_res = $this->validate($data,'HomeValidate.whole');
        if($validate_res !== true){ return json(['code'=>1015,'msg'=>$validate_res]); } //数据认证
        if(getSign($data) != $data['Sign']){ return json(['code'=>1013,'msg'=>'签名错误']);} //签名认证
        if(Cache::get($data['uuid'].'_token') != $data['token']) return json(['code'=>1004,'msg'=>'用户未登录']);//登陆验证
        $MemberModel = new MemberModel();
        $member_info = $MemberModel->getMemberInfo('id',['uuid'=>$data['uuid']]);
        $page = input('post.page')?input('post.page'):1;
        $total_money = Db::name('red_order_list')->where(['user_id'=>$member_info['id']])->sum('original_money');
        $today_money = Db::name('red_order_list')->where(['user_id'=>$member_info['id']])->whereTime('add_time','d')->sum('original_money');
        $list = Db::name('red_order_list')->field('id,original_money,add_time')->where(['user_id'=>$member_info['id']])->page($page,15)->order('add_time DESC')->select();
        if(count($list) > 0){
            foreach ($list as $k=>$v){
                $list[$k]['add_time'] = date('Y-m-d H:i:s',$v['add_time']);
            }
        }
        return json(['code'=>1011,'msg'=>'成功','data'=>$list,'total_money'=>(string)$total_money,'today_money'=>(string)$today_money]);
    }
    /**
     * 红包详情
     */
    public function red_packets_info(){

    }

    public function getAround1($lat,$lon,$raidus = 990){
        $PI = PI();

        $latitude = $lat;
        $longitude = $lon;
        $degree = (24901 * 1609) / 360.0;
        $raidusMile = $raidus;

        $dpmLat = 1 / $degree;
        $radiusLat = $dpmLat * $raidusMile;
        $minLat = $latitude - $radiusLat;
        $maxLat = $latitude + $radiusLat;

        $mpdLng = $degree * cos($latitude * ($PI / 180));
        $dpmLng = 1 / $mpdLng;
        $radiusLng = $dpmLng * $raidusMile;
        $minLng = $longitude - $radiusLng;
        $maxLng = $longitude + $radiusLng;
        $a = 1000000000000;
        $c = bcsub($maxLat, $minLat, 12);
        $d = $c*$a;
        $sj = mt_rand(0, (int)uint32val($d));
        $sj = $sj*10;
        $sj = bcdiv($sj, $a, 12);
        $f = mt_rand(0,1);
        if($f)
        {
            $sj = '-'.$sj;
        }
        $newlat = $lat+$sj;
        $cc = bcsub($maxLng, $minLng, 12);
        $dd = $cc*$a;
        $sjsj = mt_rand(0, (int)uint32val($dd));
        $sjsj = $sjsj*16;
        $sjsj = bcdiv($sjsj, $a, 12);
        $ff = mt_rand(0,1);
        if($ff)
        {
            $sjsj = '-'.$sjsj;
        }
        $newlng = $lon+$sjsj;

        $data = [
            'lat' => sprintf("%.6f",$newlat),
            'lng' => sprintf("%.6f",$newlng),
        ];
        return $data;
    }

    /**
     * 拼手气红包实现
     * 生成num个随机数，每个随机数占随机数总和的比例*money_total的值即为每个红包的钱额
     * 考虑到精度问题，最后重置最大的那个红包的钱额为money_total-其他红包的总额
     * 浮点数比较大小,使用number_format,精确到2位小数
     *
     * @param double $money_total  总钱额， 每人最少0.01,精确到2位小数
     * @param int $num 发送给几个人
     * @return array num个元素的一维数组，值是随机钱额
     */
    public function sendHB($money_total, $num) {
        if($money_total < $num*0.01) {
            exit('钱太少');
        }

        $rand_arr = array();
        for($i=0; $i<$num; $i++) {
            $rand = rand(1, 100);
            $rand_arr[] = $rand;
        }

        $rand_sum = array_sum($rand_arr);
        $rand_money_arr = array();
        $rand_money_arr = array_pad($rand_money_arr, $num, 0.01);  //保证每个红包至少0.01

        foreach ($rand_arr as $key => $r) {
            $rand_money = number_format($money_total*$r/$rand_sum, 2);

            if($rand_money <= 0.01 || number_format(array_sum($rand_money_arr), 2) >= number_format($money_total, 2)) {
                $rand_money_arr[$key] = 0.01;
            } else {
                $rand_money_arr[$key] = $rand_money;
            }

        }

        $max_index = $max_rand = 0;
        foreach ($rand_money_arr as $key => $rm) {
            if($rm > $max_rand) {
                $max_rand = $rm;
                $max_index = $key;
            }
        }

        unset($rand_money_arr[$max_index]);
        //这里的array_sum($rand_money_arr)一定是小于$money_total的
        $rand_money_arr[$max_index] = number_format($money_total - array_sum($rand_money_arr), 2);

        ksort($rand_money_arr);
        return $rand_money_arr;
    }
}