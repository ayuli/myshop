<?php
namespace app\admin\controller;

class Brand extends Common
{
    /**品牌添加*/
    public function brandAdd()
    {
        if (checkRequest()){
            $data = input('param.');
            $validate = validate('Brand');
            //使用验证器验证
            if (!$validate->scene('add')->check($data)) {
                fail($validate->getError());
            }

            //添加
            $model = model('Brand');
            $res = $model->allowField(true)->save($data);
            if ($res){
                successly('添加成功');
            }else{
                fail('添加失败');
            }
        }else{
            return view();
        }
    }
    /**品牌展示 */
    public function brandList()
    {
        return view();
    }
    /**品牌logo*/
    public function brandLogo()
    {
        // 获取表单上传文件 例如上传了001.jpg
        $file = request()->file('file');
        // 移动到框架应用根目录/public/uploads/ 目录下
        $info = $file->move(ROOT_PATH . 'public' . DS . 'brandlogo');
        if($info){
        // 成功上传后 获取上传信息
            $arr = [
                'code'=>1,
                'font'=>'上传成功',
                'src'=>$info->getSaveName()
            ];
                echo json_encode($arr);exit;
        }else{
        // 上传失败获取错误信息
            echo $file->getError();
        }
    }
    /**展示数据*/
    public function brandInfo()
    {
        $model = model('Brand');
        $page = input('get.page');
        $limit = input('get.limit');
        $count = $model->count();
        $data = $model->page($page,$limit)->select();
        $info = ['code'=>0,'msg'=>'','count'=>$count,'data'=>$data];
        echo json_encode($info);
    }
    /** 即点即改*/
    public function brandUpdata()
    {
        $brand_id = input('param.brand_id');//修改的id
        $value = input('param.value');//修改后的值
        $field = input('param.field');//当前修改的字段
        $where = [
            'brand_id'=>$brand_id
        ];
        $update = [
            $field=>$value
        ];
        $model = model('Brand');
        $res = $model->save($update,$where);
        if($res){
            successly('修改成功');
        }else{
            fail('修改失败');
        }
    }
    /**删除*/
    public function brandDel()
    {
        $brand_id = input('param.brand_id');
        $model = model('Brand');
        $res = $model->destroy($brand_id);
        if ($res){
            successly('删除成功');
        }else{
            fail('删除失败');
        }
    }
    /**编辑修改*/
    public  function brandUpdate()
    {
        if (checkRequest()){
            $data = input('param.');
            $brand_id = input('param.brand_id');
            $model = model('Brand');
            $validate = validate('Brand');
            //验证器
            if (!$validate->scene('edit')->check($data)) {
                fail($validate->getError());
            }
            $where = [
                'brand_id'=>$brand_id
            ];
            $res = $model->allowField(true)->save($data,$where);
            if ($res===false) {
                fail('修改失败');
            }else {
                successly('修改成功');
            }
        }else{
            $brand_id = input('param.brand_id');
            $model = model('Brand');
            $where = [
                'brand_id'=>$brand_id
            ];
            $data = $model->where($where)->find();
            $this->assign('data',$data);
            return view();
        }

    }
}