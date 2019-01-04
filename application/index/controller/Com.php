<?php
namespace app\index\controller;
use think\Controller;

class Com extends Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->getConfigInfo();
        $this->checkUserLogin();
        $this->topCart();
    }

    /** 防非法登陆 */
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

    /** 获取session用户id */
    public function getUserId()
    {
        return session("accInfo.user_id");
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
//            dump($var);die;
        $this->assign('topCartall', $var);

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




}