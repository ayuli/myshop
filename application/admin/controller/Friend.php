<?php
namespace app\admin\controller;

class Friend extends Common
{
    /*友链添加*/
    public function friendAdd()
    {
        if (checkRequest()){
            $data = input('param.');
            //验证器
            $validate = validate('Friend');
            if (!$validate->check($data)) {
                fail($validate->getError());
            }
            $model = model('Friend');
            $arr = $model->save($data);
            if ($arr){
                successly('添加成功');
            }else{
                fail('添加失败');
            }
        }else{
            return view();
        }

    }
    /*友链展示*/
    public function friendList()
    {
        return view();
    }
    /*友链展示数据*/
    public function friendInfo(){
        $model = model('Friend');
        $page = input('param.page');
        $limit = input('param.limit');
        $data = $model->page($page,$limit)->select();
        $count = $model->count();
        $info = ['code'=>0,'msg'=>'','count'=>$count,'data'=>$data];
        echo json_encode($info);
    }
    //即点即改
    public function friendUpdata()
    {
        $data = input('param.');
        //拼接条件 根据id修改数据
        $where = [
            'friend_id'=>$data['friend_id']
        ];
        //设置数据
        $info = [
            $data['field']=>$data['value']
        ];
        //修改
        $res = model('Friend')->where($where)->update($info);
        if ($res) {
            successly('修改成功');
        }else {
            fail('修改失败');
        }
    }
    /**管理员的删除 */
    public function friendDel()
    {
        $friend_id = input('param.friend_id');
        $where = [
            'friend_id'=>$friend_id
        ];
        $res = model('Friend')->where($where)->delete();
        if ($res) {
            successly('删除成功');
        }else {
            fail('删除失败');
        }
    }
    /**管理员修改 */
    public function friendUpdate()
    {
        if (checkRequest()) {
            $data = input('param.');
//            dump($data);exit;
            $friend_id = input('param.friend_id');
            $validate = validate('Friend');
            //验证器
            if (!$validate->check($data)) {
                fail($validate->getError());
            }
            $where = [
                'friend_id'=>$friend_id
            ];
            $model = model('Friend');
            $res = $model->allowField(true)->save($data,$where);
            if ($res===false) {
                fail('修改失败');
            }else {
                successly('修改成功');
            }

        }else {
            $friend_id = input('param.friend_id');
            $model = model('Friend');
            $where = [
                'friend_id'=>$friend_id
            ];
            $data = $model->where($where)->find();
            $this->assign('data',$data);
            return view();
        }
    }
}