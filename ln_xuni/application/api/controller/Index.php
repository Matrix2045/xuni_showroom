<?php

namespace app\api\controller;

use app\common\controller\Api;
use app\common\library\Token;
use think\Db;
use app\common\model\User as UserModel;
use app\common\library\Sms;

/**
 * 首页接口
 */
class Index extends Api
{
    protected $noNeedLogin = ['getHallWork','getHallInfo','getHallWorkInfo','getLike'];
    protected $noNeedRight = ['*'];

    /**
     * 获取展厅基本信息
     */
    public function getHallInfo()
    {
        //获取展厅id
        $hall_id = input('hall_id','');
        if(empty($hall_id)) $this->apiResult(V('0','缺少参数'));

        //获取所属展厅主图
        $data = Db::name('hall')
            ->field('id as hall_id, name, hall_type, image, width, height, curator, organizer,bg_music')
            ->where(['id'=>$hall_id,'is_show'=>'yes'])
            ->find();
        if(!$data) $this->apiResult(V('0','展厅已关闭'));
        $data['image'] = cdnurl($data['image']).'?v='.time();

        //背景音乐
        $data['bg_music'] = !empty($data['bg_music'])?cdnurl($data['bg_music']):'';

        $this->apiResult(V('1','获取成功',$data));
    }

    /**
     * 获取展厅所有作品
     */
    public function getHallWork()
    {
        //获取展厅id
        $hall_id = input('hall_id','');
        if(empty($hall_id)) $this->apiResult(V('0','缺少参数'));
        //获取所属展厅主图
        $is_have = Db::name('hall')->where(['id'=>$hall_id,'is_show'=>'yes'])->count();
        if(!$is_have) $this->apiResult(V('0','展厅已关闭'));

        //总作品数量
        $totalcount = ($count = Db::name('hall')->alias('h')
                    ->where(['h.id'=>$hall_id])
                    ->join('__HALL_TYPE__ t','h.hall_type = t.id','inner')
                    ->value('t.number'))?$count : 0;

        //获取所属展厅作品
        $data = Db::name('hall_works')
            ->field('id as work_id,image,type,width,height')
            ->where(['hall_id'=>$hall_id])
            ->order('weigh ase,id desc')
            ->limit(0,$totalcount)
            ->select();
        $list = [];
        if($data){
            foreach ($data as $value){
                if(!empty($value['image'])){
                    $value['image'] = cdnurl($value['image']).'?v='.time();
                }else{
                    $value['image'] = '';
                }

                $list[]=$value;
            }
        }
        $this->apiResult(V('1','获取成功',$list));
    }

    /**
     * 获取展厅作品基本信息
     */
    public function getHallWorkInfo()
    {
        //获取作品id
        $work_id = input('work_id','');
        if(empty($work_id)) $this->apiResult(V('0','缺少参数'));

        //获取作品详情
        $data = Db::name('hall_works')
            ->field('id as work_id, name, image, author, authorlogo, creativetime, width, height, music,video,desc')
            ->where(['id'=>$work_id])
            ->find();
        if(!$data || empty($data['image'])) $this->apiResult(V('0','作品可能已删除'));
        $data['image'] = !empty($data['image'])?cdnurl($data['image']).'?v='.time():'';
        $data['authorlogo'] = !empty($data['authorlogo'])?cdnurl($data['authorlogo']).'?v='.time():'';
        $data['music'] = !empty($data['music'])?cdnurl($data['music']).'?v='.time():'';
        $data['video'] = !empty($data['video'])?cdnurl($data['video']).'?v='.time():'';
        $data['creativetime'] = !empty($data['creativetime'])?date('Y-m-d',$data['creativetime']):'';

        $this->apiResult(V('1','获取成功',$data));
    }

    /**
     * 作品询价
     */
    public function worksInquiry()
    {
        //获取作品id
        $work_id = input('work_id','');
        if(empty($work_id)) $this->apiResult(V('0','缺少参数'));
        $is_have = Db::name('hall_works')->where(['id'=>$work_id])->count();
        if(!$is_have) $this->apiResult(V('0','作品可能已删除'));

        //判断是否有手机号
        $is_bind =Db::name('user')->where(['id'=>$this->auth->id])->value('mobile');
        if(!$is_bind) $this->apiResult(V('102','请先绑定手机号'));

        //查询是否询价过
        $info = Db::name('works_inquiry')->where(['user_id'=>$this->auth->id,'works_id'=>$work_id])->find();
        if($info){
            Db::name('works_inquiry')->where('id',$info['id'])->update(['add_time'=>time()]);
        }else{
            $array = [
                'user_id' => $this->auth->id,
                'works_id' => $work_id,
                'add_time' => time(),
            ];
            Db::name('works_inquiry')->insert($array);
        }

        $this->apiResult(V('1','询价成功'));
    }

    /**
     * 作品浏览记录添加
     */
    public function worksBrowse()
    {
        //获取参数
        $work_id = input('work_id','');
        $browse_time = input('browse_time',0);
        if(empty($work_id)) $this->apiResult(V('0','缺少参数'));
        if($browse_time<=10) $this->apiResult(V('0','浏览时间需要大于10秒'));
        $is_have = Db::name('hall_works')->where(['id'=>$work_id])->count();
        if(!$is_have) $this->apiResult(V('0','作品可能已删除'));

        if($browse_time>0){
            $array = [
                'user_id' => $this->auth->id,
                'works_id' => $work_id,
                'browse_time' => $browse_time,
                'add_time' => time(),
            ];
            Db::name('works_browse')->insert($array);
        }

        $this->apiResult(V('1','操作成功'));
    }

    /**
     * 作品点赞
     */
    public function worksLike()
    {
        //获取作品id
        $work_id = input('work_id','');
        if(empty($work_id)) $this->apiResult(V('0','缺少参数'));
        $is_have = Db::name('hall_works')->where(['id'=>$work_id])->count();
        if(!$is_have) $this->apiResult(V('0','作品可能已删除'));

        //查询是否点赞过
        $count = Db::name('works_like')->where(['user_id'=>$this->auth->id,'works_id'=>$work_id])->count();
        if($count>0){
            $this->apiResult(V('0','已点过赞'));
        }else{
            Db::startTrans();
            try {
                //添加点赞记录
                $array = [
                    'user_id' => $this->auth->id,
                    'works_id' => $work_id,
                    'add_time' => time(),
                ];
                $works_like = Db::name('works_like')->insert($array);
                if(!$works_like){
                    throw new Exception();
                }

                //作品添加点赞数
                Db::name('hall_works')->where(['id'=>$work_id])->setInc('like_num');

                Db::commit();
            } catch (Exception $e) {
                Db::rollback();
                $this->apiResult(V('0','网络有误稍后再试'));
            }
        }
        $this->apiResult(V('1','点赞成功'));
    }

    /**
     * 绑定手机号
     */
    public function bind_mobile(){

        //Request 参数处理
        $username = input('username','');
        $mobile = input('mobile','');
        $captcha = input('captcha','');

        if(empty($mobile)) $this->apiResult(V('0','手机号不能为空'));
        if (!\think\Validate::regex($mobile, "^1\d{10}$")) $this->apiResult(V('0','手机号不正确')); 
        if(empty($captcha)) $this->apiResult(V('0','验证码不能为空'));

        //验证
        $check = UserModel::getByMobile($mobile);
        if ($check) {
            $this->apiResult(V('0','已被占用'));
        }
        $ret = Sms::check($mobile, $captcha, 'bindmobile');
        if (!$ret) {
            $this->apiResult(V('0','验证码不正确'));
        }
        
        $res = UserModel::where('id',$this->auth->id)->update(['username'=>$username, 'mobile' => $mobile]);
        if($res){
            Sms::flush($mobile, 'bindmobile');
            $this->apiResult(V('1','绑定成功'));
        }else{
            $this->apiResult(V('0','网络有误请稍后再试'));
        }
    }

    /**
     * 获取点赞开启状态
     */
    public function getLike(){
        $hall_id = input('hall_id','');

        //点赞开启状态
        $open_like = Db::name('hall')->where(['id'=>$hall_id])->value('open_like');
        $data['open_like'] = $open_like;

        $this->apiResult(V('1','点赞开启',$data));
    }

    /**
     * 作品点赞限制
     */
    public function worksLike_2()
    {
        $site = \think\Config::get('site');

        //获取作品id
        $work_id = input('work_id','');
        if(empty($work_id)) $this->apiResult(V('0','缺少参数'));
        $is_have = Db::name('hall_works')->where(['id'=>$work_id])->count();
        if(!$is_have) $this->apiResult(V('0','作品可能已删除'));

        $open_like = Db::name('hall')->alias('a')
            ->join('__HALL_WORKS__ b','a.id=b.hall_id','inner')
            ->where(['b.id'=>$work_id])
            ->value('open_like');
        if(!$open_like) $this->apiResult(V('0','点赞活动暂时关闭'));

        //查询是否点赞过
        $info = Db::name('works_like')->where(['user_id'=>$this->auth->id,'works_id'=>$work_id])->order('id desc')->find();
        //符合条件处理
        $array = [
            'num' => 1,
            'start_id' => 0,
        ];
        if($info && $site['day_num']==$info['day_num'] && $site['like_num']==$info['like_num']){
            //判断当前是否是第一条点赞数据
            if($info['start_id']==0){
                $add_time = $info['add_time'];
                $start_id = $info['id'];
            }else{
                $add_time = Db::name('works_like')->where('id',$info['start_id'])->value('add_time');
                $start_id = $info['start_id'];
            }

            $startdate=strtotime(date('Y-m-d',$add_time));
            $enddate=strtotime(date('Y-m-d',time()));
            $days=round(($enddate-$startdate)/3600/24) ;
            //判断是否在时间范围内
            if($days<$info['day_num']){//在时间范围
                //判断次数是否满足了
                if($info['num']>= $info['like_num']){
                    $this->apiResult(V('0','已达到点赞上限'.$info['like_num'].'次'));
                }else{
                    $array = [
                        'num' => $info['num']+1,
                        'start_id' => $start_id,
                    ];
                }
            }
        }
        
        Db::startTrans();
        try {
            //添加点赞记录
            $array['user_id'] = $this->auth->id;
            $array['works_id'] = $work_id;
            $array['like_num'] = $site['like_num'];
            $array['day_num'] = $site['day_num'];
            $array['add_time'] = time();
            $works_like = Db::name('works_like')->insert($array);
            if(!$works_like){
                throw new Exception();
            }

            //作品添加点赞数
            Db::name('hall_works')->where(['id'=>$work_id])->setInc('like_num');

            Db::commit();
        } catch (Exception $e) {
            Db::rollback();
            $this->apiResult(V('0','网络有误稍后再试'));
        }
        $this->apiResult(V('1','点赞成功'));
    }
}
