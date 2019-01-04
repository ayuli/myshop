<?php
namespace app\index\model;
use think\Model;
class Goods extends Model
{
    // 关闭自动写入update_time字段
    protected $updateTime = false;
}