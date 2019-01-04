<?php
namespace app\admin\controller;

class Node extends Common
{
    /** 节点添加 */
    public function nodeAdd()
    {
        if(checkRequest()){
            $data = input('post.');
            if(empty($data)){
                fail('操作有误');
            }
            // 验证
            $validate = validate('Node');
            if(!$validate->check($data)){
                fail($validate->getError());
            }
            // 添加入库
            $node_model = model('Node');
            $res = $node_model->save($data);
            if($res){
                successly('添加成功');
            }else{
                fail('添加失败');
            }

        }else{
            // 查询所有顶级节点
            $nodeWhere = [
                'pid'=>0
            ];
            $node_model = model('Node');
            $nodeParent = $node_model->where($nodeWhere)->select();
            $this->assign('nodeParent',$nodeParent);
            return view();
        }
    }
    /** 节点列表 */
    public function nodeList()
    {
        $node_model = model('Node');
        $nodeInfo = $node_model->select();
        $this->assign('nodeInfo',$nodeInfo);
        return view();
    }
    /** 节点删除 */
    public function nodeDel()
    {
        $node_id = input('param.node_id');
        $node = model('Node');
        //查询此分类下是否有子类
        $where = [
            'pid'=>$node_id
        ];
        $count = $node->where($where)->count();
        if ($count>0){
            fail('此分类下有分类或商品信息,不允许删除');
        }
        //查询此分类下是否有商品
        $nodewhere = [
            'node_id'=>$node_id
        ];
        //删除
        $res = $node->where($nodewhere)->delete();
        if ($res){
            successly('删除成功');
        }else{
            fail('删除失败');
        }
    }
    /** 即点即改 */
    public function nodeUpdata()
    {
        $value = input('param.value');//当前状态
        $field = input('param.field');//字段
        $node_id = input('param.node_id');
        $node = model('Node');
        $where = [
            'node_id' => $node_id
        ];
        $data = [
            $field =>$value
        ];
        $result = $node->where($where)->update($data);
        if ($result===false){
            fail('修改失败');
        }else{
            successly('修改成功');
        }
    }
    /** 编辑修改 */
    public function nodeUpdate()
    {
        if (checkRequest()){
            $data = input('param.');
            //验证
            $validate = validate('Node');
            if(!$validate->check($data)){
                fail($validate->getError());
            }
            $where = [
                'node_id'=>$data['node_id']
            ];
            $model = model('Node');
            $res = $model->save($data,$where);
            if ($res!==false){
                successly('修改成功');
            }else{
                fail('修改失败');
            }
        }else{
            $node_id = input('param.node_id');
//            // 表单展示value值
            $model = model('Node');
            $where = [
                'node_id' => $node_id
            ];
            $arr = $model->where($where)->find();
            $this->assign('arr',$arr);
//            //下拉菜单
            $info = $this->getCateInfo();
            $this->assign('info',$info);
            return view();
        }

    }
    /** 获取分类数据 */
    public function getCateInfo()
    {
        $model = model('Node');
        $data = $model->select();
        return $data;
    }
}