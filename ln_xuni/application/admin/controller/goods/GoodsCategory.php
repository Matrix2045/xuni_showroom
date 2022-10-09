<?php

namespace app\admin\controller\goods;

use app\common\controller\Backend;
use \Justmd5\PinDuoDuo\PinDuoDuo;
use think\Cache;

/**
 * 商品分类管理
 *
 * @icon fa fa-circle-o
 */
class GoodsCategory extends Backend
{
    
    /**
     * GoodsCategory模型对象
     * @var \app\admin\model\goods\GoodsCategory
     */
    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\goods\GoodsCategory;

    }

    /**
     * 对应多多进宝列表
     */
    public function selectcate()
    {
        //当前页
        $page = $this->request->request("pageNumber");
        //分页大小
        $pagesize = $this->request->request("pageSize");
        //主键值
        $primaryvalue = $this->request->request("keyValue");

        //获取缓存中是否存在
        $goods_cats_list = Cache::get("goods_cats_list");
        if (!is_array($goods_cats_list)) {
            //实例化获取goods_cats_list
            $pinduoduo = new PinDuoDuo();
            $goods_cats_get_response = $pinduoduo->api->request('pdd.goods.cats.get', ['parent_cat_id' => 0]);
            $goods_cats_list = $goods_cats_get_response['goods_cats_get_response']['goods_cats_list'];

            Cache::set("goods_cats_list", $goods_cats_list, 86400);
        }
        $list = [];
        $primaryvalue = array_filter(explode(',',$primaryvalue));
        foreach ($goods_cats_list as $key => $value) {
            if($primaryvalue){
                if (in_array($value['cat_id'],$primaryvalue)){
                    $array = [
                        'id' => $value['cat_id'],
                        'name' => $value['cat_name'],
                        'pid' => $value['parent_cat_id'],
                    ];
                    array_push($list, $array);
                }
            }else{
                $list[$key] = [
                    'id' => $value['cat_id'],
                    'name' => $value['cat_name'],
                    'pid' => $value['parent_cat_id'],
                ];
            }
        }
       
        $page=(empty($page))?'1':$page; #判断当前页面是否为空 如果为空就表示为第一页面
        $start=($page-1)*$pagesize; #计算每次分页的开始位置
        $totals=count($list);//总条数
        $pagedata=array_slice($list,$start,$pagesize);

        return json(['list' => $pagedata, 'total' => $totals]);
    }
    
    /**
     * 默认生成的控制器所继承的父类中有index/add/edit/del/multi五个基础方法、destroy/restore/recyclebin三个回收站方法
     * 因此在当前控制器中可不用编写增删改查的代码,除非需要自己控制这部分逻辑
     * 需要将application/admin/library/traits/Backend.php中对应的方法复制到当前控制器,然后进行修改
     */
    

}
