<?php
namespace app\index\validate;
use think\Validate;

class Address extends Validate
{
    protected $rule = [
        'province'  =>  'require',
        'city'  =>  'require',
        'district'  =>  'require',
        'consignee_name'  =>  'require',
        'email'  =>  'require|email',
        'detailed_address'  =>  'require',
        'consignee_tel'  =>  'require|checkTel',
    ];

    protected $message = [
        'province.require'  =>  '配送地区请填写完整',
        'city.require'  =>  '配送地区请填写完整',
        'district.require'  =>  '配送地区请填写完整',
        'consignee_name.require'  =>  '收货人姓名必填',
        'email.require'  =>  '电子邮箱必填',
        'email.email'  =>  '电子邮箱格式有误',
        'detailed_address.require'  =>  '详细地址必填',
        'consignee_tel.require'  =>  '手机号必填',
    ];
    protected $scene = [
        'email'   =>  ['user_email','user_pwd','user_pwd1','user_code'],
        'tel'  =>  ['user_tel','user_pwd','user_pwd1','user_code'],
    ];

    public function checkTel($value,$rule,$data){
        $reg ='/^1[3-9]\d{9}$/';
        if (!preg_match($reg,$value)){
            return "手机号格式错误";
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