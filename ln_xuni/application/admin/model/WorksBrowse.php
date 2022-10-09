<?php

namespace app\admin\model;

use think\Model;


class WorksBrowse extends Model
{
    // 表名
    protected $name = 'works_browse';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = false;

    // 定义时间戳字段名
    protected $createTime = false;
    protected $updateTime = false;
    protected $deleteTime = false;

    // 追加属性
    protected $append = [
        'browse_time_text',
        'add_time_text'
    ];
    
    public function user()
    {
        return $this->belongsTo('User', 'user_id', 'id', [], 'inner')->setEagerlyType(0);
    }

    public function HallWorks()
    {
        return $this->belongsTo('HallWorks', 'works_id', 'id', [], 'inner')->setEagerlyType(0);
    }
    
    public function getBrowseTimeTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['browse_time']) ? $data['browse_time'] : '');
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }


    public function getAddTimeTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['add_time']) ? $data['add_time'] : '');
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }

    protected function setBrowseTimeAttr($value)
    {
        return $value === '' ? null : ($value && !is_numeric($value) ? strtotime($value) : $value);
    }

    protected function setAddTimeAttr($value)
    {
        return $value === '' ? null : ($value && !is_numeric($value) ? strtotime($value) : $value);
    }


}
