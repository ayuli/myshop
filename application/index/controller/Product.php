<?php

namespace app\index\controller;
use page\AjaxPage;
class Product extends Common
{
    /** 全部商品 */
    public function productList()
    {
        $this->getIndexCateInfo();
        //展示所有品牌
        $cate_id = input('get.cate_id',0,'intval');
        //传顶级分类名称
        $cateName = model('Cate')->where(['cate_id'=>$cate_id])->find();
        $this->assign('cateName',$cateName);
        if(empty($cate_id)){
            $where = [
                'is_sell'=>1
            ];
            cookie('cate_id',null);
        }else{
            $cateInfo = model('Cate')->where(['is_show'=>1])->select();
            $cate_id =getCateId($cateInfo,$cate_id);
            $cate_id = implode(',',$cate_id);
            cookie('cate_id',$cate_id);
            $where = [
                'is_sell'=>1,
                'cate_id'=>['in',$cate_id]
            ];
        }
        $brand = model('Brand')
                ->field('distinct(g.brand_id),brand_name')
                ->alias('b')
                ->join("shop_goods g",'b.brand_id=g.brand_id')
                ->where($where)
                ->select();
        $this->assign('brand',$brand);
        //获取价格
        $max_price = model('Goods')->where($where)->value("max(self_price)");
        $price = $this->getPriceSection($max_price);
        $this->assign('price',$price);
        //展示所有默认商品

        $p = 1;
        $page_num = 8;
        $goodsInfo = model('Goods')->where($where)
                ->order('sale_num','desc')
                ->page($p,$page_num)->select();
//        echo model('Goods')->getLastSql();exit;
        $this->assign('goodsInfo',$goodsInfo);
        //调用分页类 获取页码
        $count = model('Goods')->where($where)->count();
        $page_str = AjaxPage::ajaxpager($p,$count,$page_num,url('Product/productsearch'),'show');
        //共**件
        $this->assign('count',$count);
        //分页
        $this->assign('page_str',$page_str);
        // 查询浏览历史记录
        if(session('?accInfo')){
            $goods = $this->getHistoryDb();
            $this->assign('goods',$goods);
        }else{
            $goods = $this->getHistoryCookie();
            $this->assign('goods',$goods);
        }

        return view();

    }
    /** 查询浏览历史-数据库里*/
    public function getHistoryDb()
    {
        $where = [
            'user_id'=>session('accInfo.user_id')
        ];
        $goods_id = model('History')
                    ->where($where)
                    ->order('create_time','desc')
                    ->column('goods_id');
        if(!empty($goods_id)){
            $goods_id = array_slice(array_unique($goods_id),0,5);

            foreach($goods_id as $k=>$v){
                $where = [
                    'goods_id'=>$v,
                    'is_sell'=>1
                ];
                $goodsDb[] = model('Goods')
                    ->field('goods_img,goods_name,goods_score,self_price')
                    ->where($where)
                    ->find();
            }
            return $goodsDb;
        }else{
            return [];
        }

    }
    /** 查询cookie*/
    public function getHistoryCookie()
    {
        $history_srt = cookie('history');
        $historyInfo = unserialize(base64_decode($history_srt));
        if(!empty($historyInfo)){
            $historyInfo = array_reverse($historyInfo);
            foreach($historyInfo as $k=>$v){
                $goods_id[] = $v['goods_id'];
            }
            $goods_id = array_slice(array_unique($goods_id),0,5);

            foreach($goods_id as $k=>$v){
                $where = [
                    'goods_id'=>$v,
                    'is_sell'=>1
                ];
                $goodsCookie[] = model('Goods')
                    ->field('goods_img,goods_name,goods_score,self_price')
                    ->where($where)
                    ->find();
            }
            return $goodsCookie;
        }else{
            return [];
        }
    }
    /** 分页 */
    public function productsearch()
    {
    }
    /** 获取价格区间 */
    public function getPriceSection($max_price)
    {
        $price = [];
        for($i=0;$i<6;$i++){
            $start = ($max_price/6)*$i;
            $end = ($max_price/6)*($i+1)-0.01;
            $price[] = number_format($start,2,'.',',').'-'.number_format($end,2,'.',',');
        }
        $price[] = number_format($end+0.01,2,'.',',').'以上';
        return $price;
    }
    /** 重新获取价格区间 */
    public function getPrice()
    {
        // 接收品牌ID
        $brand_id = input('post.brand_id',0,'intval');
        $where = [
            'is_sell'=>1,
            'brand_id'=>$brand_id
        ];
        $maxPrice = model('Goods')->where($where)->order('self_price','desc')->value("self_price");
        if(!empty($maxPrice)){
            $price = $this->getPriceSection($maxPrice);
            echo json_encode($price);
        }else{
            fail('此品牌下没有商品');
        }


    }
    /** 大搜索 */
    public function getGoodsInfo()
    {
        // 接收条件
        $p = input('post.p',1,'intval');
        $brand_id = input('post.brand_id','');
        $price = input('post.price','');
        $price = str_replace(',','',$price);
        $flag = input('post.flag','','intval');
        $cate_id = cookie('cate_id');
//        echo $cate_id;exit;
        //处理筛选的条件
        $where = [];
        if(!empty($cate_id)){
            $where['cate_id'] = ['in',$cate_id];
        }

        if(!empty($brand_id)){
            $where['brand_id'] = $brand_id;
        }

        if(!empty($price)){
            if(substr_count($price,'-')>0){
                $arr = explode('-',$price);
                $where['self_price'] = ['between',$arr];
            }else{
                $arr = intval($price);
                $where['self_price'] = ['>=',$arr];
            }
        }
//                print_r($where);exit;
        //处理排序条件
        $field = "sale_num";
        $order = 'desc';
        if($flag==2){
            $field = "sale_num";
        }else if($flag==3){
            $field = "self_price";
        }else if($flag==4){
            $where['is_new'] = 1;
        }
//        echo $field;echo $order;
        // 查询商品
        $page_num = 8;
        $order = input('post.order','desc');

        // 查询分页页码
        $goodsInfo = model('Goods')->where($where)
            ->order($field,$order)
            ->page($p,$page_num)->select();
        $this->assign('goodsInfo',$goodsInfo);
        //调用分页类 获取页码
        $count = model('Goods')->where($where)->count();
        $page_str = AjaxPage::ajaxpager($p,$count,$page_num,url('Product/productsearch'),'show');
        $this->assign('page_str',$page_str);
        $this->view->engine->layout(false);

        $product = $this->fetch('product');

        $info = ['product'=>$product,'count'=>$count];
        echo json_encode($info);

    }
    /** 商品详情 */
    public function detail()
    {
        //查询左侧分类信息
        $this->getIndexCateInfo();
        //接收 id
        $goods_id = input('get.id',0,'intval');
        if(empty($goods_id)){
            echo "请选择商品";exit;
        }
        $listInfo = model('Goods')->where(['goods_id'=>$goods_id])->find();
        //去除最后的|
        $listInfo['goods_imgs'] = rtrim($listInfo['goods_imgs'],'|');
        //处理图片
        $listInfo['goods_imgs'] = explode('|',$listInfo['goods_imgs']);

        if(empty($listInfo)){
            echo "没有商品";exit;
        }
        if($listInfo['is_sell']==2){
            echo "商品已下架";exit;
        }
//        print_r(session('accInfo'));die;

        //记录购物车浏览记录
        if(session('accInfo')){
            // 存数据库
            $this->saveHistoryDb($goods_id);
        }else{
            // 存cookie
            $this->saveHistoryCookie($goods_id);
        }

        $this->assign('listInfo',$listInfo);
        // 面包屑导航
        $name = $this->getCrumbs($listInfo['cate_id']);
        $this->assign('name',$name);
        return view();
    }
    /** 存历史浏览记录到Cookie中 */
    public function saveHistoryCookie($goods_id){
        //先从cookie中取出
        $prevHistory = cookie('history');
        if(!empty($prevHistory)){
           //解开
            $history = unserialize(base64_decode($prevHistory));
            //加入
            $history[] = ['goods_id'=>$goods_id,'create_time'=>time()];
            //再存入cookie
            $str = base64_encode(serialize($history));
            cookie('history',$str);
        }else{
            $arr[] = ['goods_id'=>$goods_id,'create_time'=>time()];
            $str = base64_encode(serialize($arr)); //加密 - 序列化
            cookie("history",$str);
        }

    }
    /** 存历史浏览记录到数据库中 */
    public function saveHistoryDb($goods_id)
    {
        $history = [
            'user_id'=>$this->getUserId(),
            'goods_id'=>$goods_id,
        ];
        model('History')->save($history);
    }
}