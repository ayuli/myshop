<?php
/**
 * Created by PhpStorm.
 * User: 小鱼
 * Date: 2018/12/14
 * Time: 14:18
 */

namespace app\index\model;
use think\Model;

class OrderAddress extends Model
{
    // 关闭自动写入update_time字段
    protected $updateTime = false;
}