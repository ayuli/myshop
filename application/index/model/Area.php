<?php
namespace app\index\model;
use think\Model;
class Area extends Model
{
    // 关闭自动写入update_time字段
    protected $updateTime = false;
    // 关闭自动写入时间戳
    protected $autoWriteTimestamp = false;

}