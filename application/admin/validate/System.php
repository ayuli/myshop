<?php
namespace app\admin\validate;
use think\Validate;

class System extends Validate
{
    protected $rule = [
        'WEB_NAME'  =>  'require',
        'WEB_URL'  =>  'require|checkUrl',
        'WEB_COPYRIGHT'  =>  'require',
        'WEB_RECORD' =>  'require',
    ];

    protected $message = [
        'WEB_NAME.require'  =>  '网站名称必填',
        'WEB_URL.require'  =>  '网站地址必填',
        'WEB_COPYRIGHT.require'  =>  '网站版权必填',
        'WEB_RECORD.require'  =>  '网站备案号必填',
    ];
    public function checkUrl($value,$rule,$data){
        $reg = '/^www\.[a-z0-9]{2,}\.com$/i';
        if (!preg_match($reg,$value)) {
            return '请输入正确格式地址';
        }else {
            return true;
        }
    }

}