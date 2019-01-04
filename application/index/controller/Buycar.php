<?php
/**
 * Created by PhpStorm.
 * User: 小鱼
 * Date: 2018/12/8
 * Time: 13:44
 */

namespace app\index\controller;


class Buycar extends Common
{
    /** 查看购物车 */
    public function buyCar()
    {
        $this->getIndexCateInfo();
        if (session("?accInfo")) {
            $this->cartDb();
        } else {
            $this->cartCookie();
        }
        return view();
    }

    /** 数据库 */
    public function cartDb()
    {
        $carInfo = model('Cart')
            ->alias('c')
            ->join('shop_goods g', 'g.goods_id=c.goods_id')
            ->where(['user_id' => session('accInfo.user_id'), 'status' => 1])
            ->select();
        if(empty($carInfo)){
            $carInfo = [];
        }
//        dump($carInfo);exit;
        $this->assign('carInfo', $carInfo);
    }

    /** Cookie()*/
    public function cartCookie()
    {
        $cartInfo = cookie('cartInfo');

        if (!empty($cartInfo)) {
            $cart = unserialize(base64_decode($cartInfo));
            foreach ($cart as $k => $v) {
                $where = [
                    'goods_id' => $v['goods_id'],
                    'is_sell' => 1
                ];
                $goodsInfo = model('goods')->where($where)->find()->toArray();
                $cartData[] = array_merge($v, $goodsInfo);
            }
            if(empty($cartData)){
                $this->error("请添加数据",'Index/index');
            }
            $this->assign('carInfo', $cartData);

        } else {
            $this->error("请添加商品", 'Index/index');
        }

    }

    /** 更改数据库里的数量 */
    public function cartUpdate()
    {
        $number = input('post.ipt');
        if (session("?accInfo")) {
            //根据id 修改数量
            $id = input('post.cart_id');
            model('Cart')->save(['buy_number' => $number], ['cart_id' => $id]);
            return true;
        } else {
            //根据goods_id 修改cookie中的 数据
            $goods_id = input('post.goods_id', 0, 'intval');
            $cart = unserialize(base64_decode(cookie('cartInfo')));
            $cart[$goods_id]['buy_number'] = $number;
            $cartInfo = base64_encode(serialize($cart));
            cookie('cartInfo', $cartInfo);
            return true;
        }
    }

    /** 删除购物 */
    public function carDel()
    {
        if (session("?accInfo")) {
            // 接收id
            $cart_id = input('post.cart_id');
            // 根据id 删除物品
            $res = model('Cart')->save(['status' => 2, 'buy_number' => 0], ['cart_id' => $cart_id]);
            if ($res) {
                echo "1";
                exit;
            } else {
                echo "2";
                exit;
            }
        } else {
            $goods_id = input('post.goods_id');
            $cart = unserialize(base64_decode(cookie('cartInfo')));
            unset($cart[$goods_id]);
            $cartInfo = base64_encode(serialize($cart));
            cookie('cartInfo', $cartInfo);
            return true;
        }

    }

    /** 清空商品 */
    public function cartNone()
    {
        if (session("?accInfo")) {
            // 接收id
            $cart_id = input('post.cart_id');
//            $user_id = session('accInfo.user_id');

            $cart_id = explode(",", $cart_id);
//            $num='';
            foreach($cart_id as $k=>$v){
                $data = ['status'=>2,'buy_number'=>0];
                $res = model('Cart')->where(['cart_id'=>$v])->update($data);
            }
            if($res){
                echo 1;
            }else{
                echo 2;
            }
        }else{
            $goods_id = input('post.goods_id');
            $goods_id = explode(",", $goods_id);
            $cart = unserialize(base64_decode(cookie('cartInfo')));
            foreach($goods_id as $k=>$v){
                    unset($cart[$v]);
            }
            $cartInfo = base64_encode(serialize($cart));
            cookie('cartInfo', $cartInfo);
            return true;
        }
    }

    /** 收藏 */
    public function collection()
    {
        if (session("?accInfo")) {
            //收藏数据库
            $this->collectionDb();
        } else {
            echo 4;
            exit;
        }
    }

    /** 数据库 */
    public function collectionDb()
    {
        $type = input('post.type', '', 'intval');
        $goods_id = input('post.goods_id', '');
        $user_id = session('accInfo.user_id');
        if (empty($goods_id)) {
            echo "请选择商品";
            exit;
        }

        $arr = [
            'user_id' => $user_id,
            'goods_id' => $goods_id
        ];
        if ($type == 1) {
            // 先查唯一
            $count = model('Usergoods')->where($arr)->count();
            if ($count > 0) {
                echo 3;
                exit;
            }
            //添加收藏
            $res = model('Usergoods')->insert($arr);
            if ($res) {
                echo 1;
            } else {
                echo 2;
            }
        } else {
            $goods_id = explode(",", $goods_id);

            $num='';
            foreach($goods_id as $k=>$v){
                $data = ['user_id'=>$user_id,'goods_id'=>$v];
                $count = model('Usergoods')->where($data)->count();
                if($count==0){
                    $res = model('Usergoods')->insert($data);
                }else{
                    $num+=1;
                }
            }
            if(!empty($res)){
                echo 1;
            }else{
                echo 2;
            }

        }

    }

    /** 确认订单 */
    public function carteSttlement()
    {
        $this->getIndexCateInfo();
        if(session('?accInfo')){
            $cart_id = input('get.cart_id','');
            if(empty($cart_id)){
                $this->error('购物车里无商品不能结算');
            }
            $cart_id = explode(',',$cart_id);
            $cartWhere = [
                'user_id'=>$this->getUserId(),
                'cart_id'=>['in',$cart_id],
                'status'=>1
            ];
            //根据Id 查商品
            $sttlementInfo = model('Cart')
                ->alias('c')
                ->join('shop_goods g', 'c.goods_id=g.goods_id')
                ->where($cartWhere)
                ->select();
//            print_r($sttlementInfo);exit;
            $this->assign('sttle',$sttlementInfo);

            // 获取收货人信息
            $addressInfo = model('Address')->select();
//            print_r($addressInfo);exit;
            $this->assign('addressInfo',$addressInfo);

        }else{
            $this->error('请先登陆','Login/login');
        }


        return $this->fetch('buycartwo');
    }

}