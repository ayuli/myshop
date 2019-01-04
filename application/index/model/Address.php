<?php
/**
 * Created by PhpStorm.
 * User: 小鱼
 * Date: 2018/11/30
 * Time: 16:51
 */

namespace app\index\model;
use think\Model;
class Address extends Model
{
    // 关闭自动写入update_time字段
    protected $updateTime = false;
    //数据完成 当用save时会自动添user_id
    protected $insert = ['user_id'];
    protected function setUserIdAttr(){
        return session('accInfo.user_id');
    }


}