<?php

namespace addons\third\controller;

use addons\third\library\Application;
use addons\third\library\Service;
use addons\third\model\Third;
use think\addons\Controller;
use think\Config;
use think\Cookie;
use think\Hook;
use think\Lang;
use think\Session;

/**
 * 第三方登录插件
 */
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Authorization, Content-Type, If-Match, If-Modified-Since, If-None-Match, If-Unmodified-Since, X-Requested-With');
header('Access-Control-Allow-Methods: GET, POST, PATCH, PUT, DELETE');
header('Access-Control-Max-Age: 1728000');
class Index extends Controller
{
    protected $app = null;
    protected $options = [];

    public function _initialize()
    {
        parent::_initialize();
        $config = get_addon_config('third');
        $this->app = new Application($config);
    }

    /**
     * 插件首页
     */
    public function index()
    {
        if (!\app\admin\library\Auth::instance()->id) {
            $this->error('当前插件暂无前台页面');
        }
        $platformList = [];
        if ($this->auth->id) {
            $platformList = Third::where('user_id', $this->auth->id)->column('platform');
        }
        $this->view->assign('platformList', $platformList);
        return $this->view->fetch();
    }

    /**
     * 发起授权
     */
    public function connect()
    {
        $platform = $this->request->param('platform');
        $url = Session::has("url") ? Session::pull("url") : $this->request->request('url', $this->request->server('HTTP_REFERER', '/'), 'trim');
        if (!$this->app->{$platform}) {
            $this->error(__('Invalid parameters'));
        }
        if ($url) {
            Session::set("redirecturl", $url);
        }
        // 跳转到登录授权页面
        $this->redirect($this->app->{$platform}->getAuthorizeUrl());// Header("Location: $wxurl");
        return;
    }

    /**
     * 通知回调
     */
    public function callback()
    {
        $auth = $this->auth;

        //监听注册登录注销的事件
        Hook::add('user_login_successed', function ($user) use ($auth) {
            $expire = input('post.keeplogin') ? 30 * 86400 : 0;
            Cookie::set('uid', $user->id, $expire);
            Cookie::set('token', $auth->getToken(), $expire);
        });
        Hook::add('user_register_successed', function ($user) use ($auth) {
            Cookie::set('uid', $user->id);
            Cookie::set('token', $auth->getToken());
        });
        Hook::add('user_logout_successed', function ($user) use ($auth) {
            Cookie::delete('uid');
            Cookie::delete('token');
        });
        $platform = $this->request->param('platform');
        // 成功后返回之前页面
        $url = Session::has("redirecturl") ? Session::pull("redirecturl") : 'http://zg.hhscpjy.com/'.url('index/user/index');

        // 授权成功后的回调
        $userinfo = $this->app->{$platform}->getUserInfo();
        if (!$userinfo) {
            $this->error(__('操作失败'), $url);
        }


        Session::set("{$platform}-userinfo", $userinfo);
        //判断是否启用账号绑定
        $third = Third::get(['platform' => $platform, 'openid' => $userinfo['openid']]);

        $loginret = Service::connect($platform, $userinfo);
        if($loginret){
            $urls = parse_url($url);
            if($urls['host']=="zg.hhscpjy.com" && $urls['path'] == '/hallshow/index.html' && empty($urls['query'])){
                $url = 'http://'.$urls['host'].$urls['path'].'?token='.$loginret;
            }elseif($urls['host']=="zg.hhscpjy.com" && $urls['path'] == '/hallshow/index.html' && !empty($urls['query'])){
                $query = explode('&', $urls['query']);
                foreach ($query as $key => &$value) {
                    $array = explode('=', $value);
                    if($array[0]=="token"){
                        $array[1]= $loginret;
                    }
                    $value = implode('=', $array);
                }
                if(!in_array('token='.$loginret, $query)){
                    $url = $url.'&token='.$loginret;
                }
            }
            $this->redirect($url);
        }
    }

    /**
     * 绑定账号
     */
    public function bind()
    {
        $platform = $this->request->request('platform', $this->request->param('platform', ''));
        $url = $this->request->get('url', $this->request->server('HTTP_REFERER'));
        $redirecturl = url("index/third/bind") . "?" . http_build_query(['platform' => $platform, 'url' => $url]);
        $this->redirect($redirecturl);
        return;
    }

    /**
     * 解绑账号
     */
    public function unbind()
    {
        $platform = $this->request->request('platform', $this->request->param('platform', ''));
        $url = $this->request->get('url', $this->request->server('HTTP_REFERER'));
        $redirecturl = url("index/third/unbind") . "?" . http_build_query(['platform' => $platform, 'url' => $url]);
        $this->redirect($redirecturl);
        return;
    }


    /**
     * 微信分享参数处理
     */
    public function share()
    {
        $jsapiTicket =$this->app->wechat->getJsApiTicket();
        $url = urldecode(input('url'));
        $timestamp = time();
        $nonceStr = $this->app->wechat->createNonceStr();
        // 这里参数的顺序要按照 key 值 ASCII 码升序排序
        $string = "jsapi_ticket=$jsapiTicket&noncestr=$nonceStr&timestamp=$timestamp&url=$url";
        $signature = sha1($string);
        $signPackage = array(
            "appId"     => $this->app->wechat->appId(),
            "nonceStr"  => $nonceStr,
            "timestamp" => $timestamp,
            "url"       => $url,
            "signature" => $signature,
            "rawString" => $string
        );

        $result = [
            'code' => 200,
            'msg'  => '',
            'time' => $this->request->server('REQUEST_TIME'),
            'data' => $signPackage,
        ];
        return json($result);
    }
}
