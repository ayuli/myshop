<?php
namespace app\admin\controller;
use think\Controller;
class Login extends Controller
{
    /**登陆 */
    public function login()
    {   
        if (checkRequest()) {
            $admin_name = input('param.admin_name');
            $admin_pwd = input('param.admin_pwd');
            $mycode = input('param.mycode');
            //账号
            if (empty($admin_name)) {
                fail('账号必填');
            }
            //密码
            if (empty($admin_pwd)) {
                fail('密码必填');
            }
            //验证码
            if (empty($mycode)) {
                fail('验证码必填');
            }else if(!captcha_check($mycode)){
                fail('验证码有误');
            }
            // 检测账号
            $where = [
                'admin_name'=>$admin_name
            ];
            $model = model('Admin');
            $arr = $model->where($where)->find();
            if (!empty($arr)) {
                $salt = $arr['salt'];//盐值
                $pwd = createPwd($admin_pwd,$salt);
                if ($arr['admin_pwd']==$pwd) {
                    
                    //存session用户信息
                    session('adminInfo',['admin_id'=>$arr['admin_id'],'admin_name'=>$admin_name]);
                    
                    //改最后一次登陆时间登陆IP地址
                    $updateInfo = [
                        'last_login_time'=>time(),
                        'last_login_ip'=>request()->ip()
                    ];
                    $updateWhere = [
                        'admin_id'=>$arr['admin_id']
                    ];
                    $model->save($updateInfo,$updateWhere);
                    
                    //提示登录成功
                    successly("登陆成功");
                    
                }else {
                    fail("密码有误");
                }
            }else {
                fail("账号有误");
            }

        }else {
            $this->view->engine->layout(false);
            return view();
        }

    }

    /**退出 */
    public function quit()
    {
        session('adminInfo', null);
        $this->redirect(url('Login/login'));
    }
}
