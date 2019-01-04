<?php
namespace app\index\controller;
use think\Controller;

class Login extends Common
{
    public function login()
    {
        if (checkRequest()){
            $account = input('param.account');
            $user_pwd = input('param.user_pwd');
            $remember_me = input('param.remember_me');
            $data = input('param.');
//            dump($data);die;
            if ($account==''){
                fail("账号或邮箱不能为空");
            }
            if($user_pwd==''){
                fail("密码不能为空");
            }
            //验证  还有唯一
            //条件判断是邮箱还是手机号
            if (substr_count($account,'@')>0){
                $where = [
                    'user_email'=>$account,
                ];
            }else{
                $where = [
                    'user_tel'=>$account,
                ];
            }
            //查账号密码一致登陆成功
            $res = model('User')->where($where)->find();
            $time = time();
            $error_num=$res['error_num'];
            $last_error_time = $res['last_error_time'];
            if($res){
                if(md5($user_pwd)==$res['user_pwd']){
//                    echo "密码正确";
                    if($error_num>=5&&$time-$last_error_time<3600){
                        $_time = 60-(ceil(($time-$last_error_time)/60));
                        fail('当前账号已锁定,'.$_time.'分钟之后可以登陆');
                    }
                    //次数清0 时间清空
                    $updateInfo = [
                        'error_num'=>0,
                        'last_error_time'=>null
                    ];
                    model('User')->where($where)->update($updateInfo);
                    //判断是否记住密码 是 --账号密码存coolie 10 天
                    if($remember_me=='true'){
                        cookie('account',$account,$time+60*60*24*10);
                        cookie('user_pwd',$user_pwd,$time+60*60*24*10);
                    }
                    $accInfo = [
                        'account'=>$account,
                        'user_id'=>$res['user_id']
                    ];
                    session('accInfo',$accInfo);
//                    cookie('cartInfo', null);

                    //同步浏览记录到数据库
                    $this->asyncHistory();
                    //同步购物记录到数据库
                    $this->asyncCart();

                    successly('登陆成功');
                }else{
//                    echo "密码错误";
                    // 距离上次错误时间超过一个小时 次数改为1 时间为当前时间
                    if($time-$last_error_time>3600){
                        $updateInfo = [
                            'error_num'=>1,
                            'last_error_time'=>$time
                        ];
                        model('User')->where($where)->update($updateInfo);
                        fail('您还可以输入4次');
                    }else{
                        if($error_num>=5){
                            $_time = 60-(ceil(($time-$last_error_time)/60));
                            fail('当前账号已锁定,'.$_time.'分钟之后可以登陆');
                        }else{
                            $error_num++;
                            $updateInfo = [
                                'error_num'=>$error_num,
                                'last_error_time'=>$time
                            ];
                            model('User')->where($where)->update($updateInfo);
                            $num = 5-$res['error_num'];
                            fail('您还可以输入'.$num.'次');
                        }
                    }


                }
//
            }else{
                fail("账号或密码有误");
            }

        }else{
             $this->view->engine->layout(false);//关闭模板布局

            return view();
        }
    }
    /** 退出 */
    public function quit(){
        cookie('account', null);
        cookie('cartInfo', null);
        cookie('user_pwd', null);
        session('accInfo',null);
        $this->redirect(url('Login/login'));
    }
}