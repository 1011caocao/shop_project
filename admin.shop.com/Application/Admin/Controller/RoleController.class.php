<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/11/13
 * Time: 11:28
 */

namespace Admin\Controller;


use Think\Controller;

class RoleController extends Controller
{
    /**
     * @var \Admin\Model\RoleModel
     */
    private $_model;
    protected function _initialize(){
        $this->_model=D('Role');
    }

    /**
     * 角色列表展示
     */
    public function index()
    {
        //搜索
        $keyword=I("get.name");
        $condition=[];
        if($keyword){
            $condition['name']=['like','%'.$keyword.'%'];
        }
        //返回数据
        $data=$this->_model->getPage($condition);
        //var_dump($data);
        $this->assign($data);
        $this->display();
    }

    /**
     * 添加角色
     */
    public function add()
    {
        if (IS_POST) {
            if ($this->_model->create()===false) {
                $this->error(dealErrorStr($this->_model));
            }
            if ($this->_model->addRole()===false) {
                $this->error(dealErrorStr($this->_model));
            }
            $this->success('添加角色成功',U('index'));
        }else{
            $this->_before_view();
            $this->display();
        }
    }
    /**
     * 修改角色
     * @param $id
     */
    public function edit($id)
    {
        if (IS_POST) {
            if ($this->_model->create()===false) {
                $this->error(dealErrorStr($this->_model));
            }
            if ($this->_model->saveRole($id)===false) {
                $this->error(dealErrorStr($this->_model));
            }
            $this->success('修改角色成功',U('index'));
        }else{
            $row=$this->_model->getRoleMsg($id);
            $this->assign('row',$row);
            $this->_before_view();
            $this->display('add');
        }
    }

    /**
     * 删除角色
     * @param $id
     */
    public function delete($id)
    {
        if ($this->_model->deleteRole($id)===false) {
            $this->error(dealErrorStr($this->_model));
        }
        $this->success('删除角色成功',U('index'));

    }
    /**
     * 渲染视图前传入数据
     */
    public function _before_view()
    {
        //获取已有的权限列表
        $permissions=D('Permission')->getList();
        $this->assign('permissions',json_encode($permissions));
    }
}