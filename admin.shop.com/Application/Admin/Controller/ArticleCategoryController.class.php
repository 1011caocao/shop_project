<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/11/6
 * Time: 11:34
 */

namespace Admin\Controller;


use Think\Controller;

class ArticleCategoryController extends Controller
{
    /**
     * 文章分类列表
     */
    public function index(){
        //搜索
        $keyword=I("get.name");
        $condition=[];
        if($keyword){
            $condition['name']=['like','%'.$keyword.'%'];
        }
        //创建模型
        $category_model=D("ArticleCategory");
        //获取数据
        $data=$category_model->getPage($condition);
        //显示数据
        $this->assign($data);
        $this->display();
    }

    /**
     * 添加文章分类
     */
    public function add()
    {
        if (IS_POST) {
            //创建模型
        $category_model=D("ArticleCategory");
            //搜集数据
        if (!$category_model->create()) {
            $this->error($category_model->getError());
        }
            //保存到数据库
        if(!$category_model->add()){
            $this->error($category_model->getError());
        }
        $this->success("添加文章分类成功",U('index'));
        //页面跳转
        }else{
            $this->display();
        }
    }

    /**
     * 修改文章分类
     * @param $id
     */
    public function edit($id)
    {
        //创建模型
        $category_model=D("ArticleCategory");
        if (IS_POST) {
            if (!$category_model->create()) {
                $this->error($category_model->getError());
            }
            //修改数据
            if(!$category_model->save()){
                $this->error($category_model->getError());
            }
            $this->success("修改文章分类成功",U('index'));
        }else{
            //获得数据
            $row=$category_model->find($id);
            //回显数据
            $this->assign('row',$row);
            $this->display('add');
        }
    }
    /**
     * 逻辑删除文章分类
     */
    public function remove($id)
    {
        //创建模型
        $category_model=D("ArticleCategory");
        //删除数据
        if (!$category_model->where(['id'=>$id])->setField('status',-1)) {
            $this->error($category_model->getError());
        }
        $this->success("删除文章分类成功",U('index'));
    }
}