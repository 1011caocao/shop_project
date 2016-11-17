<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/11/12
 * Time: 20:52
 */

namespace Common\Behaviors;


use Think\Behavior;

class CheckPermissionBehavior extends Behavior
{
        public function run(&$param){
        //执行逻辑
        //排除不需要验证的页面 login  verify
        $ignores=C('RBAC.IGNORES');
        //获取当前的路径
        $url=MODULE_NAME.'/'.CONTROLLER_NAME.'/'.ACTION_NAME;
        if(in_array($url,$ignores)){
            return true;
        }
        //检查登录 如股票session中没有值,尝试自动登录
        if (!$admin=session('ADMIN')) {
            if (!$admin=(D('Admin')->autoLogin())) {
                //重定向
                redirect(U('Admin/login'));
            }
        }
        //忽略已登录的路径
         $user_ignores=C('RBAC.USER_IGNORES');
         if(in_array($url,$user_ignores)){
                return true;
         }
         //忽略超级管理员
         if ($admin['username']=='admin') {
            return true;
         }
         //检查RBAC权限
         //判断当前的用户权限 能否进入当前的路径
         $pathes=session('ADMIN_PATH');
         var_dump($pathes);
         if (in_array($url,$pathes)) {
             return true;
         }else{
             echo '<script type="text/javascript">alert("无权访问");history.back();</script>';
             exit;
         }

        return true;
    }

}