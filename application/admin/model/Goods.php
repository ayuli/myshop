<?php
namespace app\admin\model;
use think\Model;

class Goods extends Model
{
    protected $updateTime = false;

    public function getcliInfo($model,$page,$limit,$where)
    {
        $data = $model
            ->field('a.*,cate_name,brand_name')
            ->alias('a')
            ->join('shop_cate b','a.cate_id = b.cate_id')
            ->join('shop_brand c','a.brand_id = c.brand_id')
            ->where($where)
            ->page($page,$limit)
            ->select();
        foreach($data as $k=>$v){
            $data[$k]['is_sell'] = $v['is_sell']==1?'√':'×';
            $data[$k]['is_new'] = $v['is_new']==1?'√':'×';
            $data[$k]['is_best'] = $v['is_best']==1?'√':'×';
            $data[$k]['is_hot'] = $v['is_hot']==1?'√':'×';
        }
        return $data;
    }
    public function getCountInfo($model,$where){
        $count = $model
            ->alias('a')
            ->join('shop_cate b','a.cate_id = b.cate_id')
            ->join('shop_brand c','a.brand_id = c.brand_id')
            ->where($where)
            ->count();
        return $count;
    }

}