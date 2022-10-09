<?php

namespace app\admin\model;

use think\Model;


class Hall extends Model
{

    

    

    // 表名
    protected $name = 'hall';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = false;
    protected $deleteTime = false;

    // 追加属性
    protected $append = [
        'exhibition_start_time_text',
        'exhibition_end_time_text',
        'is_show_text',
        'is_recommend_text'
    ];
    

    protected static function init()
    {
        self::afterInsert(function ($row) {
            $count = db('hall_type')->where('id',$row['hall_type'])->value('number');
            $array = [];
            for ($i=1;$i<=$count;$i++){
                $array[] = [
                    'hall_id' => $row['id'],
                    'type' => 'two',
                    'weigh' => $i,
                    'cratetime' => time(),
                ];
            }

            db('hall_works')->insertAll($array);
        });
    }

    
    public function getIsShowList()
    {
        return ['yes' => __('Is_show yes'), 'no' => __('Is_show no')];
    }

    public function getIsRecommendList()
    {
        return ['yes' => __('Is_recommend yes'), 'no' => __('Is_recommend no')];
    }


    public function getExhibitionStartTimeTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['exhibition_start_time']) ? $data['exhibition_start_time'] : '');
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }


    public function getExhibitionEndTimeTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['exhibition_end_time']) ? $data['exhibition_end_time'] : '');
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }


    public function getIsShowTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['is_show']) ? $data['is_show'] : '');
        $list = $this->getIsShowList();
        return isset($list[$value]) ? $list[$value] : '';
    }


    public function getIsRecommendTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['is_recommend']) ? $data['is_recommend'] : '');
        $list = $this->getIsRecommendList();
        return isset($list[$value]) ? $list[$value] : '';
    }

    protected function setExhibitionStartTimeAttr($value)
    {
        return $value === '' ? null : ($value && !is_numeric($value) ? strtotime($value) : $value);
    }

    protected function setExhibitionEndTimeAttr($value)
    {
        return $value === '' ? null : ($value && !is_numeric($value) ? strtotime($value) : $value);
    }


    public function admin()
    {
        return $this->belongsTo('Admin', 'admin_id', 'id', [], 'LEFT')->setEagerlyType(0);
    }

    public function HallType()
    {
        return $this->belongsTo('HallType', 'hall_type', 'id', [], 'LEFT')->setEagerlyType(0);
    }
}
