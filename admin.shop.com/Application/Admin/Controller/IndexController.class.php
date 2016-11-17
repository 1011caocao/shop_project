<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/11/15
 * Time: 10:48
 */

namespace Admin\Controller;


use Think\Controller;

class IndexController extends Controller
{
    public function index(){
        $this->display("index");
    }
    public function top(){
        $this->display("top");
    }
    public function main(){
        $this->display("main");
    }
    public function menu(){
        $rows=D('Menu')->getVisableMenu();
        $this->assign('rows',$rows);
        $this->display("menu");
    }

}