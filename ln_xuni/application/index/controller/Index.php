<?php

namespace app\index\controller;

use app\common\controller\Frontend;
use think\Db;

class Index extends Frontend
{

    protected $noNeedLogin = '*';
    protected $noNeedRight = '*';
    protected $layout = 'default';

    /**
     * 首页
     */
    public function index()
    {
        //获取banner图
        $banner = Db::name('banner')->order('id desc')->select();
        if($banner){
            foreach ($banner as $key=>&$val) {
                $val['image'] = cdnurl($val['image']);
                if($val['type']=='one'){
                    $val['url'] = 'http://xuni.pro4.liuniukeji.net/hallshow/index.html?token='.$this->auth->getToken().'&hall_id='.$val['link_id'];
                }else{
                    $val['url'] = $val['link_url'];
                }
            }
        }

        //获取精品推荐
        $recommend = Db::name('hall')
                    ->field('id as hall_id, name, image, hall_type')
                    ->where(['is_recommend'=>'yes','is_show'=>'yes'])
                    ->order('id desc')
                    ->limit(0,8)
                    ->select();
        if($recommend){
            foreach ($recommend as $key => &$value) {
                $value['image'] = cdnurl($value['image']);
                $value['sence'] = ($sence = Db::name('hall_type')->where('id',$value['hall_type'])->value('type'))?$sence:1;
            }
        }

        //获取所有展厅
        $hall = Db::name('hall')
            ->field('id as hall_id, name, image, hall_type')
            ->where(['is_show'=>'yes'])
            ->order('id desc')
            ->select();
        if($hall){
            foreach ($hall as $key => &$value) {
                $value['image'] = cdnurl($value['image']);
                $value['sence'] = ($sence = Db::name('hall_type')->where('id',$value['hall_type'])->value('type'))?$sence:1;
            }
        }

        $url = 'http://xuni.pro4.liuniukeji.net/hallshow/index.html?token='.$this->auth->getToken().'&hall_id=';
        $this->view->assign('url',$url);
        $this->view->assign('banner',$banner);
        $this->view->assign('recommend',$recommend);
        $this->view->assign('hall',$hall);
        $this->view->assign('title', '首页');
        return $this->view->fetch();
    }

    /**
     * 搜索
     */
    public function search()
    {   
        $keyword = input('get.keyword','');
        
        //搜索
        $list = [];
        $type = 1;
        if(!empty($keyword)){
            $list = Db::name('hall')->field('id as hall_id, name, image, hall_type')->where(['is_show'=>'yes','name'=>['like','%'.$keyword.'%']])->order('id desc')->select();
            if($list){
                foreach($list as &$val){
                    $val['image'] = cdnurl($val['image']);
                    $val['sence'] = ($sence = Db::name('hall_type')->where('id',$val['hall_type'])->value('type'))?$sence:1;
                }
            }
            $type = 2;
        }

        //获取热门搜索
        $hot_search = Db::name('hot_search')->order('sort ,add_time desc,id desc')->column('keyword');
        
        $url = 'http://xuni.pro4.liuniukeji.net/hallshow/index.html?token='.$this->auth->getToken().'&hall_id=';
        $this->view->assign('url',$url);
        $this->view->assign('keyword', $keyword);
        $this->view->assign('hot_search', $hot_search);
        $this->view->assign('list', $list);
        $this->view->assign('type', $type);
        $this->view->assign('title', '搜索');
        return $this->view->fetch();
    }
}
