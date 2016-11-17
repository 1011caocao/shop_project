<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/11/8
 * Time: 16:27
 */

namespace Admin\Controller;


use Think\Controller;

class GoodsCategoryController extends Controller
{

    /**
     * @var \Admin\Model\GoodsCategoryModel
     */
    private  $_model;
    public function _initialize(){
        $this->_model=D('GoodsCategory');
    }
    //显示商品分类列表
    public function index(){
        $rows=$this->_model->getList();
        $this->assign('rows',$rows);
        $this->display();
    }
    public function add()
    {
        if (IS_POST) {
            //搜集数据
            if ($this->_model->create()===false){
                $this->error($this->_model->getError());
            }
            //保存数据
            if ($this->_model->addCategory()===false) {
                $this->error($this->_model->getError());
            }
            $this->success("添加分类成功",U('index'));
        }else{
            //获取列表，以便获得父级分类
            $rows=$this->_model->getList();
            array_unshift($rows,['id'=>0,'name'=>'顶级分类']);
            $categories=json_encode($rows);
            $this->assign('categories',$categories);
            $this->display();
        }

    }
    /**
     * 修改分类
     * @param integer $id
     */
    public function edit($id)
    {
        if (IS_POST) {
            if ($this->_model->create()===false){
                $this->error($this->_model->getError());
            }
            if ($this->_model->saveCategory()===false) {
                $this->error($this->_model->getError());
            }
            $this->success("修改分类成功",U('index'));
        }else{
            //回显数据
            $row=$this->_model->find($id);
            //获得已有分类，方便选择父级分类
            $rows=$this->_model->getList();
            //添加一个数据 顶级分类
            array_unshift($rows,['id'=>0,'name'=>'顶级分类']);
            $categories=json_encode($rows);
            $this->assign('categories',$categories);
            $this->assign('row',$row);
            $this->display('add');
        }

    }
    /**删除分类以及后代
     * @param $id
     */
    public function delete($id)
    {
        if ($this->_model->deleteCategory($id)===false) {
            $this->error($this->_model->getError());
        }
        $this->success("删除分类成功",U('index'));
    }

}