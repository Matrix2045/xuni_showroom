<?php

namespace app\index\validate;

use think\Validate;

class User extends Validate
{
    /**
     * 验证规则
     */
    protected $rule = [
        'mobile'      => 'require|regex:/^1\d{10}$/',
        'password'    => 'require|length:6,30',
        'repassword'  => 'require|confirm:password',
    ];

    /**
     * 提示消息
     */
    protected $message = [
        'mobile.require'      => '手机号不能为空',
        'mobile'              => '手机格式不正确',
        'password.require'    => '密码不能为空',
        'password.length'     => '密码必须6-30个字符',
        'repassword.require'  => '确认密码不能为空',
        'repassword.confirm'  => '两次密码不一致',
    ];

    /**
     * 字段描述
     */
    protected $field = [
    ];

    /**
     * 验证场景
     */
    protected $scene = [
        'register'    => ['mobile', 'password', 'repassword'],
        'resetpwd'    => ['password', 'repassword'],
        'profile'     => ['nickname', 'username', 'qq', 'wx'],
        'editPid'     => ['remark_name', 'pid'],
    ];
    
}
