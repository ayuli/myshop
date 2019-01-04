<?php
namespace app\index\controller;

class Cart extends Common
{
    /**
     * 购物车添加
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function addCart()
    {
        //接数据
        $goods_id = input('post.goods_id',0,'intval');
        $buy_number = input('post.goods_num',0,'intval');
        if(empty($goods_id)){
            fail('非法操作');
        }
        if(empty($buy_number)){
            fail('非法操作');
        }
        $goodsWhere = [
            'goods_id'=>$goods_id
        ];
        //查商品表信息
        $goodsInfo = model('Goods')->where($goodsWhere)->find();
        //小验证-库存和下架
        if(empty($goodsInfo)){
            fail('请选择购买的商品');
        }else if($buy_number>$goodsInfo['goods_num']){
            fail('库存不足');
        }else if($goodsInfo['is_sell']==2){
            fail('商品已下架');
        }

        // 判断是否登陆用户 - 有用户 则把购物车里的信息存到 数据库里 - 没有则存到cookie里
        if(session("?accInfo")){
            $cartWhere = [
                'goods_id'=>$goodsInfo['goods_id'],
                'user_id'=>session('accInfo.user_id')
            ];
            $cartInfo = model('Cart')->where($cartWhere)->find();
            // 用户添加↓
            $this->cartDb($cartInfo,$buy_number,$cartWhere,$goods_id,$goodsInfo);
        }else{
            // cookie添加↓
            $res = $this->cartCookie($goods_id,$buy_number,$goodsInfo);
            if($res){
                successly('已添加到购物车');
            }
        }
    }
    /**
     * 用户添加
     * @param $cartInfo
     * @param $buy_number
     * @param $cartWhere
     * @param $goods_id
     * @param $goodsInfo
     */
    public function cartDb($cartInfo,$buy_number,$cartWhere,$goods_id,$goodsInfo)
    {

        if($cartInfo){
            // 累加(修改)
            $buy_number = $buy_number+$cartInfo['buy_number'];
            $update = [
                'buy_number'=>$buy_number,
                'status'=>1
            ];
            if($buy_number>$goodsInfo['goods_num']){
                fail('库存不足');
            }
            $res = model('Cart')->save($update,$cartWhere);
//                echo model('Cart')->getLastSql();
            if($res){
                successly('已添加到购物车');
            }else{
                fail('添加失败');
            }
        }else{
            // 添加
            $insert = [
                'goods_id'=>$goods_id,
                'add_price'=>$goodsInfo['self_price'],
                'buy_number'=>$buy_number,
                'user_id'=>session("accInfo.user_id")
            ];
            $res = model('Cart')->save($insert);
            if($res){
                successly('已添加到购物车');
            }else{
                fail('添加失败');
            }
        }
    }
    /**
     * cookie添加
     * @param $goods_id
     * @param $buy_number
     */
    public function cartCookie($goods_id,$buy_number,$goodsInfo)
    {
        $cart = cookie('cartInfo');
        //判断是否有cookie↓ 没有走第一次添加 有 往数组里填
        if(!empty($cart)){
            $arr = unserialize(base64_decode(cookie('cartInfo')));

            // 判断是否重复添加同样的商品信息
            if(!empty($arr[$goods_id])){
                // 重复添加 - 增加库存 修改时间
                $arr[$goods_id]['buy_number'] = $arr[$goods_id]['buy_number']+$buy_number;
                //验证库存!!!

                if($arr[$goods_id]['buy_number']>$goodsInfo['goods_num']){
                    fail('库存不足');
                }
                $cart = base64_encode(serialize($arr));
                cookie('cartInfo',$cart,3600*24);
            }else{
                // 不重复 - 往数组里填
                $arr[$goods_id]=[
                    'goods_id'=>$goods_id,
                    'buy_number'=>$buy_number,
                ];
                $cart = base64_encode(serialize($arr));
                cookie('cartInfo',$cart,3600*24);
            }
        }else{
            // 第一次添加商品
            $arr[$goods_id]=[
                'goods_id'=>$goods_id,
                'buy_number'=>$buy_number,
            ];
            $cart = base64_encode(serialize($arr));
            cookie('cartInfo',$cart,3600*24);
        }
        return true;
    }

}