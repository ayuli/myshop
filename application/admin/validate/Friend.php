<?php
namespace app\admin\validate;
use think\Validate;

class Friend extends Validate
{
    protected $rule = [
        'friend_name'  =>  'require|checkName',
        'friend_url' =>  'require|checkUrl',
    ];

    protected $message = [
        'friend_name.require'  =>  '友链名称必填',
        'friend_url.require' =>  '友链网址必填',
    ];

    public function checkName($value,$rule,$data){
        $model = model('Friend');
        if (empty($data['friend_id'])) {
            $where = [
                'friend_name'=>$value
            ];
        }else {
            $where = [
                'friend_id'=>['neq', $data['friend_id']],
                'friend_name'=>$value
            ];
        }
        $arr = $model->where($where)->find();
        if ($arr) {
            return '友链名称已存在';
        }else {
            return true;
        }
    }
    //验证友链地址
    public function checkUrl($value,$rule,$data){
        $reg = '/^www\.[a-z0-9]{2,}\.com$/i';
        if (!preg_match($reg,$value)) {
            return '请输入正确格式地址';
        }else {
            return true;
        }
    }

}