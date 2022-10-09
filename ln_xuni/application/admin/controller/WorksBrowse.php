<?php

namespace app\admin\controller;

use app\common\controller\Backend;

/**
 * 作品浏览记录管理
 *
 * @icon fa fa-circle-o
 */
class WorksBrowse extends Backend
{
    
    /**
     * WorksBrowse模型对象
     * @var \app\admin\model\WorksBrowse
     */
    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\WorksBrowse;

    }
    
    /**
     * 查看
     */
    public function index()
    {
        //设置过滤方法
        $this->request->filter(['strip_tags']);
        if ($this->request->isAjax()) {
            //如果发送的来源是Selectpage，则转发到Selectpage
            if ($this->request->request('keyField')) {
                return $this->selectpage();
            }
            list($where, $sort, $order, $offset, $limit) = $this->buildparams();
            $total = $this->model->alias('b')
                ->join('__USER__ u','b.user_id = u.id','inner')
                ->join('__HALL_WORKS__ w','b.works_id = w.id','inner')
                ->join('__HALL__ h','w.hall_id = h.id','inner')
                ->where($where)
                ->order($sort, $order)
                ->count();

            $list = $this->model->alias('b')
                ->field('b.id,u.nickname as `u.nickname`,u.username,u.mobile,h.name as `h.name`,b.works_id,w.name as `w.name`,b.add_time,b.browse_time')
                ->join('__USER__ u','b.user_id = u.id','inner')
                ->join('__HALL_WORKS__ w','b.works_id = w.id','inner')
                ->join('__HALL__ h','w.hall_id = h.id','inner')
                ->where($where)
                ->order($sort, $order)
                ->limit($offset, $limit)
                ->select();
            $list = collection($list)->toArray();
            if($list){
            }
            $result = array("total" => $total, "rows" => $list);

            return json($result);
        }
        return $this->view->fetch();
    }
    
    /**
     * 默认生成的控制器所继承的父类中有index/add/edit/del/multi五个基础方法、destroy/restore/recyclebin三个回收站方法
     * 因此在当前控制器中可不用编写增删改查的代码,除非需要自己控制这部分逻辑
     * 需要将application/admin/library/traits/Backend.php中对应的方法复制到当前控制器,然后进行修改
     */

    /**
     * 详情
     */
    public function detail($ids)
    {
        $row = $this->model->alias('b')
                ->field('b.id,b.browse_time,b.add_time,b.user_id,u.nickname,u.username,u.mobile,h.name as hall_name,b.works_id,w.name as works_name,w.image,w.author,w.species,w.material,w.type,w.width,w.height')
                ->join('__USER__ u','b.user_id = u.id','inner')
                ->join('__HALL_WORKS__ w','b.works_id = w.id','inner')
                ->join('__HALL__ h','w.hall_id = h.id','inner')
                ->where(['b.id' => $ids])
                ->find();
        if (!$row)
            $this->error(__('No Results were found'));
        $this->view->assign("row", $row->toArray());
        return $this->view->fetch();
    }
}
