<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/11/13
 * Time: 11:44
 */

namespace Admin\Controller;


use Think\Controller;

class PermissionController extends Controller
{
    /**
     * @var \Admin\Model\PermissionModel
     */
    private $_model;
    protected function  _initialize(){
        $this->_model=D('Permission');
    }

    /**
     * 权限列表
     */
    public function index()
    {
        $rows=$this->_model->getList();
        $this->assign('rows',$rows);
        $this->display();
    }

    /**
     * 添加权限
     */
    public function add()
    {
        if (IS_POST) {
            //搜集数据
            if ($this->_model->create()===false) {
                $this->error(dealErrorStr($this->_model));
            }
            if ($this->_model->addPermission()===false) {
                $this->error(dealErrorStr($this->_model));
            }

            $this->success('添加权限成功',U('index'));
        }else{
            $this->_before_view();
            $this->display();
        }

    }

    /**
     * 修改权限
     * @param $id
     */
    public function edit($id)
    {
        if (IS_POST) {
            if ($this->_model->create()===false) {
                $this->error(dealErrorStr($this->_model));
            }
            if ($this->_model->savePermission($id)===false) {
                $this->error(dealErrorStr($this->_model));
            }
            $this->success('修改权限成功',U('index'));
        }else{
            $row=$this->_model->find($id);
            $this->assign('row',$row);
            $this->_before_view();
            $this->display('add');
        }

    }

    /**
     * 权限删除
     * @param $id
     */
    public function delete($id)
    {
        if ($this->_model->deletePermission($id)===false) {
            $this->error($this->_model->getError());
        }else{
            $this->success('删除权限成功',U('index'));
        }
    }

    public function _before_view()
    {
        //获取已有的权限列表
        $permissions=$this->_model->getList();
        //顶级分类  将传入的单元插入到 array 数组的开头
        array_unshift($permissions,['id'=>0,'name'=>'顶级权限']);
        //var_dump($permissions);exit;
        $this->assign('permission',json_encode($permissions));
    }
}