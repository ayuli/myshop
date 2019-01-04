<?php
namespace app\admin\controller;

class Admin extends Common
{
    /*管理员添加*/
    public function adminAdd()
    {
        if (checkRequest()) {
            $data = input('param.');
            $validate = validate('Admin');
            //验证器
            if (!$validate->scene('add')->check($data)) {
                fail($validate->getError());
            }

           /*保存数据*/
            $model = model('Admin');
            $result = $model->save($data);
            if ($result) {
                successly('添加成功');
            }else {
                fail('添加失败');
            }

        }else {
            return view();
        }
    }
    /**唯一 */
    public function checkName()
    {
        $admin_name = input('param.admin_name');
        $type = input('param.type');
        //处理条件
        if ($type==1) {
            $where = [
                'admin_name'=>$admin_name
            ];
        }else {
            $old_name = input('param.old_name');
            $where = "admin_name != '$old_name' and admin_name = '$admin_name'";
        }

        $model = model('Admin')->where($where)->find();
        if ($model) {
            echo "no";
        }else {
            echo "yes";
        }
    }
    /**管理员展示 */
    public function adminList()
    {
        return view(); 
    }
    /**获取管理员的数据 */
    public function getAdminInfo()
    {
        $model = model('Admin');
        $page = input('param.page');
        $limit = input('param.limit');
        $data = $model->page($page,$limit)->select();
        $count = $model->count();
        $info = ['code'=>0,'msg'=>'','count'=>$count,'data'=>$data];
        echo json_encode($info);
    }
    /**控制器执行修改 */
    //即点即改
    public function adminUpdata()
    {
        $data = input('param.');
        //拼接条件 根据id修改数据
        $where = [
            'admin_id'=>$data['admin_id']
        ];
        //设置数据
        $info = [
            $data['field']=>$data['value']
        ];
        //修改
        $res = model('Admin')->where($where)->update($info);
        if ($res) {
            successly('修改成功');
        }else {
            fail('修改失败');
        }
    }
    /**管理员的删除 */
    public function adminDel()
    {
        $admin_id = input('param.admin_id');
        $where = [
            'admin_id'=>$admin_id
        ];
        $res = model('Admin')->where($where)->delete();
        if ($res) {
            successly('删除成功');
        }else {
            fail('删除失败');
        }
    }
    /**管理员修改 */
    public function adminUpdate()
    {
        if (checkRequest()) {
            $data = input('param.');
            $admin_id = input('param.admin_id');
            $validate = validate('Admin');
            //验证器
            if (!$validate->scene('edit')->check($data)) {
                fail($validate->getError());
            }
            // dump($data);die;
            $where = [
                'admin_id'=>$admin_id
            ];
            $model = model('Admin');
            $res = $model->save($data,$where);
            if ($res===false) {
                fail('修改失败');
            }else {
                successly('修改成功');
            }
//
        }
            else {
            $admin_id = input('param.admin_id');
            $model = model('Admin');
            $where = [
                'admin_id'=>$admin_id
            ];
            $data = $model->where($where)->find();
            $this->assign('data',$data);
            return view();
        }
    }
    /** 修改密码 */
    public function updatePwd(){
        if (checkRequest()){
            $old_pwd = input('post.old_pwd');
            $new_pwd1 = input('post.new_pwd1');
            $new_pwd2 = input('post.new_pwd2');

            $model = model('Admin');
            $admin_id = session('adminInfo.admin_id');// 从session里取出id
            $where = [
                'admin_id'=>$admin_id
            ];
            //验证密码
            if (empty($old_pwd)){
                fail('原密码不能为空');
            }else{
                $arr = $model->where($where)->find()->toArray();
                $createPwd = createPwd($old_pwd,$arr['salt']);
                if ($createPwd!=$arr['admin_pwd']){
                    fail('原密码有误请重新输入');
                }
            }
            if (empty($new_pwd1)){
                fail('新密码不能为空');
            }else if(strlen($new_pwd1)<6){
                fail('新密码最少6位');
            }

            if(empty($new_pwd2)){
                fail('请确认密码');
            }else if($new_pwd1!=$new_pwd2){
                fail('请确认密码与新密码一致');
            }else if($new_pwd2==$old_pwd){
                fail('不能与原密码一致');
            }
            //修改密码
            $updateInfo = [
                'admin_pwd'=>createPwd($new_pwd2,$arr['salt'])
            ];
            $res = $model->where($where)->update($updateInfo);
            if ($res){
                successly('修改成功');
            }else{
                fail('修改失败');
            }
//            echo $old_pwd;echo $new_pwd1;echo $new_pwd2;die;
        }else{
            return view();
        }
    }
    /** 赋予角色  */
    public function adminGiverole()
    {
        if(checkRequest()){
            $data = input('post.');
            try{
                if(empty($data)){
                    throw new \Exception('无数据');
                }
                $adminRoleModel = model('AdminRole');
                //删除派生表
                $where = [
                    'admin_id'=>$data['admin_id']
                ];
                $adminRoleModel->startTrans();
                $res = $adminRoleModel->where($where)->delete();
                if($res===false){
                    $adminRoleModel->rollback();
                    throw new \Exception('操作有误');
                }
                // 添加
                $insertData = [];
                foreach($data['role_id'] as $k=>$v){
                    $insertData[] = [
                        'admin_id'=>$data['admin_id'],
                        'role_id'=>$v
                    ];
                }
                $res1 = $adminRoleModel->saveAll($insertData);
                if($res1===false){
                    $adminRoleModel->rollback();
                    throw new \Exception('操作有误');
                }
                $adminRoleModel->commit();
                successly('操作成功');
            }catch(\Exeption $e){
                fail($e->getMessage());
            }
            print_r($data);
        }else{
            $admin_id = input('get.admin_id');
            $roleModel = model('Role');
            $roleInfo = $roleModel->select();
            $this->assign('roleInfo',$roleInfo);
            $this->assign('admin_id',$admin_id);
            //默认选中
            $adminRoleModel = model('AdminRole');
            $where = [
                'admin_id'=>$admin_id
            ];
            $adminRole = $adminRoleModel->where($where)->column('role_id');
            $this->assign('adminRole',$adminRole);
            return view();
        }
    }
}