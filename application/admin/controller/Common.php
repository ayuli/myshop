<?php
namespace app\admin\controller;
use think\Controller;
use think\Request;

class Common extends Controller
{
    public function __construct(Request $request = null)
    {
        parent::__construct($request);
        if (!session('?adminInfo'))
        {
            $this->error('请先登录','Login/login');
        }
        $node_model = $this->getNode();
        $aaainfo = getNodeInfo($node_model);
        $this->assign('aaainfo',$aaainfo);

        foreach($node_model as $k=>$v){
            $node_id[] = $v['node_id'];
        }
//        print_r($node_id);die;
        $node_model = model('Node');
        $nodeWhere = [
            'controller_name'=>request()->controller(),
            'action_name'=>request()->action()
        ];
        $my_node_id = $node_model->where($nodeWhere)->value('node_id');
//        print_r($my_node_id);die;
        if(!in_array($my_node_id,$node_id)){
            $this->error('权限不够',url('index/index'));
        }
    }
    /** 查询左侧节点信息 */
    public function getNode()
    {
        // 条件
        $admin_id = session('adminInfo.admin_id');
        $where = [
            'admin_id'=>$admin_id,
        ];

        // 查询左侧节点信息
        $node_model = model('Node')
            ->alias('n')
            ->join('shop_role_node r_n','n.node_id=r_n.node_id')
            ->join('shop_role r','r_n.role_id=r.role_id')
            ->join('shop_admin_role a_r','r.role_id=a_r.role_id')
            ->where($where)
            ->select();

        return $node_model;
//        print_r(collection($info)->toArray());exit;
    }
}