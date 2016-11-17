<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/11/15
 * Time: 20:50
 */

namespace Admin\Controller;


use Think\Controller;

class MenuController extends Controller
{
    /**
     * @var \Admin\Model\MenuModel
     */
    private  $_model;
    protected function _initialize(){
        $this->_model=D('Menu');
    }
    /**
     * 菜单列表
     */
    public function index()
    {
        //获取商品信息
        $rows=$this->_model->getList();
        $this->assign('rows',$rows);
        $this->display();
    }
    /**
     * 添加菜单
     */
    public function add()
    {
        if (IS_POST) {
            if ($this->_model->create()===false) {
                $this->error(dealErrorStr($this->_model));
            }
            if ($this->_model->addMenu()===false) {
                $this->error(dealErrorStr($this->_model));
            }
            $this->success('添加成功',U('index'));
        }else{
            $this->_before_view();
            $this->display();
        }
    }
    /**
     * 修改菜单
     */
    public function edit($id)
    {
        if (IS_POST) {
            if ($this->_model->create()===false) {
                $this->error(dealErrorStr($this->_model));
            }
            if ($this->_model->saveMenu($id)===false) {
                $this->error(dealErrorStr($this->_model));
            }
            $this->success('修改成功',U('index'));
        }else{
            //获取数据
            $row=$this->_model->getMenuInfo($id);
            //回显
            $this->assign('row',$row);
            $this->_before_view();
            $this->display('add');
        }
    }
    /**删除菜单
     * @param $id
     */
    public function delete($id)
    {
        if ($this->_model->deleteMenu($id)===false) {
            $this->error(dealErrorStr($this->_model));
        }
        $this->success('删除菜单成功');
    }
    /**
     * 页面加载前渲染页面数据
     */
    public function _before_view()
    {
        //获取所有的权限列表
        $permissions=D('Permission')->getList();
        $this->assign('permissions',json_encode($permissions));
        //获取已有的菜单列表以便设置父级
        $rows=$this->_model->getList();
        array_unshift($rows,['id'=>0,'name'=>'顶级菜单']);
        $this->assign('menus',json_encode($rows));
    }
}