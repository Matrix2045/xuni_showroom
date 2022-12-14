<?php

namespace app\admin\controller;

use app\common\controller\Backend;
use think\Db;
use think\exception\PDOException;
use think\exception\ValidateException;

/**
 * 热门搜索记录
 *
 * @icon fa fa-circle-o
 */
class HotSearch extends Backend
{
    
    /**
     * HotSearch模型对象
     * @var \app\admin\model\HotSearch
     */
    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\HotSearch;

    }
    
    /**
     * 默认生成的控制器所继承的父类中有index/add/edit/del/multi五个基础方法、destroy/restore/recyclebin三个回收站方法
     * 因此在当前控制器中可不用编写增删改查的代码,除非需要自己控制这部分逻辑
     * 需要将application/admin/library/traits/Backend.php中对应的方法复制到当前控制器,然后进行修改
     */

    /**
     * 添加
     */
    public function add()
    {
        if ($this->request->isPost()) {
            $params = $this->request->post("row/a");
            if ($params) {
                $params = $this->preExcludeFields($params);
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

                    $keyword=array_unique(array_filter($params['keyword']));//去空去重
                    if(!$keyword){
                        $this->error('关键词不能为空');
                    }
                    $array=[];
                    foreach ($keyword as $key => &$value) {//key值
                        if($this->model->where(['keyword'=>$value])->find()){
                            $this->error('“'.$value.'” 已存在，不能重复添加');
                        }
                        $array[]=array(
                            "keyword"=>$value,
                            "sort"=>$params['sort'][$key],
                            "add_time"=>time(),
                        );
                    }
                    $result = $this->model->allowField(true)->saveAll($array);
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
