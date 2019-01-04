<?php
namespace app\admin\controller;

class Role extends Common
{
    /** 角色添加 */
    public function roleAdd()
    {
        if(checkRequest()){
            $data = input('post.');
            // 添加角色
            model('Role')->allowField(true)->save($data);
            $role_id = model('Role')->role_id;
            foreach($data['node_id'] as $k=>$v){
                $info[] = ['role_id'=>$role_id,'node_id'=>$v];
            }
            $res = model('RoleNode')->saveAll($info);
            if($res){
                successly('添加成功');
            }else{
                fail('添加失败');
            }

        }else{
            $nodeInfo = model('Node')->where(['pid'=>0])->select();
            foreach($nodeInfo as $k=>$v){
                $where = [
                    'pid'=>$v['node_id']
                ];
                $nodeInfo[$k]['son']= model('Node')->where($where)->select();
            }
            $this->assign('nodeInfo',$nodeInfo);
            return view();
        }


    }

    /** 角色列表 */
    public function roleList()
    {
        $role_model = model('Role');
        $roleInfo = $role_model->select();
        $this->assign('roleInfo',$roleInfo);
        return view();
    }

    /** 角色删除 */
    public function roleDel()
    {
        $role_id = input('post.role_id',0,'intval');
        try{
            if(empty($role_id)){
                throw new \Exception('没有要删除的数据');
            }
            $role_model = model('Role');
            $role_model->startTrans();//开启事务

            $res = $role_model->where(['role_id'=>$role_id])->delete();
            if($res===false){
                $role_model->rollback();
                throw new \Exception('删除失败');
            }
            // 删除派生表的信息
            $roleNodeModel = model('RoleNode');
            $res1 = $roleNodeModel->where(['role_id'=>$role_id])->delete();
            if($res1===false){
                $role_model->rollback();
                throw new \Exception('删除失败');
            }
            $role_model->commit();
            successly('删除成功');

        }catch(\Exeption $e){
            fail($e->getMessage());
        }
    }

    /** 角色修改 */
    public function roleUpdate()
    {
        if(checkRequest()){
            $data = input('post.');
            if(empty($data['node_id'])){
                fail('权限不能为空哦!');
            }
//            print_r($data);die;
            $where = [
                'role_id'=>$data['role_id']
            ];
            $roleMode = model('Role');
            $roleMode->startTrans();
            // 修改角色表
            try{

                $res = $roleMode->allowField(true)->where($where)->update(['role_name'=>$data['role_name']]);
                if($res===false){
                    $roleMode->rollback();
                    throw new \Exception('数据异常');
                }

                // 删除角色——节点 派生表
                $roleNodeModel = model('RoleNode');
                $res1 = $roleNodeModel->where($where)->delete();
                if($res1===false){
                    $roleMode->rollback();
                    throw new \Exception('修改失败');
                }
                // 添加
                foreach($data['node_id'] as $k=>$v){
                    $info[] = ['role_id'=>$data['role_id'],'node_id'=>$v];
                }
                $res2 = model('RoleNode')->saveAll($info);
                if($res2===false){
                    $roleMode->rollback();
                    throw new \Exception('修改失败');
                }
                $roleMode->commit();
                successly('修改成功');

            }catch(\Exeption $e){
                fail($e->getMessage());
            }

        }else{
            $role_id = input('get.role_id',0,'intval');
            try{
                if(empty($role_id)){
                    throw new \Exception('无角色修改');
                }
                $role_Model = model('Role');

                $where = [
                    'role_id'=>$role_id
                ];
                $role_Model->startTrans();// 开启事务
                $roleInfo = $role_Model->where($where)->find();
                if(empty($roleInfo)){
                    throw new \Exception('无数据');
                }
                //角色 一维
                $this->assign('roleInfo',$roleInfo);

                $nodeInfo = model('Node')->where(['pid'=>0])->select();
                foreach($nodeInfo as $k=>$v){
                    $where = [
                        'pid'=>$v['node_id']
                    ];
                    $nodeInfo[$k]['son']= model('Node')->where($where)->select();
                }
                // 权限 二维
                $this->assign('nodeInfo',$nodeInfo);

                //if 判断默认选中
                $roleWhere = [
                    'role_id'=>$role_id
                ];
                $roleNodeInfo = model('RoleNode')->where($roleWhere)->column('node_id');
//                print_r($roleNodeInfo);die;
                // 根据角色ID 角色-权限派生表
                $this->assign('roleNodeInfo',$roleNodeInfo);
                $role_Model->commit();
                return view();
            }catch(\Exeption $e){
                fail($e->getMessage());
            }
        }


    }

    /** 获取分类数据 */
    public function getNodeInfo()
    {
        $model = model('Node');
        $data = $model->select();
        return $data;
    }

}