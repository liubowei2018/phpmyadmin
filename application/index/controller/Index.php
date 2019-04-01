<?php
namespace app\index\controller;

use app\Interactive\model\MoneyModel;
use think\Controller;
use think\Db;
use think\Session;

class Index extends Controller
{
    public function index()
    {
        /*        $data = request()->get();
                $token="rongdian";
                $sigArr=[$token,$data['timestamp'],$data['nonce']];
                sort($sigArr,SORT_STRING);
                $sigArr = implode($sigArr);
                $enctystr = sha1($sigArr);
                if ($data['signature'] === $enctystr) {
                    echo $data['echostr'];
                }else{
                    return false;
                }*/
        $param = input('param.phone');
        $member_id = Session::get('wx_unionid');
        if(empty($member_id)){
            $this->redirect('index/authorization', ['phone' => $param]);
        }else{
            $this->redirect('index/dowlone', ['phone' => $param]);
        }
        return $this->fetch();
    }

    public function dowlone(){
        return view('index');
    }
    /**
     * 微信授权
     */
    public function authorization(){
        $phone = input('param.phone');
        $code = request()->get('code');
        $wechatConfig = config('WeChatConfig');
        if(empty($code)){
            $web_url = web_url_str()."/index/index/authorization/phone/".$phone;
            $curUrl = urlencode($web_url);
            $url = "https://open.weixin.qq.com/connect/oauth2/authorize?appid={$wechatConfig['app_id']}&redirect_uri={$curUrl}&response_type=code&scope=snsapi_userinfo&state=STATE#wechat_redirect ";
            return redirect($url);
        }
        //使用code 换去accesstoken
        $url = "https://api.weixin.qq.com/sns/oauth2/access_token?appid={$wechatConfig['app_id']}&secret={$wechatConfig['app_security']}&code={$code}&grant_type=authorization_code";
        $result = get_request($url);
        $resultArr = json_decode($result,true);
        $accessToken = $resultArr['access_token'];
        $openId = $resultArr['openid'];
        //请求用户信息
        $userUrl = "https://api.weixin.qq.com/sns/userinfo?access_token={$accessToken}&openid={$openId}&lang=zh_CN";
        $user_info = get_request($userUrl);
        $userInfoArr = json_decode($user_info,true);
        Session::set('wx_unionid',$userInfoArr['unionid']);
        $member = Db::name('member')->where('uuid',$userInfoArr['unionid'])->find();
        $t_member = Db::name('member')->where('mobile',$phone)->find();
        if(!$member){
            //用户不存在
            $member_data = [
                'uuid'=>$userInfoArr['unionid'],
                'username'=>$userInfoArr['nickname'],
                'user_img'=>$userInfoArr['headimgurl'],
                'sex'=>$userInfoArr['sex']?'男':'女',
                'create_time'=>time()
            ];
            $member_id = Db::name('member')->insertGetId($member_data);
            $config = $this->red_config();
            $MoneyModel = new MoneyModel();
            $MoneyModel->getAddInfo(['user_id' => $member_id,'red_number'=>$config['ordinary_today_hongbao_number'],'total_red_number'=>$config['ordinary_today_hongbao_number'],'today_state'=>1]);
            if($t_member){
                if($member_id != $t_member['id']){
                    $this->save_recommender($member_id,$t_member['id'],$t_member['pid']);
                }
            }
        }else{
            if(empty($member['pid'])){
                if($t_member){

                    if($member['id'] != $t_member['id']){
                        $this->save_recommender($member['id'],$t_member['id'],$t_member['pid']);
                    }
                }
            }
        }
        //处理完毕跳转回 下载页面
        $this->redirect('index/dowlone');
    }

    /**
     * 修改推荐人
     */
    private function save_recommender($uid,$pid,$gid){
        Db::name('member')->where('id',$uid)->update(['pid'=>$pid,'gid'=>$gid]);
    }
    private function red_config(){
        $app_config = Db::name('privilege_config')->select();
        $config = [];
        foreach ($app_config as $k => $v) {
            $config[trim($v['name'])]=$v['value'];
        }
        return $config;
    }
}
