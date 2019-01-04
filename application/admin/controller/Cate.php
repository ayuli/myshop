<?php
namespace app\admin\controller;


class Cate extends Common
{
    /** 前端验证唯一 */
    public function checkName()
    {
        $cate_name = input('param.cate_name');
        $type = input('param.type');
        if ($type==1){
            $where = [
                'cate_name'=>$cate_name
            ];
        }else{
            $cate_id = input('param.cate_id');
            $where = "cate_id != '$cate_id' and cate_name = '$cate_name'";
        }

        $arr = model('Cate')->where($where)->find();
        if($arr){
            echo "no";
        }else {
            echo "yes";
        }
    }
    /*分类添加*/
    public function cateAdd()
    {
        if (checkRequest()){ // checkRequest()检测是否是ajax执行添加修改
            $data = input('param.');
            //验证
            $validate = validate('Cate');
            if (!$validate->check($data)) {
                fail($validate->getError());
            }
            //分类添加
            $model = model('Cate');
            $res = $model->save($data);
            if ($res){
                successly('添加成功');
            }else{
                fail('添加失败');
            }
        }else{
            //展示页面
            $info = $this->getCateInfo();
            $this->assign('info',$info);
            return view();
        }
    }
    /**分类展示 */
    public function cateList()
    {
        $info = $this->getCateInfo();
        $this->assign('info',$info);
        return view();
    }
    /** 获取分类数据 */
    public function getCateInfo()
    {
        $model = model('Cate');
        $data = $model->select();
//        print_r($data);exit;
        $info = getCateInfo($data);
        return $info;
    }
    /** 分类删除 */
    public function cateDel()
    {
        $cate_id = input('param.cate_id');
        $cate = model('Cate');
        //查询此分类下是否有子类
        $where = [
            'pid'=>$cate_id
        ];
        $count = $cate->where($where)->count();
        if ($count>0){
            fail('此分类下有分类或商品信息,不允许删除');
        }
//        echo $count;exit;
        //查询此分类下是否有商品
        $goods = model('Goods');
        $goodswhere = [
            'cate_id'=>$cate_id
        ];
        $goodscount = $goods->where($goodswhere)->count();
        if ($goodscount>0){
            fail('此分类下有分类或商品信息,不允许删除');
        }
        //删除
        $res = $cate->where($goodswhere)->delete();
        if ($res){
            successly('删除成功');
        }else{
            fail('删除失败');
        }
    }
    /** 即点即改 */
    public function cateUpdata()
    {
        $value = input('param.value');//当前状态
        $field = input('param.field');//字段
        $cate_id = input('param.cate_id');
        $cate = model('Cate');
        $where = [
            'cate_id' => $cate_id
        ];
        $data = [
            $field =>$value
        ];
        $result = $cate->where($where)->update($data);
        if ($result===false){
            fail('修改失败');
        }else{
            successly('修改成功');
        }
    }
    /** 编辑修改 */
    public function cateUpdate()
    {
        if (checkRequest()){
            $data = input('param.');
            //验证
            $validate = validate('Cate');
            if (!$validate->check($data)) {
                fail($validate->getError());
            }
            $where = [
                'cate_id'=>$data['cate_id']
            ];
            $model = model('Cate');
            $res = $model->save($data,$where);
            if ($res){
                successly('修改成功');
            }else{
                fail('修改失败');
            }
        }else{
            $cate_id = input('param.cate_id');
            // 表单展示value值
            $model = model('Cate');
            $where = [
                'cate_id' => $cate_id
            ];
            $arr = $model->where($where)->find();
            $this->assign('arr',$arr);
            //下拉菜单
            $info = $this->getCateInfo();
            $this->assign('info',$info);
            return view();
        }

    }
}