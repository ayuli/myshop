<?php
namespace app\index\controller;
use think\Controller;

class Text extends Controller
{
    public function text()
    {
//        echo getRandCode();
         $res = sendEmail('649160717@qq.com','4352423');
         dump($res);
//        $res=sendSms('17788131078','666666');
//        echo $res->Code;
    }
}