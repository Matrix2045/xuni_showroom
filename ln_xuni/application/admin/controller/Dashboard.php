<?php

namespace app\admin\controller;

use app\common\controller\Backend;
use think\Config;
use think\Db;

/**
 * 驾驶舱
 *
 * @icon fa fa-dashboard
 * @remark 用于展示当前系统中的统计数据、统计报表及重要实时数据
 */
class Dashboard extends Backend
{

    /**
     * 查看
     */
    public function index()
    {
        //展厅数量
        $totalhall = Db::name('hall')->count();

        $this->view->assign([
            'totalhall'        => $totalhall,
        ]);

        return $this->view->fetch();
    }

}
