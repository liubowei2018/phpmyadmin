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
                'money_type'=>1,
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
            $member_type = Db::name('member')->where('id',$member_info['id'])->value('type');
            $config = privilege_config_list();
            switch ($member_type){
                case 1: //普通会员
                    $red_number = $config['ordinary_today_pay_red'];
                    break;
                case 2: //vip会员
                    $red_number = $config['vip_today_pay_red'];
                    break;
                case 3: //广告商
                    $red_number = $config['partner_today_pay_red'];
                    break;
            }
            Db::name('money')->where('user_id',$member_info['id'])->setInc('total_red_number',$red_number);
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
     * 添加红包
     */
    public function add_wx_red(){
        $data = input('post.');
        $validate_res = $this->validate($data,'HongbaoValidate.hongbao');
        if($validate_res !== true){ return json(['code'=>1015,'msg'=>$validate_res]); } //数据认证
        if(getSign($data) != $data['Sign']){ return json(['code'=>1013,'msg'=>'签名错误']);} //签名认证
        if(Cache::get($data['uuid'].'_token') != $data['token']) return json(['code'=>1004,'msg'=>'用户未登录']);//登陆验证
        $member_info = Db::name('member')->where('uuid',$data['uuid'])->find();
        $money_info = Db::name('money')->where('user_id',$member_info['id'])->find();
        $config = app_config_list();
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
                'state'=>2,
                'money_type'=>2,
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

            //创建红包
            Db::name('red_order_list')->insertGetId($order_data);
            $Wxpay = new Wxpay();
            $url = web_url_str().'/Interactive/hongbao/pay_notify';
            $wx_pay_one = $Wxpay->getPrePayOrder('购买会员',$order_number,0.01*100,$url);
            $res = $Wxpay->getOrder($wx_pay_one['prepay_id']);
            Db::commit();
            return json(['code'=>1011,'msg'=>'红包发送成功','data'=>$res]);
        }catch (\Exception $exception){
            Db::rollback();
            return json(['code'=>1012,'msg'=>'红包发放失败','data'=>""]);
        }
    }
    /**
     * 支付回调地址
     */
    public function pay_notify(){
        $testxml  = file_get_contents("php://input");
        $jsonxml = json_encode(simplexml_load_string($testxml, 'SimpleXMLElement', LIBXML_NOCDATA));
        $result = json_decode($jsonxml, true);//转成数组，
        $Wxpay = new Wxpay();
        $sign = $Wxpay->getSign($result);
        if($result['sign'] == $sign){
            //如果成功返回了
            $out_trade_no = $result['out_trade_no'];
            if($result['return_code'] == 'SUCCESS' || $result['return_msg'] == 'OK'){
                $order_info = Db::name('red_order_list')->where('order_number',$out_trade_no)->find();
                if($order_info['state'] == '2'){
                    Db::startTrans();
                    try{
                        //修改订单状态
                        Db::name('red_order_list')->where('id',$order_info['id'])->update(['state'=>1,'wx_order_number'=>$result['transaction_id']]);
                        $member_type = Db::name('member')->where('id',$order_info['user_id'])->value('type');
                        $config = privilege_config_list();
                        switch ($member_type){
                            case 1: //普通会员
                                $red_number = $config['ordinary_today_pay_red'];
                                break;
                            case 2: //vip会员
                                $red_number = $config['vip_today_pay_red'];
                                break;
                            case 3: //广告商
                                $red_number = $config['partner_today_pay_red'];
                                break;
                        }
                        Db::name('money')->where('user_id',$order_info['user_id'])->setInc('total_red_number',$red_number);
                        //分解子订单
                        $this->splitting_red_packets($order_info['id']);
                        Db::commit();
                        echo '<xml><return_code><![CDATA[SUCCESS]]></return_code><return_msg><![CDATA[OK]]></return_msg></xml>';
                    }catch (\Exception $exception){
                        Db::rollback();
                        echo 'error';
                    }
                }else{
                    echo '<xml><return_code><![CDATA[SUCCESS]]></return_code><return_msg><![CDATA[OK]]></return_msg></xml>';
                }
            }else{
                echo 'error';
            }
        }else{
            echo 'error';
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
        $user_id = $member_info['id'];
        $citycode = current_city($lng,$lat,$member_info['id']);
        $page = input('post.page');
        $page = $page?$page:1;
        $map = [];
        //距离红包
        //$city_list = Db::query("CALL QueryRedEnvelopes($lat,$lng,$user_id,1,$citycode)");
        $distance = 1;
        $red_number = 6;
        $red_list_sql = "SELECT i.id,i.order_id,i.lng,i.lat,i.state FROM think_red_order_info as i  LEFT JOIN (SELECT order_id as not_info_id FROM think_red_order_info WHERE member_id = $user_id) as oi ON oi.not_info_id = i.order_id LEFT JOIN (SELECT id as not_list_id FROM think_red_order_list WHERE user_id = $user_id) as ol ON ol.not_list_id = i.order_id WHERE citycode = $citycode AND state = 0  AND oi.not_info_id is null  AND ol.not_list_id is null AND sqrt( ( (($lng-lng)*PI()*12656*cos((($lat+lat)/2)*PI()/180)/180) * (($lng-lng)*PI()*12656*cos ((($lat+lat)/2)*PI()/180)/180) ) + ( (($lat-lat)*PI()*12656/180) * (($lat-lat)*PI()*12656/180) ) )< $distance group by order_id limit $red_number;";
        //$red_list_sql = " SELECT id,order_id,lng,lat,state FROM think_red_order_info WHERE citycode = $citycode AND state = 0 AND order_id NOT IN (SELECT l.id FROM think_red_order_info AS i INNER JOIN  think_red_order_list AS l ON l.id = i.order_id WHERE i.member_id = $user_id OR l.user_id = $user_id AND i.state = 1 ) AND sqrt( ( (($lng-lng)*PI()*12656*cos((($lat+lat)/2)*PI()/180)/180) * (($lng-lng)*PI()*12656*cos ((($lat+lat)/2)*PI()/180)/180) ) + ( (($lat-lat)*PI()*12656/180) * (($lat-lat)*PI()*12656/180) ) )< $distance group by order_id limit $red_number;";
        //$red_list_sql = " SELECT id,order_id,lng,lat,state FROM think_red_order_info WHERE citycode = $citycode AND state = 0 AND sqrt( ( (($lng-lng)*PI()*12656*cos((($lat+lat)/2)*PI()/180)/180) * (($lng-lng)*PI()*12656*cos ((($lat+lat)/2)*PI()/180)/180) ) + ( (($lat-lat)*PI()*12656/180) * (($lat-lat)*PI()*12656/180) ) )< $distance group by order_id limit 10;";
        $city_list = Db::query($red_list_sql);
        if(count($city_list) > 0){
            $city_list = $city_list;
        }else{
            $city_list = [];
        }
        //城市红包  距离不足查询城市
        if(count($city_list) < $red_number){
            $num = $red_number - count($city_list);
            $sql_2 = "SELECT i.* FROM think_red_order_info as i INNER JOIN think_red_order_list as l ON l.id = i.order_id WHERE i.state = 0 AND i.type = 3 AND  order_id NOT IN (SELECT order_id FROM think_red_order_info WHERE member_id = $user_id AND state = 1 ) AND l.user_id <> $user_id  group by order_id limit $num;";
            $city_list_2 = Db::query($sql_2);
            $array = [];
            foreach ($city_list_2 as $a=>$b) {
                $location = $this->getAround1($lat,$lng,1000);
                $array = [
                    'id'=>$b['id'],
                    'order_id'=>$b['order_id'],
                    'lng'=>$location['lng'],
                    'lat'=>$location['lat'],
                ];
                $city_list[]=$array;
            }
        }
        //城市红包  距离不足查询城市
        if(count($city_list) < $red_number){
            $num = $red_number - count($city_list);
            $sql_2 = "SELECT i.* FROM think_red_order_info as i INNER JOIN think_red_order_list as l ON l.id = i.order_id WHERE i.state = 0 AND i.type = 4 AND  order_id NOT IN (SELECT order_id FROM think_red_order_info WHERE member_id = $user_id AND state = 1 ) AND l.user_id <> $user_id  group by order_id limit $num;";
            $city_list_2 = Db::query($sql_2);
            $array = [];
            foreach ($city_list_2 as $a=>$b) {
                $location = $this->getAround1($lat,$lng,1000);
                $array = [
                    'id'=>$b['id'],
                    'order_id'=>$b['order_id'],
                    'lng'=>$location['lng'],
                    'lat'=>$location['lat'],
                ];
                $city_list[]=$array;
            }
        }
        //全国红包  城市的也不足
        return json(['code'=>1011,'msg'=>'成功','data'=>$city_list]);
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
        $array = ['minLat'=>sprintf("%.6f",$minLat), 'maxLat'=>sprintf("%.6f",$maxLat), 'minLng'=>sprintf("%.6f",$minLng), 'maxLng'=>sprintf("%.6f",$maxLng)];
        return $array;
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
        $MemberModel = new MemberModel();
        $member_info = $MemberModel->getMemberInfo('id',['uuid'=>$data['uuid']]);
        $order_id = input('post.id');
        $order_info = Db::name('red_order_info')->where(['id'=>$order_id])->find();
        if(!$order_info){
            return json(['code'=>1012,'msg'=>'红包不存在','data'=>'']);
        } elseif($order_info['state'] == 1){
            return json(['code'=>1012,'msg'=>'红包已领取','data'=>'']);
        }else{
            $user_money = Db::name('money')->where('user_id',$member_info['id'])->find();
            if($user_money['total_red_number'] < 1){
                return json(['code'=>1012,'msg'=>'领取红包次数已用完','data'=>'']);
            }
            $order_list = Db::name('red_order_list')->where('id',$order_info['order_id'])->find();
            $order_info_count = Db::name('red_order_info')->where(['order_id'=>$order_info['order_id'],'member_id'=>$member_info['id']])->count();
            if($order_info_count > 0){
                return json(['code'=>1011,'msg'=>'红包已领取','data'=>'']);
            }
            Db::startTrans();
            try{
                //修改红包信息
                Db::name('red_order_info')->where('id',$order_info['id'])->update(['member_id'=>$member_info['id'],'state'=>1,'add_time'=>time()]);
                //给用户增加金额
                $money = $order_info['money'];
                Db::name('money')->where('user_id',$member_info['id'])->setInc('balance',$money);
                Db::name('money')->where('user_id',$member_info['id'])->setDec('total_red_number',1);
                //添加记录
                getAddMoneyLog($member_info['id'],$money,$user_money['balance'],$user_money['balance']+$money,1,1,'领取红包',$order_list['order_number'],'1',time(),'');
                //分润上级
                $this->two_level_award($member_info['id'],$money);
                Db::commit();
                return json(['code'=>1011,'msg'=>'红包领取成功','data'=>'']);
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
     * 领取红包列表
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
        $total_money = Db::name('red_order_info')->where(['member_id'=>$member_info['id'],'state'=>1])->sum('money');
        $total_count = Db::name('red_order_info')->where(['member_id'=>$member_info['id'],'state'=>1])->count();
        $today_money = Db::name('red_order_info')->where(['member_id'=>$member_info['id'],'state'=>1])->whereTime('add_time','d')->sum('money');
        $today_count = Db::name('red_order_info')->where(['member_id'=>$member_info['id'],'state'=>1])->whereTime('add_time','d')->count();
        $list = Db::name('red_order_list')->alias('l')->field('l.id,l.img_path,l.content,i.add_time')
            ->where(['i.member_id'=>$member_info['id'],'i.state'=>1])->join('red_order_info i','i.order_id = l.id')->page($page,15)->order('add_time DESC')->select();
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
        return json(['code'=>1011,'msg'=>'成功','data'=>$list,'total_money'=>(string)$total_money,'total_count'=>(string)$total_count,'today_money'=>(string)$today_money,'today_count'=>(string)$today_count]);
    }
    /**
     * issue_red_packets
     * 发放红包列表
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
        $total_count = Db::name('red_order_list')->where(['user_id'=>$member_info['id']])->count();
        $today_money = Db::name('red_order_list')->where(['user_id'=>$member_info['id']])->whereTime('add_time','d')->sum('original_money');
        $today_count = Db::name('red_order_list')->where(['user_id'=>$member_info['id']])->whereTime('add_time','d')->count();
        $list = Db::name('red_order_list')->field('id,original_money,add_time')->where(['user_id'=>$member_info['id']])->page($page,15)->order('add_time DESC')->select();
        if(count($list) > 0){
            foreach ($list as $k=>$v){
                $list[$k]['add_time'] = date('Y-m-d H:i:s',$v['add_time']);
            }
        }
        return json(['code'=>1011,'msg'=>'成功','data'=>$list,'total_money'=>(string)$total_money,'total_count'=>(string)$total_count,'today_money'=>(string)$today_money,'today_count'=>(string)$today_count]);
    }
    /**
     * 红包详情
     */
    public function red_packets_info(){
        $data = input('post.');
        $validate_res = $this->validate($data,'HomeValidate.whole');
        if($validate_res !== true){ return json(['code'=>1015,'msg'=>$validate_res]); } //数据认证
        if(getSign($data) != $data['Sign']){ return json(['code'=>1013,'msg'=>'签名错误']);} //签名认证
        if(Cache::get($data['uuid'].'_token') != $data['token']) return json(['code'=>1004,'msg'=>'用户未登录']);//登陆验证
        $MemberModel = new MemberModel();
        $member_info = $MemberModel->getMemberInfo('id,user_img',['uuid'=>$data['uuid']]);
        $id = input('post.id');
        $info = Db::name('red_order_list')->where('id',$id)->find();
        $money = '';
        $red_member_detail = $MemberModel->getMemberInfo('id,username,user_img',['id'=>$info['user_id']]);
        if($info['user_id'] == $member_info['id']){
            //查看自己的红包
            $money = Db::name('red_order_info')->where(['order_id'=>$info['id'],'state'=>0])->sum('money');
            $time = date('Y-m-d H:i:s',$info['add_time']);
        }else{
            //查看领取的红包
            $order_info = Db::name('red_order_info')->field("money,FROM_UNIXTIME(add_time, '%Y-%m-%d') as add_time")->where(['order_id'=>$info['id'],'member_id'=>$member_info['id'],'state'=>1])->find();
            $money = $order_info['money'];
            $time = $order_info['add_time'];
        }
        switch ($info['distance']){
            case 1:
                $str = '一公里';
                break;
            case 2:
                $str = '五公里';
                break;
            case 3:
                $str = '全市';
                break;
            case 4:
                $str = '全国';
                break;
            default:
                $str = '暂无类型';
        }
        $red_member_list = Db::name('red_order_info')->alias('i')->field('m.user_img')->where(['i.state'=>1,'i.order_id'=>$info['id']])->join('member m','m.id=i.member_id')->limit(10)->select();
        $url = web_url_str();
        $hongbao = Db::name('banner')->field("CONCAT('$url',path) as path,web_url")->where(['group_id'=>4])->order('id DESC')->find();
        $hongbao = $hongbao?$hongbao:"";
        $array = [
            'user_img'=>$red_member_detail['user_img'],
            'username'=>$red_member_detail['username'],
            'content'=>$info['content'],
            'money'=>sprintf("%.2f",$money),
            'type'=>$str,
            'add_time'=>$time,
        ];
        $img_path = explode(",", $info['img_path']);
        $img_array = [];
        if(count($img_path) > 0){
            if(!empty($img_path[0])){
                foreach ($img_path as $a=>$b){
                    $img_array[]['value']=web_url_str().$b;
                }
            }else{
                $img_array = [];
            }
        }
        return json(['code'=>1011,'msg'=>'成功','data'=>$array,'img_path'=>$img_array,'member_list'=>$red_member_list,'banner_img'=>$hongbao]);
    }

    /**
     * 红包领取人数列表
     */
    public function hongbao_info(){
        $data = input('post.');
        $validate_res = $this->validate($data,'HomeValidate.whole');
        if($validate_res !== true){ return json(['code'=>1015,'msg'=>$validate_res]); } //数据认证
        if(getSign($data) != $data['Sign']){ return json(['code'=>1013,'msg'=>'签名错误']);} //签名认证
        if(Cache::get($data['uuid'].'_token') != $data['token']) return json(['code'=>1004,'msg'=>'用户未登录']);//登陆验证
        $MemberModel = new MemberModel();
        $member_info = $MemberModel->getMemberInfo('id,username,user_img',['uuid'=>$data['uuid']]);
        $id = input('post.id');
        $page = input('post.page')?input('post.page'):1;
        $order_info =  Db::name('red_order_list')->where('id',$id)->find();
        $ling_qu = Db::name('red_order_info')->alias('i')->field("m.username,m.user_img,i.money,FROM_UNIXTIME(i.add_time, '%Y-%m-%d') as add_time")->where(['i.order_id'=>$id,'i.state'=>1])->join('member m','m.id = i.member_id')->page($page,10)->select();
        if($member_info['id'] == $order_info['user_id']){
            $money = Db::name('red_order_info')->where(['order_id'=>$id,'state'=>1])->sum('money');
            $money = $order_info['money'] - $money;
            $member_info = $MemberModel->getMemberInfo('id,username,user_img',['id'=>$order_info['user_id']]);
        }else{
            $money = Db::name('red_order_info')->where(['member_id'=>$member_info['id'],'order_id'=>$order_info['id']])->value('money');
        }
        $info_count = Db::name('red_order_info')->where(['order_id'=>$id,'state'=>1])->count();
        $array = [
            'username'=>$member_info['username'],
            'user_img'=>$member_info['user_img'],
            'money'=>(string)$money,
            'number'=>$info_count
        ];
        return json(['code'=>1011,'msg'=>'成功','data'=>$array,'lingqu'=>$ling_qu]);
    }

    public function member_red_list(){
        $data = input('post.');
        $validate_res = $this->validate($data,'HomeValidate.whole');
        if($validate_res !== true){ return json(['code'=>1015,'msg'=>$validate_res]); } //数据认证
        if(getSign($data) != $data['Sign']){ return json(['code'=>1013,'msg'=>'签名错误']);} //签名认证
        if(Cache::get($data['uuid'].'_token') != $data['token']) return json(['code'=>1004,'msg'=>'用户未登录']);//登陆验证
        $MemberModel = new MemberModel();
        $member_info = $MemberModel->getMemberInfo('id,user_img',['uuid'=>$data['uuid']]);
        $page = input('post.page')?input('post.page'):1;
        $list = Db::name('red_order_list')->field("id,content,img_path,FROM_UNIXTIME(add_time, '%Y-%m-%d') as add_time")->where(['user_id'=>$member_info['id'],'state'=>1])->page($page,15)->order('add_time DESC')->select();

        $number = Db::name('red_order_list')->where(['user_id'=>$member_info['id'],'state'=>1])->count();
        $place_money = Db::name('red_order_list')->where(['user_id'=>$member_info['id'],'state'=>1])->sum('money');
        $enter_money = Db::name('red_order_info')->where('member_id',$member_info['id'])->sum('money');

        if(count($list) > 0){
            foreach ($list as $k=>$v){
                $keys = $v['add_time'];
                $new_list[$keys]['time'] = $v['add_time'];
                $img_path = explode(",", $v['img_path']);
                if(count($img_path) > 0){
                    $url = web_url_str();
                    if(!empty($img_path['0'])){
                        $v['img_path'] = $url.$img_path['0'];
                    }
                }
                $new_list[$keys]['value'][] = $v;
            }
            foreach ($new_list as $a => $b){
                $final_list[]=$b;
            }
            return json(['code'=>1011,'msg'=>'成功','data'=>$final_list,'place_money'=>(string)$place_money,'enter_money'=>(string)$enter_money,'number'=>(string)$number]);
        }else{
            return json(['code'=>1012,'msg'=>'暂无数据','data'=>[],'place_money'=>(string)$place_money,'enter_money'=>(string)$enter_money,'number'=>(string)$number]);
        }
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

    /**
     * 会员升级二级分润
     * $user_id 推荐人账号
     * $money 分润金额
     */
    private function two_level_award($user_id,$money)
    {
        $config = privilege_config_list();
        $user_info = Db::name('member')->where('id',$user_id)->find();
        if($user_info['pid'] > 0){  //一级
            $p_user_info = Db::name('member')->where('id',$user_info['pid'])->find();
            $p_money_list = Db::name('money')->where('user_id',$p_user_info['id'])->find();
            if($p_user_info){
                switch ($p_user_info['type']){
                    case 1: //普通会员
                        $p_bonus = $money*$config['ordinary_one_hongbao_bonus']/100;
                        break;
                    case 2: //vip会员
                        $p_bonus = $money*$config['vip_one_hongbao_bonus']/100;
                        break;
                    case 3: //广告商
                        $p_bonus = $money*$config['partner_one_hongbao_bonus']/100;
                        break;
                }
            }
            if($p_bonus > 0) {
                Db::name('money')->where('user_id', $p_user_info['id'])->setInc('bonus', $p_bonus);
                Db::name('money')->where('user_id', $p_user_info['id'])->setInc('one_bonus_log', $p_bonus);
                $money_log = [
                    'user_id' => $p_user_info['id'],
                    'type' => '4',
                    'money' => $p_bonus,
                    'original' => $p_money_list['bonus'],
                    'now' => $p_money_list['bonus'] + $p_bonus,
                    'state' => '1',
                    'info' => $user_info['mobile'] . '领取红包获得',
                    'trend' => '2',
                    'create_time' => time(),
                    'son_id' => $user_id,
                ];
                Db::name('money_log')->insert($money_log);
            }
        }
        if($user_info['gid'] > 0){  //二级
            $g_user_info = Db::name('member')->where('id',$user_info['gid'])->find();
            $g_money_list = Db::name('money')->where('user_id',$g_user_info['id'])->find();
            if($g_user_info){
                switch ($g_user_info['type']){
                    case 1: //普通会员
                        $g_bonus = $money*$config['ordinary_two_hongbao_bonus']/100;
                        break;
                    case 2: //vip会员
                        $g_bonus = $money*$config['vip_two_hongbao_bonus']/100;
                        break;
                    case 3: //广告商
                        $g_bonus = $money*$config['partner_two_hongbao_bonus']/100;
                        break;
                }
                if($g_bonus > 0) {
                    Db::name('money')->where('user_id', $g_user_info['id'])->setInc('bonus', $g_bonus);
                    Db::name('money')->where('user_id', $g_user_info['id'])->setInc('two_bonus_log', $g_bonus);
                    $money_log = [
                        'user_id' => $g_user_info['id'],
                        'type' => '4',
                        'money' => $g_bonus,
                        'original' => $g_money_list['bonus'],
                        'now' => $g_money_list['bonus'] + $g_bonus,
                        'state' => '1',
                        'info' => $user_info['mobile'] . '领取红包获得',
                        'trend' => '2',
                        'create_time' => time(),
                        'son_id' => $user_id,
                    ];
                    Db::name('money_log')->insert($money_log);
                }
            }
        }
    }


}