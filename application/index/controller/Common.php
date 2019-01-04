<?php
namespace app\index\controller;
use think\Controller;

class Common extends Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->getConfigInfo();
        $this->topCart();
    }

    /** 防非法登陆- */
    public function checkUserLogin(){
        if(session("?accInfo")){
            session('accInfo');
        }else{
            $this->error('请先登陆','Login/login');
        }
    }

    /** 获取网络配置信息 */
    public function getConfigInfo()
    {
        if(session('?configInfo')){
            $configInfo = session('configInfo');
        }else{
            $arr = collection(model('Config')->select())->toArray();
            foreach($arr as $k=>$v){
                $configInfo[$v['name']]=$v['value'];
            }
            session('configInfo',$configInfo);
        }
        //dump($configInfo);die;
        $this->assign('config',$configInfo);

    }

    /** 获取左侧分类数据 */
    public function getIndexCateInfo()
    {
        $model = model('Cate');
        $where = [
            'is_show'=>1
        ];
        $info = $model->where($where)->select();
        $data = getIndexCateInfo($info);
        $this->assign('data',$data);
//        $this->view->engine->layout(false);//关闭模板布局
    }

    /** 获取楼层信息 */
    public function getFloorInfo($cate_id)
    {
        // 1.顶级分类信息
        $cate = model('Cate');
        $topwhere = [
            'is_show'=>1,
            'cate_id'=>$cate_id
        ];
        $info['topData'] = $cate->field('cate_id,cate_name')->where($topwhere)->find();
        // 2.二级分类信息
        $catewhere = [
            'pid'=>$cate_id,
            'is_show'=>1
        ];
        $info['cateData'] = $cate->field('cate_id,cate_name')->where($catewhere)->select();
        // 3.商品信息
        $cateInfo = $cate->where(['is_show'=>1])->select();
        $cateId = getCateId($cateInfo,$cate_id);
        $cateId = implode(',',$cateId);
        //
        $goods = model('Goods');
        $goodsWhere = [
            'cate_id'=>['in',$cateId],
            'is_sell'=>1
        ];
        $info['goodsData'] = $goods->where($goodsWhere)->select();

        return $info;
    }

    /** 获取session用户id */
    public function getUserId()
    {
        return session("accInfo.user_id");
    }

    /** 同步浏览记录 */
    public function asyncHistory()
    {
        $history = cookie('history');
        if(!empty($history)){
            $arr = unserialize(base64_decode($history));
            $user_id = $this->getUserId();
            foreach ($arr as $k=>$v){
                $v['user_id'] = $user_id;
                model('History')->insert($v);
            }
            cookie('history',null);
        }

    }

    /** 同步购物车到数据库里 */
    public function asyncCart()
    {
        $cart = cookie('cartInfo');
        $cartInfo = unserialize(base64_decode($cart));
        if(!empty($cartInfo)){
            // 查询商品是否在购物车
            foreach($cartInfo as $k=>$v){
                $goodsInfo = model('Goods')->where(['goods_id'=>$v['goods_id']])->find();
                if(empty($goodsInfo)){
                    fail('请选择商品');
                }
                if($goodsInfo['is_sell']==2){
                    fail('商品已下架');
                }
                $cartWhere = [
                    'goods_id'=>$v['goods_id'],
                    'user_id'=>session('accInfo.user_id')
                ];
                $cartData = model('Cart')->where($cartWhere)->find();
                if(!empty($cartData)){
                    // 有 修改
                    $update = [
                        'buy_number'=> $v['buy_number']+$cartData['buy_number']
                    ];
                    model('Cart')->save($update,$cartWhere);
                }else{
                    // 没有 添加
                    $v['add_price']=$goodsInfo['self_price'];
                    $v['user_id']=session("accInfo.user_id");
                    $v['create_time']=time();
                    $v['update_time']=time();
                    model('Cart')->insert($v);
                }
            }
            cookie('cartInfo',null);
        }
    }

    /**
     * 面包屑导航栏
     * @param $cate_id
     * @return string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getCrumbs($cate_id)
    {
        $cate = model('Cate');
        $where = [
            'is_show'=>1
        ];
        $arr = $cate->where($where)->select();
        //获取分类名称
        $name = getCateName($arr,$cate_id);
        //array_reverse返回一个单元顺序相反的数组
        $new_name = array_reverse($name);

//        print_r($new_name);die;
        $str = '';
        foreach($new_name  as $k=>$v){
            $str.="<a href='".url('product/productlist')."?cate_id=".$v['cate_id']."'>".$v['cate_name']."</a>".' > ';
        }
        $cate_str = rtrim($str,' > ');
        return $cate_str;
    }

    /** 右上角购物车清单  */
    public function topCart()
    {
            $topCart = model('Cart')
                ->alias('c')
                ->join('shop_goods g', 'g.goods_id=c.goods_id')
                ->where(['user_id' => session('accInfo.user_id'),'status'=>1])
                ->select();
            $count = model('Cart')->where(['user_id' => session('accInfo.user_id'),'status'=>1])->count();
            if(empty($topCart)){
                $topCart = [];
            }

            $this->assign('topCart', $topCart);
            $this->assign('countTop', $count);

//            $cartInfo = cookie('cartInfo');
//            if (!empty($cartInfo)) {
//                $cart = unserialize(base64_decode($cartInfo));
//                foreach ($cart as $k => $v) {
//                    $where = [
//                        'goods_id' => $v['goods_id'],
//                        'is_sell' => 1
//                    ];
//                    $goodsInfo = model('goods')->where($where)->find()->toArray();
//                    $cartData[] = array_merge($v, $goodsInfo);
//                }
//
//                $count = count($cartData);
//                $this->assign('topCart', $cartData);
//                $this->assign('countTop', $count);

            // 购物车共计
            $topCartall = model('Cart')
                ->alias('c')
                ->join('shop_goods g', 'g.goods_id=c.goods_id')
                ->where(['user_id' => session('accInfo.user_id')])
                ->select();
            $var = '';
            foreach ($topCartall as $k => $v) {
                $var += $v['self_price'] * $v['buy_number'];
            }
            if(empty($topCart)){
                $var = 0;
            }
//           dump($var);die;
            $this->assign('topCartall', $var);

    }

    /** 获取收货地址信息 */
    public function getAddressInfo($where,$address_id=''){
        $model_address=model('address');
        $area_model=model('area');
        if(empty($address_id)){
            $addressInfo=collection($model_address->where($where)->select())->toArray();
            if(!empty($addressInfo)){
                $id=[];
                foreach($addressInfo as $k=>$v){
                    $id[]=$v['province'];
                    $id[]=$v['city'];
                    $id[]=$v['district'];
                }
                $id=array_unique($id);
                $areaWhere=[
                    'id'=>['in',$id]
                ];
                $areaInfo=$area_model->where($areaWhere)->select();
                $area_name=[];
                foreach($areaInfo as $k=>$v){
                    $area_name[$v['id']]=$v['name'];
                }
                foreach($addressInfo as $k=>$v){
                    $addressInfo[$k]['province_name']=$area_name[$v['province']];
                    $addressInfo[$k]['city_name']=$area_name[$v['city']];
                    $addressInfo[$k]['district_name']=$area_name[$v['district']];
                }
                return $addressInfo;
            }else{
                return [];
            }

        }else{
            $where['id']=$address_id;
            $addressInfo=$model_address->where($where)->find()->toArray();
            if(!empty($addressInfo)){
                $id=[];
                $id[]=$addressInfo['province'];
                $id[]=$addressInfo['city'];
                $id[]=$addressInfo['district'];

                $areaWhere=[
                    'id'=>['in',$id]
                ];
                $areaInfo=$area_model->where($areaWhere)->select();
                $area_name=[];
                foreach($areaInfo as $k=>$v){
                    $area_name[$v['id']]=$v['name'];
                }

                $addressInfo['province_name']=$area_name[$addressInfo['province']];
                $addressInfo['city_name']=$area_name[$addressInfo['city']];
                $addressInfo['district_name']=$area_name[$addressInfo['district']];
                return $addressInfo;
            }else{
                return [];
            }

        }
    }

    /** 获取订单信息 */
    public function getOrderInfo($where){
        $orderInfo = model('Order')->where($where)->find();
        return $orderInfo;
    }

}