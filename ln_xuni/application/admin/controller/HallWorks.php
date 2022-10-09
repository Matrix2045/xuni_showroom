<?php

namespace app\admin\controller;

use app\common\controller\Backend;
use think\Db;

/**
 * 展厅作品管理
 *
 * @icon fa fa-circle-o
 */
class HallWorks extends Backend
{
    
    /**
     * HallWorks模型对象
     * @var \app\admin\model\HallWorks
     */
    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\HallWorks;
        $this->view->assign("typeList", $this->model->getTypeList());
        $this->view->assign("isCoverList", $this->model->getIsCoverList());
        $this->view->assign('hall_id',input('hall_id',0));
    }
    
    /**
     * 默认生成的控制器所继承的父类中有index/add/edit/del/multi五个基础方法、destroy/restore/recyclebin三个回收站方法
     * 因此在当前控制器中可不用编写增删改查的代码,除非需要自己控制这部分逻辑
     * 需要将application/admin/library/traits/Backend.php中对应的方法复制到当前控制器,然后进行修改
     */

    /**
     * 查看
     */
    public function index()
    {
        //当前是否为关联查询
        $this->relationSearch = true;
        //设置过滤方法
        $ids = input('ids');
        $this->request->filter(['strip_tags']);
        if ($this->request->isAjax()) {
            //如果发送的来源是Selectpage，则转发到Selectpage
            if ($this->request->request('keyField')) {
                return $this->selectpage();
            }
            list($where, $sort, $order, $offset, $limit) = $this->buildparams();
            $total = $this->model
                ->with(['hall'])
                ->where($where)
                ->where(['hall_id'=>$ids])
                ->order($sort, $order)
                ->count();

            $list = $this->model
                ->with(['hall'])
                ->where($where)
                ->where(['hall_id'=>$ids])
                ->order($sort, $order)
                ->limit($offset, $limit)
                ->select();

            $list = collection($list)->toArray();
            $result = array("total" => $total, "rows" => $list);

            return json($result);
        }
        
        $totaladd = $this->model->with(['hall'])->where(['hall_id'=>$ids])->count();
        $this->assign('totaladd',$totaladd);
        $this->assign('ids',$ids);
        return $this->view->fetch();
    }

    /**
     * 添加
     */
    public function add()
    {
        if ($this->request->isPost()) {

            $params = $this->request->post("row/a");
            if ($params) {

                $params = $this->preExcludeFields($params);

                //已传作品
                $total = $this->model
                        ->with(['hall'])
                        ->where(['hall_id'=>$params['hall_id']])
                        ->count();

                //总作品数量
                $totalcount = ($count = Db::name('hall')->alias('h')
                            ->where(['h.id'=>$params['hall_id']])
                            ->join('__HALL_TYPE__ t','h.hall_type = t.id','inner')
                            ->value('t.number'))?$count : 0;
                if($total>=$totalcount) $this->error("展厅最多上传".$totalcount."个作品");

                if ($this->dataLimit && $this->dataLimitFieldAutoFill) {
                    $params[$this->dataLimitField] = $this->auth->id;
                }
                $result = false;
                Db::startTrans();
                try {
                    //是否采用模型验证
                    if ($this->modelValidate) {
                        $name = str_replace("\\model\\", "\\validate\\", get_class($this->model));
                        $validate = is_bool($this->modelValidate) ? ($this->modelSceneValidate ? $name . '.add' : $name) : $this->modelValidate;
                        $this->model->validateFailException(true)->validate($validate);
                    }

                    $result = $this->model->allowField(true)->save($params);
                    Db::commit();
                } catch (ValidateException $e) {
                    Db::rollback();
                    $this->error($e->getMessage());
                } catch (PDOException $e) {
                    Db::rollback();
                    $this->error($e->getMessage());
                } catch (Exception $e) {
                    Db::rollback();
                    $this->error($e->getMessage());
                }
                if ($result !== false) {
                    $this->success();
                } else {
                    $this->error(__('No rows were inserted'));
                }
            }
            $this->error(__('Parameter %s can not be empty', ''));
        }
        return $this->view->fetch();
    }
}
