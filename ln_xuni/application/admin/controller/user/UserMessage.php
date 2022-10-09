<?php

namespace app\admin\controller\user;

use app\common\controller\Backend;
use think\Db;

/**
 * 站内信
 *
 * @icon fa fa-circle-o
 */
class UserMessage extends Backend
{
    
    /**
     * UserMessage模型对象
     * @var \app\admin\model\user\UserMessage
     */
    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\user\UserMessage;
        $this->admin = new \app\admin\model\Admin;
        $this->user = new \app\admin\model\User;
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
            $total = $this->model
                ->where($where)
                ->order($sort, $order)
                ->count();

            $list = $this->model
                ->where($where)
                ->order($sort, $order)
                ->limit($offset, $limit)
                ->select();

            $list = collection($list)->toArray();
            if($list){
                foreach ($list as $key => &$value) {
                    if($value['type']==0){
                        $value['send_user_id'] = ($send_user_id=$this->admin->where('id',$value['send_user_id'])->value('username'))?'管理员：'.$send_user_id:"--";
                        $value['user_id'] = '全体用户';
                    }else{
                        $value['send_user_id'] = ($send_user_id=$this->user->where('id',$value['send_user_id'])->value('username'))?'用户：'.$send_user_id:"--";
                        $value['user_id'] = ($user_id=$this->user->where('id',$value['user_id'])->value('username'))?'用户：'.$user_id:"--";
                    }
                }
            }
            $result = array("total" => $total, "rows" => $list);

            return json($result);
        }
        return $this->view->fetch();
    }

}
