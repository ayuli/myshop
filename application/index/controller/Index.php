<?php
namespace app\index\controller;

class Index extends Common
{
    /** 前台首页 */
    public function index()
    {
        //查询所有可以展示的分类信息
        $this->getIndexCateInfo();
        //获取楼层数据
        $cate_id =1;
        $info = $this->getFloorInfo($cate_id);
        $this->assign('info',$info);
        return view();
    }

    /**
     * 获取更多楼层数据
     */
    public function getMoreFloor()
    {
        $cate_id = input('post.cate_id');
        $floor_num = input('post.floor_num');
        $floor_num = $floor_num+1;
//        echo $cate_id;
        $cate = model('Cate');
        $where = [
            'cate_id'=>['>',$cate_id],
            'pid'=>0,
            'is_show'=>1
        ];
        $arr = $cate->field('cate_id')->where($where)->order('cate_id','asc')->find();
        $cate_id = $arr['cate_id'];
        if(!empty($cate_id)){
            $info = $this->getFloorInfo($cate_id);

            $this->view->engine->layout(false);
            $this->assign('info',$info);
            $this->assign('floor_num',$floor_num);
            echo $this->fetch('div');
        }


    }



}
