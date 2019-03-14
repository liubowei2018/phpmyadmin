<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 流年 <liu21st@gmail.com>
// +----------------------------------------------------------------------
use think\Cache;
// 应用公共文件
function getSign($arr)
{
    $key = '0f4137ed1502b5045d6083aa258b5c42';
    //去除数组中的空值
    foreach ($arr as $k=>$v){
        if($v == ''){
            unset($arr[$k]);
        }
    }
    //如果数组中有签名删除签名
    if(isset($arr['Sign']))
    {
        unset($arr['Sign']);
    }
    //按照键名字典排序
    ksort($arr);
    //生成URL格式的字符串
    //http_build_query()中文自动转码需要处理下
    $str1 = http_build_query($arr);

    $str1 = urldecode($str1).'&key='.$key;
    return  md5($str1);
}


/**
 * POST请求https接口返回内容
 * @param  string $url [请求的URL地址]
 * @param  string $post [请求的参数]
 * @return  string
 */
function post_curls($url, $post)
{
    $curl = curl_init(); // 启动一个CURL会话
    curl_setopt($curl, CURLOPT_URL, $url); // 要访问的地址
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0); // 对认证证书来源的检查
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2); // 从证书中检查SSL加密算法是否存在
    curl_setopt($curl, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']); // 模拟用户使用的浏览器
    curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1); // 使用自动跳转
    curl_setopt($curl, CURLOPT_AUTOREFERER, 1); // 自动设置Referer
    curl_setopt($curl, CURLOPT_POST, 1); // 发送一个常规的Post请求
    curl_setopt($curl, CURLOPT_POSTFIELDS, $post); // Post提交的数据包
    curl_setopt($curl, CURLOPT_TIMEOUT, 30); // 设置超时限制防止死循环
    curl_setopt($curl, CURLOPT_HEADER, 0); // 显示返回的Header区域内容
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1); // 获取的信息以文件流的形式返回
    $res = curl_exec($curl); // 执行操作
    if (curl_errno($curl)) {
        echo 'Errno'.curl_error($curl);//捕抓异常
    }
    curl_close($curl); // 关闭CURL会话
    return $res; // 返回数据，json格式
}


/*
 * curl post请求 访问https
 *
 * */
function curl_post_https($url,$data){ // 模拟提交数据函数
    $curl = curl_init(); // 启动一个CURL会话
    curl_setopt($curl, CURLOPT_URL, $url); // 要访问的地址
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0); // 对认证证书来源的检查
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2); // 从证书中检查SSL加密算法是否存在
    curl_setopt($curl, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']); // 模拟用户使用的浏览器
    curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 0); // 使用自动跳转
    curl_setopt($curl, CURLOPT_AUTOREFERER, 1); // 自动设置Referer
    curl_setopt($curl, CURLOPT_POST, 1); // 发送一个常规的Post请求
    curl_setopt($curl, CURLOPT_POSTFIELDS, $data); // Post提交的数据包
    curl_setopt($curl, CURLOPT_TIMEOUT, 30); // 设置超时限制防止死循环
    curl_setopt($curl, CURLOPT_HEADER, 0); // 显示返回的Header区域内容
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1); // 获取的信息以文件流的形式返回
    curl_setopt($curl, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
        )
    );
    $tmpInfo = curl_exec($curl); // 执行操作
    if (curl_errno($curl)) {
        echo 'Errno'.curl_error($curl);//捕抓异常
    }
    curl_close($curl); // 关闭CURL会话
    return $tmpInfo; // 返回数据，json格式
}

/**
 * get https请求
 * @param $url
 * @return bool|string
 */
function curl_get_https($url){
    $curl = curl_init(); // 启动一个CURL会话
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_HEADER, 0);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false); // 跳过证书检查
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2);  // 从证书中检查SSL加密算法是否存在
    $tmpInfo = curl_exec($curl);     //返回api的json对象
    //关闭URL请求
    curl_close($curl);
    return $tmpInfo;    //返回json对象
}


/**
 * 获取网站链接
 */
function web_url_str(){
    return $str = 'http://'.$_SERVER['SERVER_NAME'];
}

/**
 * 逆地理编码
 * @param $lng 纬度
 * @param $lat 经度
 * @param $user_id 用户id
 */
function current_city($lng,$lat,$user_id){
    $key = "b15d61df8800f59bc986419017d018d0";
    $location = $lng.','.$lat;
    $url = "https://restapi.amap.com/v3/geocode/regeo?key=$key&location=$location";
    $citycode = Cache::get("current_city_$user_id");

    if($citycode){
        return $citycode;
    }else{
        $res = curl_get_https($url);
        $res = json_decode($res,true);
        if($res['info'] == 'OK'){
            $citycode = $res['regeocode']['addressComponent']['citycode'];
            Cache::set("current_city_$user_id",$citycode,3600);
        }
        return $citycode;
    }
}


/**
 * 强制转换为32位整数
 * @param $var
 * @return float|int|string
 */
function uint32val($var) {
    if (is_string($var)) {
        if (PHP_INT_MAX > 2147483647) {
            $var = intval($var);
        } else {
            $var = floatval($var);
        }
    }
    if (!is_int($var)) {
        $var = intval($var);
    }
    if ((0 > $var) || ($var > 4294967295)) {
        $var &= 4294967295;
        if (0 > $var) {
            $var = sprintf('%u', $var);
        }
    }
    return $var;
}

/**
 * @param $user_id  用户id
 * @param $money    操作数量
 * @param $original 原始数量
 * @param $now      现有数量
 * @param $type     类型   1 余额 2 积分 3 微信 4奖金
 * @param $state    状态   1 增加 2 减少
 * @param $info     详情
 * @param $source   来源订单号
 * @param $trend    作用
 * @param $create_time  时间
 * @param $son_id   子级id
 */
function getAddMoneyLog($user_id,$money,$original,$now,$type,$state,$info,$source,$trend,$create_time,$son_id){
    $data = [
        'user_id'=>$user_id,
        'money'=>$money,
        'original'=>$original,
        'now'=>$now,
        'type'=>$type,
        'state'=>$state,
        'info'=>$info,
        'source'=>$source,
        'trend'=>$trend,
        'create_time'=>$create_time,
        'son_id'=>$son_id,
    ];
   $res =  \think\Db::name('money_log')->insert($data);
   return $res;
}

/**
 * 管理修改订单记录
 * @param $user_id
 * @param $info
 * @param $order_number
 * @param $admin_id
 * @param $admin_name
 */
function getAddAdminOrderLog($user_id,$info,$order_number,$admin_id,$admin_name){
    $data = [
        'user_id'=>$user_id,
        'info'=>$info,
        'order_number'=>$order_number,
        'admin_id'=>$admin_id,
        'admin_name'=>$admin_name,
        'add_time'=>time(),
    ];
   $res =  \think\Db::name('admin_order')->insert($data);
    return $res;
}
function getAddAdminMoneyLog($user_id,$money,$type,$state,$info,$admin_id,$admin_name){
    $data = [
        'user_id'=>$user_id,
        'money'=>$money,
        'type'=>$type,
        'state'=>$state,
        'info'=>$info,
        'admin_id'=>$admin_id,
        'admin_name'=>$admin_name,
        'add_time'=>time(),
    ];
    $res = \think\Db::name('admin_money')->insert($data);
    return $res;
}