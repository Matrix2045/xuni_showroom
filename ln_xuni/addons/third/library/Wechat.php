<?php

namespace addons\third\library;

use fast\Http;
use think\Config;
use think\Session;
use think\Cookie;

/**
 * 微信
 */
class Wechat
{
    const GET_AUTH_CODE_URL = "https://open.weixin.qq.com/connect/oauth2/authorize";
    const GET_ACCESS_TOKEN_URL = "https://api.weixin.qq.com/sns/oauth2/access_token";
    const GET_USERINFO_URL = "https://api.weixin.qq.com/sns/userinfo";
    const GET_SHARETOKRN_URL = 'https://api.weixin.qq.com/cgi-bin/token';
    const GET_TICKET_URL = "https://api.weixin.qq.com/cgi-bin/ticket/getticket";

    /**
     * 配置信息
     * @var array
     */
    private $config = [];

    public function __construct($options = [])
    {
        if ($config = Config::get('third.wechat')) {
            $this->config = array_merge($this->config, $config);
        }
        $this->config = array_merge($this->config, is_array($options) ? $options : []);
    }

    /**
     * 登陆
     */
    public function login()
    {
        header("Location:" . $this->getAuthorizeUrl());
    }

    /**
     * 获取authorize_url
     */
    public function getAuthorizeUrl()
    {
        $state = md5(uniqid(rand(), true));
        Session::set('state', $state);
        $queryarr = array(
            "appid"         => $this->config['app_id'],
            "redirect_uri"  => $this->config['callback'],
            "response_type" => "code",
            "scope"         => $this->config['scope'],
            "state"         => $state,
        );
        request()->isMobile() && $queryarr['display'] = 'mobile';
        $url = self::GET_AUTH_CODE_URL . '?' . http_build_query($queryarr) . '&connect_redirect=1#wechat_redirect';
        return $url;
    }

    /**
     * 获取用户信息
     * @param array $params
     * @return array
     */
    public function getUserInfo($params = [])
    {
        $params = $params ? $params : request()->get();
        if (isset($params['access_token']) || (isset($params['state']) && $params['state'] == Session::get('state') && isset($params['code']))) {
            //获取access_token
            $data = isset($params['code']) ? $this->getAccessToken($params['code']) : $params;
            $access_token = isset($data['access_token']) ? $data['access_token'] : '';
            $refresh_token = isset($data['refresh_token']) ? $data['refresh_token'] : '';
            $expires_in = isset($data['expires_in']) ? $data['expires_in'] : 0;
            if ($access_token) {
                $openid = isset($data['openid']) ? $data['openid'] : '';
                $unionid = isset($data['unionid']) ? $data['unionid'] : '';
                //获取用户信息
                $queryarr = [
                    "access_token" => $access_token,
                    "openid"       => $openid,
                    "lang"         => 'zh_CN'
                ];
                $ret = Http::get(self::GET_USERINFO_URL, $queryarr);
                $userinfo = (array)json_decode($ret, true);
                if (!$userinfo || isset($userinfo['errcode'])) {
                    return [];
                }
                $userinfo = $userinfo ? $userinfo : [];
                $userinfo['avatar'] = isset($userinfo['headimgurl']) ? $userinfo['headimgurl'] : '';
                
                $data = [
                    'access_token'  => $access_token,
                    'refresh_token' => $refresh_token,
                    'expires_in'    => $expires_in,
                    'openid'        => $openid,
                    'unionid'       => $unionid,
                    'userinfo'      => $userinfo
                ];
                return $data;
            }
        }
        return [];
    }

    /**
     * 获取access_token
     * @param string code
     * @return array
     */
    public function getAccessToken($code = '')
    {
        if (!$code) {
            return [];
        }
        $queryarr = array(
            "appid"      => $this->config['app_id'],
            "secret"     => $this->config['app_secret'],
            "code"       => $code,
            "grant_type" => "authorization_code",
        );
        $response = Http::get(self::GET_ACCESS_TOKEN_URL, $queryarr);
        $ret = (array)json_decode($response, true);
        return $ret ? $ret : [];
    }



    //分享
    public function getJsApiTicket()
    {
        // jsapi_ticket 应该全局存储与更新，以下代码以写入到文件中做示例
        $ticket = Cookie::get("weixin_share_ticket");
        if (empty($ticket)) {
            $accessToken = $this->getShareToken();
            $queryarr = array(
                "access_token" => $accessToken,
                "type"         => 1,
            );
            $response = Http::get(self::GET_TICKET_URL, $queryarr);
            $ret = (array)json_decode($response, true);
            $ticket = $ret['ticket'];
            if($ticket){
                Cookie::set('weixin_share_ticket', $ticket, 7000);
            }
        } 
        return $ticket;
    }

    public function getShareToken()
    {
        $access_token = Cookie::get("weixin_share_token");
        if(empty($access_token)){
            $queryarr = array(
                "appid"      => $this->config['app_id'],
                "secret"     => $this->config['app_secret'],
                "grant_type" => "client_credential",
            );
            $response = Http::get(self::GET_SHARETOKRN_URL, $queryarr);
            $ret = (array)json_decode($response, true);
            $access_token = $ret['access_token'];
            if($access_token){
                Cookie::set('weixin_share_token', $access_token, 7000);
            }
        }

        return $access_token;
    }

    public function createNonceStr($length = 16)
    {
        $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
        $str = "";
        for ($i = 0; $i < $length; $i++) {
            $str .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
        }
        return $str;
    }

    public function appId()
    {
        return $this->config['app_id'];
    }
}
