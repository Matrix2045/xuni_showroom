<?php

namespace app\index\model;

use think\Model;
use think\Request;
use think\Validate;
use traits\ModelTrait;
use app\common\library\Sms;
use app\common\library\Auth;

/**
 * Description: 申请开展馆相关模型
 * Author: wanggz
 * @package app\index\model\ApplyHall
 */
class ApplyHall extends Model
{
    use ModelTrait;

    protected $insert = ['add_time'];

    protected function setAddTimeAttr()
    {
        return time();
    }

    /*
     * [addData 添加数据]
     * @return object
     * */
    public static function addData($user_id,$name,$mobile,$address)
    {
        return self::set(compact('user_id','name','mobile','address'));
    }
}