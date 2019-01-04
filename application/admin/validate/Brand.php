<?php
namespace app\admin\validate;
use think\Validate;

class Brand extends Validate
{
    protected $rule = [
        'brand_name'  =>  'require|checkName',
        'brand_url'  =>  'require|checkUrl',
        'brand_logo'  =>  'require',
        'brand_describe'  =>  'require',
    ];
    protected $message = [
        'brand_name.require'  =>  '品牌名称必填',
        'brand_url.require'  =>  '品牌地址必填',
        'brand_describe.require'  =>  '品牌描述必填',
        'brand_logo.require'  =>  '品牌logo必填',
    ];
    public function checkName($value,$rule,$data){
        $model = model('Brand');
        if (empty($data['brand_id'])) {
            $where = [
                'brand_name'=>$value
            ];
        }else {
            $where = [
                'brand_id'=>['neq', $data['brand_id']],
                'brand_name'=>$value
            ];
        }
        $arr = $model->where($where)->find();
        if ($arr) {
            return '品牌名称已存在';
        }else {
            return true;
        }
    }
    public function checkUrl($value,$rule,$data){
        $reg = '/^www\.[a-z0-9]{2,}@\d{2,6}\.com$/i';
        if (!preg_match($reg,$value)) {
            return '请输入正确格式地址';
        }else {
            return true;
        }
    }
    protected $scene = [
        'add'   =>  ['brand_name','brand_url','brand_describe','brand_logo'],
        'edit'  =>  ['brand_name','brand_url','brand_describe','brand_logo'],
    ];

}