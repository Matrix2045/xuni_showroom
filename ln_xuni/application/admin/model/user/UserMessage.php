<?php

namespace app\admin\model\user;

use think\Model;
use app\admin\library\Auth;

class UserMessage extends Model
{
    // 表名
    protected $name = 'user_message';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = true;

    // 定义时间戳字段名
    protected $createTime = 'add_time';
    
    //发送时间处理
    public function getAddTimeTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['add_time']) ? $data['add_time'] : '');
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }

    protected static function init()
    {
        self::beforeInsert(function ($row) {
            $auth = Auth::instance();
            $row->send_user_id = $auth->isLogin() ? $auth->id : 0;
            $row->user_id = 0;
            $row->type = 0;
        });
    }
}
