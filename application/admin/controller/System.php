<?php
namespace app\admin\controller;

use phpDocumentor\Reflection\DocBlock\Type\Collection;

class System extends Common
{
    public function system()
    {
        if (checkRequest()){
            $data = input('post.');
            //验证
            $validate = validate('System');
            if (!$validate->check($data)) {
                fail($validate->getError());
            }

            foreach($data as $k=>$v){
                $info[] = ['name'=>$k,'value'=>$v];
            }
            $model = model('Config');
            $model->query('delete from shop_config');
            $res = $model->saveAll($info);
            if($res){
                //清除session
                session('configInfo',null);
                successly('添加成功');
            }else{
                fail('添加失败');
            }
        }else{
            $model = model('Config');
            $data = $model->select();
            if (!empty($data)){
                foreach($data as $k=>$v){
                    $info[$v['name']]=$v['value'];
                }
                $this->assign('info',$info);
            }

            return view();
        }
    }
}