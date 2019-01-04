<?php
namespace app\index\validate;
use think\Validate;

class User extends Validate
{
    protected $rule = [
        'user_tel'  =>  'require|checkTel',
        'user_email'  =>  'require|email|checkEmail|unique:User',
        'user_code'  =>  'require|checkCode',
        'user_pwd'  =>  'require|checkPwd',
        'user_pwd1'  =>  'require|confirm:user_pwd',
    ];

    protected $message = [
        'user_tel.require'  =>  '手机号或邮箱不能为空',
        'user_email.require'  =>  '手机号或邮箱不能为空',
        'user_code.require'  =>  '验证码不能为空',
        'user_email.email'  =>  '邮箱格式错误',
        'user_email.unique'  =>  '邮箱已存在',
        'user_tel.unique'  =>  '手机号已存在',
        'user_pwd.require' =>  '密码不能为空',
        'user_pwd1.require' =>  '确认密码不能为空',
        'user_pwd1.confirm' =>  '确认密码不一致',
    ];
    protected $scene = [
        'email'   =>  ['user_email','user_pwd','user_pwd1','user_code'],
        'tel'  =>  ['user_tel','user_pwd','user_pwd1','user_code'],
    ];
    /** 验证手机  证邮箱唯一 还有要与发送验证码的邮箱一致*/
    public function checkTel($value,$rule,$data){
        $reg ='/^1[3-9]\d{9}$/';
        $user_tel = session('accountInfo.user_tel');
        if (!preg_match($reg,$value)){
            return "手机号格式错误";
        }elseif($value!=$user_tel){
            return "与发送验证码的手机号不一致";
        }else{
            //验证唯一
            $model=model('User');
            $where = [
                'user_tel'=>$value
            ];
            $res = $model->where($where)->find();
            if($res) {
                return "手机号已存在";
            }else{
                return true;
            }

        }



    }
    /** 验证邮箱唯一 还有要与发送验证码的邮箱一致 */
    public function checkEmail($value,$rule,$data){
    $user_email = session('accountInfo.user_email');
        if($value!=$user_email){
            return "与发送验证码的邮箱不一致";
        }else{
            return true;
        }
    }
    /** 验证 验证码 */
    public function checkCode($value,$rule,$data){
        $code = session('accountInfo.code');
        $time = session('accountInfo.time');
        if($value!=$code){
            return "验证码有误";
        }else if(time()-$time>300){
            return "验证码失效";
        }else{
            return true;
        }

    }
    /** 验证密码 */
    public function checkPwd($value,$rule,$data){
        $reg ='/^.{6,16}$/';
        if (!preg_match($reg,$value)){
            return "密码6-16位";
        }else{
            return true;
        }
    }

}