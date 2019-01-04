<?php
namespace app\admin\validate;
use think\Validate;

class Cate extends Validate
{
    protected $rule = [
        'cate_name'  =>  'require|checkName',
    ];

    protected $message = [
        'cate_name.require'  =>  '分类名称必填',
    ];
    public function checkName($value,$rule,$data){
        $model = model('Cate');
        if (empty($data['cate_id'])) {
            $where = [
                'cate_name'=>$value
            ];
        }else {
            $where = [
                'cate_id'=>['neq', $data['cate_id']],
                'cate_name'=>$value
            ];
        }
        $arr = $model->where($where)->find();
        if ($arr) {
            return '分类已存在';
        }else {
            return true;
        }
    }

}