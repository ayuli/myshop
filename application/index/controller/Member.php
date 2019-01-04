<?php
namespace app\index\controller;

class Member extends Com
{
    /** 管理中心 */
    public function member()
    {
        return view();
    }
    /** 我的订单 */
    public function order()
    {
        $where = [
            'user_id'=>$this->getUserId()
        ];
        $orderInfo = model('Order')->where($where)->select();
        $this->assign('orderInfo',$orderInfo);
        return view();
    }
    /** order */
    public function orderlist(){
        $order_number = input('get.order_number');
        $data = $this->text($order_number);
        foreach($data as $k=>$v){
            $dataInfo[] = get_object_vars($v);//把stdclass object 转化成数组
        }
        $this->assign('dataInfo',$dataInfo);
        return view();
    }
    /** 快递接口 */
    public function text($order_number)
    {
        date_default_timezone_set("PRC");
        $showapi_appid = '83196';  //替换此值,在官网的"我的应用"中找到相关值
        $showapi_secret = 'c0e8c841b1614dba9d1d4775e081536c';  //替换此值,在官网的"我的应用"中找到相关值
        $paramArr = array(
            'showapi_appid'=> $showapi_appid,
            'com'=> "zhongtong",
            'nu'=> $order_number
        //添加其他参数
        );

        //创建参数(包括签名的处理)
        function createParam ($paramArr,$showapi_secret) {
            $paraStr = "";
            $signStr = "";
            ksort($paramArr);
            foreach ($paramArr as $key => $val) {
                if ($key != '' && $val != '') {
                    $signStr .= $key.$val;
                    $paraStr .= $key.'='.urlencode($val).'&';
                }
            }
            $signStr .= $showapi_secret;//排好序的参数加上secret,进行md5
            $sign = strtolower(md5($signStr));
            $paraStr .= 'showapi_sign='.$sign;//将md5后的值作为参数,便于服务器的效验
            return $paraStr;
        }

        $param = createParam($paramArr,$showapi_secret);
        $url = 'http://route.showapi.com/64-19?'.$param;
        $result = file_get_contents($url);
        $result = json_decode($result);
        $data = $result->showapi_res_body->data;
        return $data;
    }
    /** 收货地址 */
    public function address()
    {
        if(checkRequest()){
            $id = input('post.id');
            $where = [
                'pid'=>$id
            ];
            $city = model('Area')->where($where)->select();
            echo json_encode($city);
        }else{
            // 三级联动 获取所有省份作为下拉菜单的值
            $where = [
                'pid'=>0
            ];
            $province = model('Area')->where($where)->select();
            $this->assign('province',$province);
            // 展示数据
            $userWhere = [
                'user_id'=>session('accInfo.user_id')
            ];
            $userList = model('Address')->where($userWhere)->select();
            foreach($userList as $k=>$v){
                $userList[$k]['province']= model('Area')->where(['id'=>$v['province']])->value('name');
                $userList[$k]['city']= model('Area')->where(['id'=>$v['city']])->value('name');
                $userList[$k]['district']= model('Area')->where(['id'=>$v['district']])->value('name');
            }

//            foreach($userList as $k=>$v) {
//             $areaWhere = [
//                   'id' => ['in', [$v['province'],$v['city'],$v['district']]]
//                ];
//                $field = model('Address')->where($areaWhere)->column('name');
//                $userList[$k]['address'] = implode('',$field);
//            }
            $this->assign('userList',$userList);

            return view();
        }
    }
    /** 添加收货地址 */
    public function addressAdd()
    {
        // 接收值
        $data = input('post.');
        if($data['is_default']=='true'){
            $data['is_default']=1;
            $where = [
                'user_id'=>session('accInfo.user_id')
            ];
            model('Address')->where($where)->update(['is_default'=>2]);
        }else{
            $data['is_default']=2;
        }
        // 验证器
        $validate = validate('Address');
        if (!$validate->check($data)) {
            fail($validate->getError());
        }
        //入库
        $res = model('Address')->save($data);
        if($res){
            successly('添加成功');
        }else{
            fail('添加失败');
        }
    }
    /** 删除收货地址 */
    public function addressDel()
    {
        $address_id = input('post.address_id');
        $res = model('Address')->where(['id'=>$address_id])->delete();
//        echo model('Address')->getLastSql();
//        die;
        if($res){
            successly('删除成功');
        }else{
            fail('删除失败');
        }
    }
    /** 设为默认 */
    public function defaults()
    {
        $default_id = input('post.default_id');
        $where = [
            'user_id'=>session('accInfo.user_id')
        ];
        model('Address')->where($where)->update(['is_default'=>2]);
        $res = model('Address')->where(['id'=>$default_id])->update(['is_default'=>1]);
        if($res){
            successly('设置成功');
        }else{
            fail('设置失败');
        }
    }
    /** 收货地址编辑 */
    public function compile()
    {

        // 根据id查数据
        $id = input('get.id',0,'intval');
        if(empty($id)){
            echo "非法操作";exit;
        }

        $arr = model('Address')->where(['id'=>$id])->find();
        $this->assign('arr',$arr);

        // 三级联动 获取所有省份作为下拉菜单的值
        $where = [
            'pid'=>0
        ];
        //省
        $province = model('Area')->where($where)->select();
        $this->assign('province',$province);
        //省下的市
        $city = model('Area')->where(['pid'=>$arr['province']])->select();
        $this->assign('city',$city);
        //市下的区
        $district = model('Area')->where(['pid'=>$arr['city']])->select();
        $this->assign('district',$district);
        return view();
    }
    /** 收货地址执行编辑修改 */
    public function compileUpdate()
    {
           //接收数据
           $data = input('post.');
           //是否默认收货地址那一个
           if($data['is_default']=='true'){
               $data['is_default']=1;
               $where = [
                   'user_id'=>session('accInfo.user_id')
               ];
               model('Address')->where($where)->update(['is_default'=>2]);
           }else{
               $data['is_default']=2;
           }
           //根据隐藏ID修改
           $res = model('Address')->where(['id'=>$data['id']])->update($data);
           if($res){
               successly('修改成功');
           }else{
               fail('修改失败');
           }



    }
    /** 用户信息 */
    public function user()
    {
        return view();
    }
    /** 我的收藏 */
    public function collect()
    {
        return view();
    }
    /** 我的留言 */
    public function msg()
    {
        return view();
    }
    /** 推广链接 */
    public function links()
    {
        return view();
    }
    /** 我的佣金 */
    public function commission()
    {
        return view();
    }
    /** 申请体现 */
    public function cash()
    {
        return view();
    }
    /** 账号安全 */
    public function safe()
    {
        return view();
    }
    /** 我的红包 */
    public function packet()
    {
        return view();
    }
    /** 资金管理 */
    public function money()
    {
        return view();
    }
    /** 充值 */
    public function moneyCharge()
    {
        return view();
    }
    /** 支付 */
    public function moneyPay()
    {
        return view();
    }
        /** 我的会员 */
    public function memberCenter()
    {
        return view();
    }
    /** 一级会员 */
    public function memberList()
    {
        return view();
    }
    /** 我的业绩 */
    public function results()
    {
        return view();
    }
}