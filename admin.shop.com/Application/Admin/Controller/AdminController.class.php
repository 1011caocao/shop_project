<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/11/10
 * Time: 16:54
 */

namespace Admin\Controller;


use Think\Controller;
use Think\Verify;

class AdminController extends Controller
{
    /**
     * @var \Admin\Model\AdminModel
     *
     */
    private $_model;
    protected function _initialize(){
        $this->_model=D('Admin');
    }
    public function index()
    {
        //搜索字符串的获取
        $keyword=I("get.name");
        $condition=[];
        if($keyword){
            $condition['name']=['like','%'.$keyword.'%'];
        }
        //获取分页数据
        $data=$this->_model->getPage($condition);
        //返回到页面
        $this->assign($data);
        $this->display();
    }
    /**
     * 后台登录
     */
    public function login() {
        if (IS_POST) {
//            //验证码验证
//            $code=I('post.captcha');
//            $verify=new Verify();
//            if ($verify->check($code)===false) {
//                $this->error('验证码错误',U('login'));
//            }
            if ($this->_model->create()===false) {
                $this->error(dealErrorStr($this->_model));
            }
            if (($result=$this->_model->login())===false) {
                $this->error(dealErrorStr($this->_model));
            }
            $this->success("登录成功",U('Index/index'));
        }else{
            $this->display('login');
        }

    }
    /**
     * 添加管理员
     */
    public function add()
    {
        if (IS_POST) {
            if ($this->_model->create()===false) {
                $this->error($this->_model->getError());
            }
            if ($this->_model->addAdmin()===false) {
                $this->error($this->_model->getError());
            }
            $this->success("添加成功",U('index'));
        }else{
            //渲染页面
            $this->_before_view();
            $this->display();
        }
    }
    /**
     * 修改管理员
     * @param $id
     */
    public function edit($id){
        if (IS_POST) {
            if ($this->_model->create()===false) {

                $this->error(dealErrorStr($this->_model));
            }
            if ($this->_model->saveAdmin($id)===false) {

                $this->error(dealErrorStr($this->_model));
            }
            $this->success('修改管理员成功',U('index'));
        }else{
            //渲染页面
            $this->_before_view();
            //获取所有的管理员
            $row=$this->_model->getAdminMsg($id);
            $this->assign('row',$row);
            $this->display('add');
        }
    }

    /**
     * 删除管理员
     * @param $id
     */
    public function delete($id)
    {
        if ($this->_model->deleteAdmin($id)===false) {
            $this->error(dealErrorStr($this->_model));
        }
        $this->success('删除管理员成功',U('index'));
    }
    /**
     * 注销登录
     */
    public function logout()
    {
        session(null);
        cookie('AUTO_LOGIN_TOKEN',null);
        $this->success('退出成功', U('login'));
    }

    /**
     * 在渲染页面前，发送一些数据
     */
    public function _before_view()
    {
        //获取所有的角色
        $roles=D('Role')->getList();
        $this->assign('roles',json_encode($roles));
    }
}