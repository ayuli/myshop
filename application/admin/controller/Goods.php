<?php
namespace app\admin\controller;

class Goods extends Common
{
    /*商品添加*/
    public function goodsAdd()
    {
        if (checkRequest()){
            $data = input('post.');
            $model = model('Goods');
            //验证
            $validate = validate('Goods');
            if (!$validate->check($data)) {
                fail($validate->getError());
            }

            $res = $model->allowField(true)->save($data);
            if ($res){
                successly('添加成功');
            }else{
                fail('添加失败');
            }
        }else{
            //获取分类数据
            $info = $this->getCateInfo();
            $this->assign('info',$info);
            //获取品牌数据
            $model = model('Brand');
            $arr = $model->select();
            $this->assign('arr',$arr);
            return view();
        }
    }
    /** 富文本编辑器上传 */
    public function goodsEditImgs()
    {
        // 获取表单上传文件 例如上传了001.jpg
        $file = request()->file('file');
        // 移动到框架应用根目录/public/uploads/ 目录下
        $info = $file->move(ROOT_PATH . 'public' . DS . 'goodseditimgs');
        if($info){
            // 成功上传后 获取上传信息
            $arr = [
                'code'=>0,
                'font'=>'上传成功',
                'data'=>[
                    'src'=>"http://www.myshop.com/public/goodseditimgs/".$info->getSaveName(),
                    'title'=>'a'
                ]
            ];
            echo json_encode($arr);exit;
        }else{
            // 上传失败获取错误信息
            echo $file->getError();
        }
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
    /**商品图片上传*/
    public function goodsUpload()
    {
        $type = input('get.type');
        $dir = $type==1?'goodsimg':'goodsimgs';
        // 获取表单上传文件 例如上传了001.jpg
        $file = request()->file('file');
        // 移动到框架应用根目录/public/uploads/ 目录下
        $info = $file->move(ROOT_PATH . 'public' . DS . $dir);
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
    /**商品展示 */
    public function goodsList()
    {
        //获取分类数据
        $cate = $this->getCateInfo();
        $this->assign('cate',$cate);
        //获取品牌数据
        $model = model('Brand');
        $brand = $model->select();
        $this->assign('brand',$brand);
        return view();
    }
    //获取管理员的数据
    public function getGoodsList()
    {
        $goods_name = input('param.goods_name');
        $cate_name = input('param.cate_name');
        $brand_name = input('param.brand_name');
        $where=[];
        if (!empty($goods_name)){
            $where['goods_name'] = ['like',"%$goods_name%"];
        }
        if(!empty($cate_name)){
            $where['cate_name'] = $cate_name;
        }
        if(!empty($brand_name)){
            $where['brand_name'] = $brand_name;
        }
//        print_r($where);die;
        $model = model('Goods');
        $page = input('param.page');
        $limit = input('param.limit');
        $count=$model->getCountInfo($model,$where);
        $data = $model->getcliInfo($model,$page,$limit,$where);
        $info = [
                'code'=>0,
                'msg'=>'',
                'count'=>$count,
                'data'=>$data
        ];
        echo json_encode($info);
    }
    /** 即点即改 */
    public function goodsUpdata()
    {
        $data = input('param.');
        //根据id修改值
        $where = [
            'goods_id'=>$data['goods_id']
        ];
        $info = [
            $data['field'] =>$data['value']
        ];
        //修改
        $model = model('Goods');
        $res = $model->save($info,$where);
        if ($res){
            successly('修改成功');
        }else{
            fail('修改失败');
        }
    }
    /** 删除 */
    public function goodsDel()
    {
        $goods_id = input('param.goods_id');
        $res = model('Goods')->destroy($goods_id);
        if ($res){
            successly('删除成功');
        }else{
            fail('删除失败');
        }
    }
    /** 编辑修改 */
    public function goodsUpdate()
    {
        if(checkRequest()){
            //接收数据
            $data = input('param.');
//            dump($data);die;
            $data['is_new']=empty($data['is_new'])?2:$data['is_new'];
            $data['is_hot']=empty($data['is_hot'])?2:$data['is_hot'];
            $data['is_best']=empty($data['is_best'])?2:$data['is_best'];
//            dump($data);die;
            $goods_id = input('param.goods_id');
            //验证
            $validate = validate('Goods');
            if (!$validate->check($data)) {
                fail($validate->getError());
            }
            //根据id修改
            $res = model('Goods')->allowField(true)->save($data,$goods_id);
            if($res!==false){
                successly('修改成功');
            }else{
                fail('修改失败');
            }
        }else{
            //根据id查询一条数组展示到修改value值里
            $goods_id = input('get.goods_id');
            //查询
            $ali = model('Goods')->get($goods_id);
            $this->assign('ali',$ali);
            //获取分类数据
            $info = $this->getCateInfo();
            $this->assign('info',$info);
            //获取品牌数据
            $model = model('Brand');
            $arr = $model->select();
            $this->assign('arr',$arr);
            return view();
        }

    }
}