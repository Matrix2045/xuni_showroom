<?php

namespace app\admin\model;

use think\Model;


class HallWorks extends Model
{
    // 表名
    protected $name = 'hall_works';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = false;

    // 定义时间戳字段名
    protected $createTime = false;
    protected $updateTime = false;
    protected $deleteTime = false;

    // 追加属性
    protected $append = [
        'type_text',
        'is_cover_text',
        'cratetime_text',
        'creativetime_text'
    ];
    

    public function hall()
    {
        return $this->belongsTo('Hall', 'hall_id', 'id', [], 'LEFT')->setEagerlyType(0);
    }

    public function getTypeList()
    {
        return ['one' => __('Type one'), 'two' => __('Type two')];
    }

    public function getIsCoverList()
    {
        return ['yes' => __('Is_cover yes'), 'no' => __('Is_cover no')];
    }


    public function getTypeTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['type']) ? $data['type'] : '');
        $list = $this->getTypeList();
        return isset($list[$value]) ? $list[$value] : '';
    }


    public function getIsCoverTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['is_cover']) ? $data['is_cover'] : '');
        $list = $this->getIsCoverList();
        return isset($list[$value]) ? $list[$value] : '';
    }


    public function getCratetimeTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['cratetime']) ? $data['cratetime'] : '');
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }

    protected function setCratetimeAttr($value)
    {
        return $value === '' ? null : ($value && !is_numeric($value) ? strtotime($value) : $value);
    }

    public function getCreativetimeTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['creativetime']) ? $data['creativetime'] : '');
        return is_numeric($value) ? date("Y-m-d", $value) : $value;
    }

    protected function setCreativetimeAttr($value)
    {
        return $value === '' ? null : ($value && !is_numeric($value) ? strtotime($value) : $value);
    }

}
