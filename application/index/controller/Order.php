<?php
namespace app\index\controller;

class Order extends Common
{
    // 订单
    public function confirmOrder()
    {
        if(!session('?accInfo')){
            fail("请先登陆");
        }
        $cart_id = input('post.cart_id','');
        $cart_id = explode(',',$cart_id);
        $pay_type = input('post.pay_type','','intval');
        $add_txt = input('post.add_txt','');

        $user_id = $this->getUserId();

        if(empty($cart_id)){
            fail("操作有误");
        }

        $cartWhere = [
            'user_id'=>$user_id,
            'cart_id'=>['in',$cart_id],
            'status'=>1
        ];
        //根据Id 查商品
        $cartInfo = model('Cart')
            ->alias('c')
            ->join('shop_goods g', 'c.goods_id=g.goods_id')
            ->where($cartWhere)
            ->select();
        // 循环检测每个商品的库存
        foreach($cartInfo as $k=>$v){
            if($v['buy_number']>$v['goods_num']){
                fail('库存不足');
//                unset($cartInfo[$k]);
            }
        }
        if(empty($cartInfo)){
            fail("数据库里无数据不能结算");
        }

//        $this->assign('sttle',$sttlementInfo);
        model('Order')->startTrans();//启动事务

        try{
            // 表单数据写入
            $order =[];
            $order_number = $this->getOrderNumber();
            $order['order_number'] = $order_number;
            $order['user_id'] = $user_id;
            $order_amount=0;
            $order_score=0;
            foreach ($cartInfo as $k=>$v){
                $order_amount+=$v['buy_number']*$v['self_price'];
                $order_score+=$v['buy_number']*$v['goods_score'];
            }

            $order['order_amount'] = $order_amount;//总价
            $order['order_score'] = $order_score;//积分
            $order['pay_type'] = $pay_type;
            $order['order_message'] = $add_txt;
            if($pay_type==3){
                $order['order_status']=5;
            }

            $order_res = model('Order')->save($order);
            $order_id = model('Order')->order_id;
            if(empty($order_res)){
                model('Order')->rollback();
                throw new \Exception('订单数据写入失败');
            }

            // 订单详细表 数据写入
            $order_detail = [];
            foreach($cartInfo as $k=>$v){
                $order_detail[] = [
                    'user_id'=>$user_id,
                    'order_id'=>$order_id,
                    'goods_id'=>$v['goods_id'],
                    'goods_name'=>$v['goods_name'],
                    'self_price'=>$v['self_price'],
                    'goods_img'=>$v['goods_img'],
                    'buy_number'=>$v['buy_number']
                ];
            }
            $detail_res = model('OrderDetail')->saveAll($order_detail);
            if(empty($detail_res)){
                model('Order')->rollback();
                throw new \Exception('订单数据写入失败');
            }
            // 订单收货地址 数据写入
            $address_id = input('post.address_id','');
            $order_address = [];
            $addressWhere = [
                'user_id'=>$user_id
            ];
            $addressInfo = $this->getAddressInfo($addressWhere,$address_id);
            $order_address['user_id'] = $user_id;
            $order_address['order_id'] = $order_id;
            $order_address['province'] = $addressInfo['province_name'];
            $order_address['city'] = $addressInfo['city_name'];
            $order_address['district'] = $addressInfo['district_name'];
            $order_address['consignee_name'] = $addressInfo['consignee_name'];
            $order_address['consignee_tel'] = $addressInfo['consignee_tel'];
            $order_address['detailed_address'] = $addressInfo['detailed_address'];
            $address_res = model('OrderAddress')->save($order_address);
            if(empty($address_res)){
                model('Order')->rollback();
                throw new \Exception('订单收货地址写入失败');
            }
            // 购物车表 数据清空
            $cartWhere = [
                'user_id'=>$user_id,
                'cart_id'=>['in',$cart_id]
            ];
            $cartData = ['status'=>2,'buy_number'=>0];
            $cart_res = model('Cart')->where($cartWhere)->update($cartData);
            if(empty($cart_res)){
                model('Order')->rollback();
                throw new \Exception('购物车数据清空失败');
            }
            // 商品表 修改数据
            foreach($cartInfo as $k=>$v){
                $goodsWhere = [
                    'goods_id'=>$v['goods_id']
                ];
                $goodsData = [
                    'goods_num'=>$v['goods_num']-$v['buy_number']
                ];
                $goods_res = model('Goods')->where($goodsWhere)->update($goodsData);
            }
            if(empty($goods_res)){
                model('Order')->rollback();
                throw new \Exception('商品数据修改失败');
            }

            model('Order')->commit(); //用于非自动提交状态下面的查询提交
            echo json_encode(['font'=>'下单成功','code'=>1,'order_id'=>$order_id]);
            // successly('下单成功');

        }catch(\Exeption $e){
            fail($e->getMessage());
        }

    }

    // 获取订单号
    public function getOrderNumber()
    {
        // 1+年月日+id4为+随机4位
        $date = date('ymd');
        $id = $this->getUserId();
        $user_id = str_repeat('0',4-strlen($id)).$id;
        $order_number = '1'.$date.$user_id.rand(1000,9999);
        return $order_number;
    }

    /** 下单成功页面 */
    public function orderSuccess()
    {
        $this->getIndexCateInfo();
        $order_id = input('get.order_id');
        try{
            if(empty($order_id)){
                new \Exception('订单操作错误');
            }

            $orderWher = [
                'order_id'=>$order_id
            ];
            $orderInfo = model('Order')->where($orderWher)->find();
            if(empty($orderInfo)){
                new \Exception('没有此订单信息');
            }
            $this->assign('orderInfo',$orderInfo);

        }catch(\Exeption $e){
            echo $e->getMessage();
        }
        return view();

    }

    /** 支付 */
    public function alipay()
    {
        // 获取订单号
        $order_number = input('get.order_number');
        // 查询订单信息
        $orderWhere = [
            'order_number'=>$order_number
        ];
        $orderInfo = $this->getOrderInfo($orderWhere);
        if(empty($orderInfo)){
            $this->error('订单不存在',url('Index/index'));
        }
        /** @var  $config */
        $config = config('alipay_config');
        require_once EXTEND_PATH.'alipay/pagepay/service/AlipayTradeService.php';
        require_once EXTEND_PATH.'alipay/pagepay/buildermodel/AlipayTradePagePayContentBuilder.php';

        //商户订单号，商户网站订单系统中唯一订单号，必填
        $out_trade_no = $order_number;

        //订单名称，必填
        $subject = '电商-支付';

        //付款金额，必填
        $total_amount = $orderInfo['order_amount'];

        //商品描述，可空
        $body = '';

        //构造参数
        $payRequestBuilder = new \AlipayTradePagePayContentBuilder();
        $payRequestBuilder->setBody($body);
        $payRequestBuilder->setSubject($subject);
        $payRequestBuilder->setTotalAmount($total_amount);
        $payRequestBuilder->setOutTradeNo($out_trade_no);

        $aop = new \AlipayTradeService($config);

        /**
         * pagePay 电脑网站支付请求
         * @param $builder 业务参数，使用buildmodel中的对象生成。
         * @param $return_url 同步跳转地址，公网可以访问
         * @param $notify_url 异步通知地址，公网可以访问
         * @return $response 支付宝返回的信息
         */
        $response = $aop->pagePay($payRequestBuilder,$config['return_url'],$config['notify_url']);

        //输出表单
        var_dump($response);
    }

    /** 同步通知地址 */
    public function paySuccess()
    {
        $param=input('get.');

        //验证订单号
        $orderWhere=[
            'order_number'=>$param['out_trade_no']
        ];
        $orderInfo=$this->getOrderInfo($orderWhere);
        if(empty($orderInfo)){
            $this->error('当前支付的订单号不存在',url('Index/index'));
        }

        //验证订单金额
        if($orderInfo['order_amount']!=$param['total_amount']){
            $this->error('当前支付的总金额有误',url('Index/index'));
        }

        //验证签名
        require_once EXTEND_PATH.'alipay/pagepay/service/AlipayTradeService.php';
        $config=config('alipay_config');
        $alipaySevice = new \AlipayTradeService($config);
        $result = $alipaySevice->check($param);
        if(!$result) {
            $this->error('验签失败',url('Index/index'));
        }

        //调用支付宝查询接口 如果为已支付 并且  数据库支付状态为未支付   改变数据库支付状态
        require_once EXTEND_PATH.'alipay/pagepay/buildermodel/AlipayTradeQueryContentBuilder.php';

        $out_trade_no = $param['out_trade_no'];   //商户订单号，商户网站订单系统中唯一订单号
        $trade_no = ''; //支付宝交易号 //请二选一设置

        //构造参数
        $RequestBuilder = new \AlipayTradeQueryContentBuilder();
        $RequestBuilder->setOutTradeNo($out_trade_no);
        $RequestBuilder->setTradeNo($trade_no);
        $aop = new \AlipayTradeService($config);
        $response = $aop->Query($RequestBuilder);
        if($response->trade_status=='TRADE_SUCCESS'){
            $data=[
                'pay_status'=>2,
                'order_status'=>3,
                'pay_time'=>time(),
            ];
            $where=[
                'order_number'=>$out_trade_no
            ];
            $order_model=model('Order');
            $res=$order_model->save($data,$where);
            if(empty($res)){
                $this->error('支付状态修改失败',url('Index/index'));
            }
        }


        $this->getIndexCateInfo();
        $this->assign('orderInfo',$orderInfo);
        return view();

    }

    /** 异步通知地址 */
    public function notify(){
        $param=input('post.');
        $filename='/data/wwwroot/myshop/notify.log';
        file_put_contents(
            $filename,
            print_r($param,true),
            FILE_APPEND
        );

        //验证订单是否正确
        $order_number=$param['out_trade_no'];
        $orderWhere=[
            'order_number'=>$param['out_trade_no']
        ];
        $orderInfo=$this->getOrderInfo($orderWhere);
        if(empty($orderInfo)){
            file_put_contents(
                $filename,
                'order_number not found',
                FILE_APPEND
            );
            echo 'order_number error';exit;
        }
        //验证订单金额
        if($orderInfo['order_amount']!=$param['total_amount']){
            file_put_contents(
                $filename,
                'order_amount error',
                FILE_APPEND
            );
            echo 'order_amount error';exit;
        }

        //验证签名
        $config=config('alipay_config');
        require_once EXTEND_PATH.'alipay/pagepay/service/AlipayTradeService.php';

        $alipaySevice = new \AlipayTradeService($config);
        $result = $alipaySevice->check($param);
        if(empty($result)){
            file_put_contents(
                $filename,
                'sign error',
                FILE_APPEND
            );
            echo 'sign error';exit;
        }


        //验证应用id
        if($config['app_id']!=$param['app_id']){
            file_put_contents(
                $filename,
                'app_id error',
                FILE_APPEND
            );
            echo 'app_id error';exit;
        }

        //验证支付状态是否为已支付 success
        if($orderInfo['pay_status']==2){
            file_put_contents(
                $filename,
                'pay_status pass',
                FILE_APPEND
            );
            echo 'success';exit;
        }

        //改支付状态  支付时间   success  fail
        if($orderInfo['pay_status']==1){
            $data=[
                'pay_status'=>2,
                'order_status'=>3,
                'pay_time'=>time(),
            ];
            $where=[
                'order_number'=>$order_number
            ];
            $order_model=model('Order');
            $res=$order_model->save($data,$where);
            if($res){
                file_put_contents(
                    $filename,
                    'pay_status pass',
                    FILE_APPEND
                );
                echo 'success';
            }else{
                file_put_contents(
                    $filename,
                    'pay_status fail',
                    FILE_APPEND
                );
                echo 'fail';
            }
        }
    }

    /** 取消订单 */
    public function orderQuit()
    {
        $order_id = input('get.order_id',0,'intval');
        $user_id = $this->getUserId();
        $orderidWhere = [
            'user_id'=>$user_id,
            'order_id'=>$order_id,
        ];
        //根据Id 查购买商品数量
        $orderDeailInfo = model('OrderDetail') //商品详情表
            ->where($orderidWhere)
            ->find();
        // 商品表
        $goodsWhere = [
            'goods_id'=>$orderDeailInfo['goods_id']
        ];
        $goodsInfo = model('Goods')->where($goodsWhere)->find();
        //返回库存
        $goodsData = [
            'goods_num'=>$orderDeailInfo['buy_number']+$goodsInfo['goods_num']
        ];
        $goods_res = model('Goods')->where($goodsWhere)->update($goodsData);

        //修改订单状态
        model('Order')->where(['order_id'=>$order_id])->update(['pay_status'=>3,'order_status'=>2]);

        $this->success('取消成功','member/order');

    }

    /** 退款 */
    public function orderRefund()
    {
        $order_id = input('get.order_id',0,'intval');
        echo $order_id;
    }
}