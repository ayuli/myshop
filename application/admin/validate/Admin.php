<?php
namespace app\admin\validate;
use think\Validate;

class Admin extends Validate{
    protected $rule = [
        'admin_name'  =>  'require|checkName',//|unique:admin
        'admin_pwd'  =>  'require|checkPwd',
        'admin_email' =>  'require|email',
        'admin_tel'  =>  'require|checkTel',
    ];

    protected $message = [
        'admin_name.require'  =>  '用户名必填',
        'admin_name.unique'  =>  '账号已存在',
        'admin_pwd.require'  =>  '密码必填',
        'admin_email.require' =>  '邮箱必填',
        'admin_email.email' =>  '邮箱格式错误',
        'admin_tel.require' =>  '手机号必填',
    ];

    /**验证用户名 */
    public function checkName($value,$rule,$data){
        $reg = '/^[a-z_]\w{3,11}$/i';
        if (!preg_match($reg,$value)) {
            return '账号数字字母下划线组成非数字开头4-12';
        }else {
            $model = model('Admin');
            if (empty($data['admin_id'])) {
                $where = [
                    'admin_name'=>$value
                ];
            }else {
                $where = [
                    'admin_id'=>['neq', $data['admin_id']],
                    'admin_name'=>$value
                ];
            }
            $arr = $model->where($where)->find();
            if ($arr) {
                return '账号已存在';
            }else {
                return true;
            }
        }
    }

    /**验证密码 */
    public function checkPwd($value,$rule,$data){
        $reg = '/^.{4,16}$/';
        if (!preg_match($reg,$value)) {
            return '密码允许4-16';
        }else {
            return true;
        }
    }

    /**验证手机号 */
    public function checkTel($value,$rule,$data){
        $reg = '/^1[3-9]\d{9}$/';
        if (!preg_match($reg,$value)) {
            return '请填正确格式的手机号';
        }else {
            return true;
        }
    }
    /*验证场景*/
    protected $scene = [
        'add'   =>  ['admin_name','admin_pwd','admin_email','admin_tel'],
        'edit'  =>  ['admin_name','admin_email','admin_tel'],
    ];

}