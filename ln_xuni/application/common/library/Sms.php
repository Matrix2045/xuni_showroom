<?php

namespace app\common\library;

use think\Hook;

/**
 * 短信验证码类
 */
class Sms
{

    /**
     * 验证码有效时长
     * @var int
     */
    protected static $expire = 300;

    /**
     * 最大允许检测的次数
     * @var int
     */
    protected static $maxCheckNums = 10;

    /**
     * 获取最后一次手机发送的数据
     *
     * @param   int    $mobile 手机号
     * @param   string $event  事件
     * @return  Sms
     */
    public static function get($mobile, $event = 'default')
    {
        $sms = \app\common\model\Sms::
        where(['mobile' => $mobile, 'event' => $event])
            ->order('id', 'DESC')
            ->find();
        Hook::listen('sms_get', $sms, null, true);
        return $sms ? $sms : null;
    }

    /**
     * 发送验证码
     *
     * @param   int    $mobile 手机号
     * @param   int    $code   验证码,为空时将自动生成4位数字
     * @param   string $event  事件
     * @return  boolean
     */
    public static function send($mobile, $code = null, $event = 'default')
    {
        $code = is_null($code) ? mt_rand(1000, 9999) : $code;
        $time = time();
        $ip = request()->ip();
        $sms = \app\common\model\Sms::create(['event' => $event, 'mobile' => $mobile, 'code' => $code, 'ip' => $ip, 'createtime' => $time]);
        $content = '短信验证码:'.$code.',10分钟有效!';
        $result = sendSms($mobile,$content);
        if ($result['code'] == 0) {
            $sms->delete();
            return false;
        }
        return true;
    }

    /**
     * 发送通知
     *
     * @param   mixed  $mobile   手机号,多个以,分隔
     * @param   string $msg      消息内容
     * @param   string $template 消息模板
     * @return  boolean
     */
    public static function notice($mobile, $msg = '', $template = null)
    {
        $params = [
            'mobile'   => $mobile,
            'msg'      => $msg,
            'template' => $template
        ];
        $result = Hook::listen('sms_notice', $params, null, true);
        return $result ? true : false;
    }

    /**
     * 校验验证码
     *
     * @param   int    $mobile 手机号
     * @param   int    $code   验证码
     * @param   string $event  事件
     * @return  boolean
     */
    public static function check($mobile, $code, $event = 'default')
    {
        $time = time() - self::$expire;
        $sms = \app\common\model\Sms::where(['mobile' => $mobile, 'event' => $event])
            ->order('id', 'DESC')
            ->find();
        if ($sms) {
            if ($sms['createtime'] > $time && $sms['times'] <= self::$maxCheckNums) {
                $correct = $code == $sms['code'];
                if (!$correct) {
                    $sms->times = $sms->times + 1;
                    $sms->save();
                    return false;
                } else {
                    return true;
                }
            } else {
                // 过期则清空该手机验证码
                self::flush($mobile, $event);
                return false;
            }
        } else {
            return false;
        }
    }

    /**
     * 清空指定手机号验证码
     *
     * @param   int    $mobile 手机号
     * @param   string $event  事件
     * @return  boolean
     */
    public static function flush($mobile, $event = 'default')
    {
        \app\common\model\Sms::
        where(['mobile' => $mobile, 'event' => $event])
            ->delete();
        Hook::listen('sms_flush');
        return true;
    }

    /**
     * 阿里大鱼
     * @param $data
     * @param string $method
     * @return array
     */
    public static function sendSms($data, $method = 'POST'){

        $time = date("YmdHis");
        $option = [
            'name' => self::encoding(config('sms.name'), config('sms.enCode')),
            'seed' => $time,
            'key' => md5(md5(config('sms.password')).$time),
            'dest' => '',
            'content' => '',
            'ext' => '',
            'reference' => ''
        ];

        $data = array_merge($option, $data);

        if (config('sms.enCode') == 'UTF-8'){
            $url = 'http://160.19.212.218:8080/eums/utf8/send_strong.do'; //UTF-8编码接口地址
        }else {
            $url = 'http://160.19.212.218:8080/eums/send_strong.do'; //GBK编码接口地址
        }
        $data['content'] = self::encoding($data['content'], config('sms.enCode'));

        if ($method=='POST'){
            $resp =  self::send_post_curl($url, $data);
        }else if ($method=='GET'){
            $resp =  self::send_get($url, $data);
        }
        $response = explode(':', $resp);
        if ($response[0]=='success'){
            return ['status' => '1', 'msg' => ''];
        }else {
            return ['status' => 0, 'msg' => '错误代码:'.$response[1]];
        }
    }
}
