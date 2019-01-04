<?php
namespace app\admin\validate;
use think\Validate;

class Login extends Validate{
    protected $rule = [
        'admin_name'  =>  'require',
        'admin_pwd'  =>  'require',
    ];

    protected $message = [
        'admin_name.require'  =>  '用户名必填',
        'admin_pwd.require'  =>  '密码必填',
    ];
        
}