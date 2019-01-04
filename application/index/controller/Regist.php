<?php
namespace app\index\controller;
use think\Controller;

class Regist extends Controller
{
    public function regist()
    {
        if(checkRequest()){
            $data = input();
//            dump($data);die;
            //验证器验证
            $model = model('User');
            $validate = validate('User');
            if(empty($data['user_tel'])){
                if (!$validate->scene('email')->check($data)) {
                    fail($validate->getError());
                }
                $account=$data['user_email'];
            }else{
                if (!$validate->scene('tel')->check($data)) {
                    fail($validate->getError());
                }
                $account=$data['user_tel'];
            }
            $res = $model->allowField(true)->save($data);
            if($res){
                $accInfo = [
                    'user_id'=>$model->user_id,
                    'account'=>$account
                ];
                session('accInfo',$accInfo);
                successly('注册成功');
            }else{
                fail('注册失败');
            }
        }else{
            $this->view->engine->layout(false);//关闭模板布局
            return view();
        }
    }
    /**  邮箱发送验证*/
    public function sendEmail()
    {
        $email = input('post.email');
        $where = [
            'user_email'=>$email
        ];
        $ress = model('User')->where($where)->find();
        if($ress){
            fail('邮箱已存在');
        }
        $code = getRandCode();
        $res = sendEmail($email,$code);
        if($res){
            $emailInfo = [
                'user_email'=>$email,
                'code'=>$code,
                'time'=>time()
            ];
            session('accountInfo',$emailInfo);
            successly('发送成功');
        }else{
            fail('发送失败,请检查您的网络');
        }
    }
    /**  手机发送验证*/
    public function sendTel()
    {
        $tel = input('post.tel');
        $where = [
            'user_tel'=>$tel
        ];
        $ress = model('User')->where($where)->find();
        if($ress){
            fail('手机号已存在');
        }
        $code = getRandCode();
        $res = sendSms($tel,$code);
        if($res){
            $telInfo = [
                'user_tel'=>$tel,
                'code'=>$code,
                'time'=>time()
            ];
            session('accountInfo',$telInfo);
            successly('发送成功');
        }else{
            fail('发送失败');
        }
    }
}