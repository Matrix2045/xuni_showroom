<?php

namespace app\index\controller;

use app\common\controller\Frontend;
use app\common\library\Sms;
use think\Config;
use think\Cookie;
use think\Hook;
use think\Validate;
use fast\Random;
use app\common\model\User as UserModel;
use app\index\model\ApplyHall as ApplyModel;
use think\Db;

/**
 * 会员中心
 */
class User extends Frontend
{
    protected $layout = 'default';
    protected $noNeedLogin = ['login', 'register','forgetpwd','user_agreement'];
    protected $noNeedRight = ['*'];

    public function _initialize(){

        parent::_initialize();
        $auth = $this->auth;

        if (!Config::get('fastadmin.usercenter')) {
            $this->error(__('User center already closed'));
        }

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
        Hook::add('user_delete_successed', function ($user) use ($auth) {
            Cookie::delete('uid');
            Cookie::delete('token');
        });
        Hook::add('user_logout_successed', function ($user) use ($auth) {
            Cookie::delete('uid');
            Cookie::delete('token');
        });
    }

    /**
     * 用户注册协议
     */
    public function user_agreement(){
        $site = \think\Config::get('site');
        $content = $site['user_agreement'];
        $this->view->assign('content', $content);
        $this->view->assign('title', '用户注册协议');
        return $this->view->fetch();
    }

    /**
     * 注册会员
     */
    public function register(){

        $url = $this->request->request('url', '');
        if ($this->auth->id) {
            $this->success(__('You\'ve logged in, do not login again'), $url ? $url : url('user/index'));
        }
        if ($this->request->isPost()) {
            //Request 参数处理
            $param=$this->postMore([
                ['mobile',''],
                ['captcha',''],
                ['password',''],
                ['repassword',''],
            ]);

            //判断手机验证码
            if(empty($param['captcha'])) $this->error('验证码不能为空', null, ['token' => $this->request->token()]);
            $captchaResult = Sms::check($param['mobile'], $param['captcha'], 'register');
            if(!$captchaResult) $this->error('验证码不正确', null, ['token' => $this->request->token()]);

            //字段验证
            if(!validate('User')->scene("register")->check($param)){
                $this->error(__(validate('User')->getError()), null, ['token' => $this->request->token()]);
            }

            //注册操作
            if ($this->auth->register($param)) {
                $this->success(__('Sign up successful'), $url ? $url : url('user/login'));
            } else {
                $this->error($this->auth->getError(), null, ['token' => $this->request->token()]);
            }
        }
        //判断来源
        $referer = $this->request->server('HTTP_REFERER');
        if (!$url && (strtolower(parse_url($referer, PHP_URL_HOST)) == strtolower($this->request->host()))
            && !preg_match("/(user\/login|user\/register|user\/logout)/i", $referer)) {
            $url = $referer;
        }
        $this->view->assign('url', $url);
        $this->view->assign('title', __('Register'));
        return $this->view->fetch();
    }

    /**
     * 会员登录
     */
    public function login(){

        $url = $this->request->request('url', '');
        if ($this->auth->id) {
            $this->success(__('You\'ve logged in, do not login again'), $url ? $url : url('user/index'));
        }
        if ($this->request->isPost()) {
            //Request 参数处理
            $param=$this->postMore([
                ['mobile',''],
                ['password',''],
            ]);

            if ($this->auth->login($param['mobile'],$param['password'])) {
                $this->success(__('Logged in successful'), $url ? $url : url('index/index'));
            } else {
                $this->error($this->auth->getError(), null, ['token' => $this->request->token()]);
            }
        }
        //判断来源
        $referer = $this->request->server('HTTP_REFERER');
        if (!$url && (strtolower(parse_url($referer, PHP_URL_HOST)) == strtolower($this->request->host()))
            && !preg_match("/(user\/login|user\/register|user\/logout)/i", $referer)) {
            $url = $referer;
        }
        $this->view->assign('url', $url);
        $this->view->assign('title', __('Login'));
        return $this->view->fetch();
    }

    /**
     * 忘记密码
     */
    public function forgetpwd(){

        if ($this->request->isPost()) {
            //Request 参数处理
            $param=$this->postMore([
                ['mobile',''],
                ['captcha',''],
                ['password',''],
                ['repassword',''],
            ]);

            //验证
            $user = UserModel::getByMobile($param['mobile']);
            if (!$user) {
                $this->error('手机号不存在');
            }
            $ret = Sms::check($param['mobile'], $param['captcha'], 'resetpwd');
            if (!$ret) {
                $this->error(__('Captcha is incorrect'));
            }
            //字段验证
            if(!validate('User')->scene('resetpwd')->check($param)){
                $this->error(__(validate('User')->getError()));
            }

            //更新密码
            $salt = Random::alnum();
            $newpassword = $this->auth->getEncryptPassword($param['password'], $salt);
            if (UserModel::where('mobile',$param['mobile'])->update(['loginfailure' => 0, 'password' => $newpassword, 'salt' => $salt])) {
                Sms::flush($param['mobile'], 'resetpwd');
                $this->success(__('Reset password successful'),url('user/login'));
            } else {
                $this->error($this->auth->getError());
            }
        }

        $this->view->assign('title', '忘记密码');
        return $this->view->fetch();
    }

    /**
     * 我的
     */
    public function index(){
        //获取用户作品浏览记录
        $works_browse = Db::name('works_browse')->alias('b')
                        ->join('__HALL_WORKS__ w','b.works_id = w.id','inner')
                        ->field('b.works_id,w.name,w.image')
                        ->where('b.user_id',$this->auth->id)
                        ->group('b.works_id')
                        ->order('b.id desc')
                        ->select();
       
        if($works_browse){
            foreach ($works_browse as $key => &$value) {
                $value['image'] = cdnurl($value['image']);
            }
        }
        $this->view->assign('works_browse', $works_browse);               
        $this->view->assign('title', __('User center'));
        return $this->view->fetch();
    }

    /**
     * 作品详情
     */
    public function works_info($works_id=''){
        if(empty($works_id)) $this->error('作品已删除');
        $works_info = Db::name('hall_works')->where('id',$works_id)->field('hall_id,name,image,author,species,material')->find();
        if(!$works_info) $this->error('作品已删除');
        $works_info['image'] = cdnurl($works_info['image']);
        $works_info['inquiry'] = Db::name('works_inquiry')->where('works_id',$works_id)->count();
        $works_info['hall_name'] = Db::name('hall')->where('id',$works_info['hall_id'])->value('name');

        $this->view->assign('works_info', $works_info);
        $this->view->assign('title', '作品详情');
        return $this->view->fetch();
    }

    /**
     * 修改密码
     */
    public function changepwd(){

        if ($this->request->isPost()) {
            //Request 参数处理
            $param=$this->postMore([
                ['mobile',''],
                ['captcha',''],
                ['password',''],
                ['repassword',''],
            ]);
            //验证
            $mobile =  UserModel::where('id',$this->auth->id)->value('mobile');
            if (!$mobile && $mobile!=$param['mobile']) {
                $this->error('输入手机号与当前登录手机号不符');
            }
            $ret = Sms::check($param['mobile'], $param['captcha'], 'changepwd');
            if (!$ret) {
                $this->error(__('Captcha is incorrect'));
            }
            //字段验证
            if(!validate('User')->scene('resetpwd')->check($param)){
                $this->error(__(validate('User')->getError()));
            }

            //更新密码
            Db::startTrans();
            try {
                $salt = Random::alnum();
                $newpassword = $this->auth->getEncryptPassword($param['password'], $salt);
                UserModel::where('mobile',$param['mobile'])->update(['loginfailure' => 0, 'password' => $newpassword, 'salt' => $salt]);
                $token = $this->auth->getToken();
                \app\common\library\Token::delete($token);
                //修改密码成功的事件
                Hook::listen("user_changepwd_successed", $this->_user);
                Db::commit();
            } catch (Exception $e) {
                Db::rollback();
                $this->error($e->getMessage());
            }
            $this->success(__('Reset password successful'),url('user/login'));
        }
        $this->view->assign('title', __('Change password'));
        return $this->view->fetch();
    }

    /**
     * 注销登录
     */
    public function logout(){

        //注销本站
        $this->auth->logout();
        $this->success(__('Logout successful'), url('index/index'));
    }

    /**
     * 设置
     */
    public function setting(){
        $this->view->assign('title', __('User center'));
        return $this->view->fetch();
    }

    /**
     * 修改昵称
     * @author phpstorm
     * @date 2020/2/15
     */
    public function changeinfo($nickname='')
    {
        if (!$nickname) {
            $this->error('昵称不能为空');
        }
        $user = $this->auth->getUser();
        $user->nickname = $nickname;
        $user->save();
        $this->success('修改成功');
    }

    /**
     * 修改头像
     * @author phpstorm
     * @date 2020/2/19
     */
    public function changeAvatar($avatar='')
    {
        if (!$avatar) {
            $this->error('头像不能为空');
        }
        $user = $this->auth->getUser();
        $user->avatar = $avatar;
        $user->save();
        $this->success('修改成功');
    }

    /**
     * 绑定手机号
     */
    public function bind_mobile(){

        if ($this->request->isPost()) {
            //Request 参数处理
            $param=$this->postMore([
                ['username',''],
                ['mobile',''],
                ['captcha',''],
            ]);
            //验证
            $check = UserModel::getByMobile($param['mobile']);
            if ($check) {
                $this->error(__('已被占用'));
            }
            $ret = Sms::check($param['mobile'], $param['captcha'], 'bindmobile');
            if (!$ret) {
                $this->error(__('Captcha is incorrect'));
            }
            
            $res = UserModel::where('id',$this->auth->id)->update(['username'=>$param['username'], 'mobile' => $param['mobile']]);
            if($res){
                $this->success("绑定成功",url('user/setting'));
            }else{
                $this->error('网络有误请稍后再试');
            }
        }
        $this->view->assign('title', "绑定手机号");
        return $this->view->fetch();
    }

    /**
     * 申请开展馆
     */
    public function apply_hall(){

        //验证是否提交过申请
        $is_have =  ApplyModel::where('user_id',$this->auth->id)->count();
        if ($is_have) {
            $this->error('已申请，请等待平台管理员线下联系');
        }

        if ($this->request->isPost()) {
            //Request 参数处理
            $param=$this->postMore([
                ['name',''],
                ['mobile',''],
                ['address',''],
            ]);
            
            $res = ApplyModel::addData($this->auth->id,$param['name'],$param['mobile'],$param['address']);
            if($res){
                $this->success("提交成功，请等待平台管理员线下联系",url('user/setting'));
            }else{
                $this->error('网络有误请稍后再试');
            }
        }
        $this->view->assign('title', "申请开展馆");
        return $this->view->fetch();
    }
}
