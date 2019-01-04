<?php
namespace app\admin\model;
use think\Model;

class Admin extends Model{
    protected $salt;
    //数据完成
    protected $insert = ['salt'];
    // 关闭自动写入update_time字段
    protected $updateTime = false;
    //修改器修改密码
    public function setAdminPwdAttr($value)
    {
        $this->salt = $salt = createSalt();//函数
        $newPwd = createPwd($value,$salt);
        return $newPwd;
    }
    /**数据完成-盐值 */
    public function setSaltAttr(){
        return $this->salt;
    }

}