<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/11/5
 * Time: 13:14
 */

namespace Admin\Controller;


use Think\Controller;

class BrandController extends Controller
{
    /**
     * 品牌首页
     */
    public function index()
    {
        //搜索
        $keyword=I("6get.name");
        $condition=[];
        if($keyword){
            $condition['name']=['like','%'.$keyword.'%'];
        }
        //创建模型
        $brand_model=D("Brand");
        //获取数据
        $data=$brand_model->getPage($condition);
        //var_dump($data);
        //显示数据
        $this->assign($data);
        $this->display();
    }
    /**
     * 添加品牌
     */
    public function add()
    {
        if (IS_POST) {
            //创建模型
            $brand_model=D("Brand");
            //搜集数据
            if($brand_model->create()==false){
                 $this->error($brand_model->getError());
            }
            if($brand_model->add()==false){
                $this->error($brand_model->getError());
            }
            //添加数据到数据库
            $this->success('添加数据成功',U('index'));
            //跳转页面
        }
        $this->display();
    }
    /**
     * 修改品牌
     * @param $id
     */
    public function edit($id)
    {
        //创建模型
        $brand_model=D("Brand");
        if (IS_POST) {
            if ($brand_model->create()==false) {
                $this->error($brand_model->getError());
            }
            if ($brand_model->save()==false) {
                $this->error($brand_model->getError());
            }
            $this->success('修改数据成功',U('index'));
           //根据id 修改数据
        }else{
            //根据id查找数据
            $row=$brand_model->find($id);
            //返回数据到页面
            $this->assign('row',$row);
            $this->display('add');
        }
    }
    //逻辑删除品牌
    public function remove($id){
        //创建模型
        $brand_model=D("Brand");
        if (!$brand_model->where(['id'=>$id])->setField("status",-1)) {
            $this->error($brand_model->getError());
        }else{
            $this->success('删除数据成功',U('index'));
        }
    }
}