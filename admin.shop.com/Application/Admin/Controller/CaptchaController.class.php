<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/11/16
 * Time: 15:44
 */

namespace Admin\Controller;


use Think\Controller;
use Think\Verify;

class CaptchaController extends  Controller
{
    public function show(){
        $config=[
            'fontSize'  =>  18,              // 验证码字体大小(px)
            'useCurve'  =>  false,            // 是否画混淆曲线
            'useNoise'  =>  true,            // 是否添加杂点
            'length'    =>  4,               // 验证码位数
        ];
        $verify=new Verify($config);
        $captcha=$verify->entry();
        return $captcha;
    }
}