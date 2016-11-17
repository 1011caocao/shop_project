<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/11/9
 * Time: 11:17
 */

namespace Admin\Controller;

use Think\Controller;

class GoodsController extends  Controller
{
    /**
     * @var \Admin\Model\GoodsModel
     */
    private  $_model;
    protected function _initialize(){
        $this->_model=D('Goods');
    } 
    /**
     * 商品列表页
     */
    public function index()
    {
        //创建模型
        //查询数据
        $data=$this->_model->getPage();
        //返回数据
        $this->assign($data);
        $this->display();
    }
    /**
     * 添加商品信息
     */
    public function add()
    {
        if (IS_POST) {
            //创建模型
            if ($this->_model->create()===false) {
                $this->error(dealErrorStr($this->_model));
            }
            //保存数据
            if ($this->_model->addGoods()===false) {
                $this->error(dealErrorStr($this->_model));
            }
            //跳转
            $this->success("添加成功",U('index'));
        }else{
            $this->_before_view();
            $this->display();
        }

    }

    /**
     * 修改商品
     * @param $id
     */
    public function edit($id)
    {
        if (IS_POST) {
            //创建模型
            //搜集数据
            if ($this->_model->create()===false) {
                $this->error(dealErrorStr($this->_model));
            }
            //保存数据
            if ($this->_model->saveGoods()===false) {
                $this->error(dealErrorStr($this->_model));
            }
            $this->success("修改商品信息成功",U('index'));
        }else{
            //回显基本数据
            $row=$this->_model->findGoods($id);
            //回显分类列表
            $this->_before_view();
            //回显商品详细描述
            $goods_id=$row['id'];
            $content=M('GoodsIntro')->getFieldByGoodsId($goods_id,'content');
            //回显商品相册
            $paths=M('GoodsGallery')->where(['goods_id'=>$id])->select();
            $this->assign('paths',$paths);
            $this->assign('content',$content);
            $this->assign('row',$row);
            $this->display('add');
        }

    }

    /**
     * 删除商品信息
     * @param $id
     */
    public function remove($id)
    {
        //创建模型
        //删除数据
        if ($this->_model->removeGoods($id)===false) {
            $this->error(dealErrorStr($this->_model));
        }
        //跳转
        $this->success("删除成功",U('index'));
    }
    /**
     * 准备分类列表
     */
    private function _before_view(){
        //商品分类
        $goods_category_model=D("GoodsCategory");
        $categorise=$goods_category_model->getList();
        $this->assign('categories',json_encode($categorise));
        //商品品牌
        $brand_model=D('Brand');
        $brands=$brand_model->select();
        $this->assign('brands',$brands);
        //商品供货商
        $supplier_model=M('Supplier');
        $supplier=$supplier_model->select();
        $this->assign('supplier',$supplier);
    }

}