<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/11/6
 * Time: 14:07
 */

namespace Admin\Controller;


use Think\Controller;

class ArticleController extends Controller
{
    /**
     * 文章列表
     */
    public function index()
    {
        //搜索
        $keyword=I("get.name");
        //创建模型
        $article_model=D('Article');
        //获得数据
        $date=$article_model->getPage($keyword);
        $this->assign($date);
        //选择视图
        $this->display();
    }

    /**
     * 添加文章
     */
    public function add()
    {
        //创建模型
        $article_model=D('Article');
        $_POST['inputtime']=NOW_TIME;
        if (IS_POST) {
        //搜集数据
        if ($article_model->create()===false) {
            $this->error($article_model->getError());
        }
        //存入数据库
            if ($article_model->addArticle()===false) {
                $this->error($article_model->getError());
            }
        $this->success('添加文章成功',U('index'));
        }else{
            //获取文章分类
            $category_model=D('ArticleCategory');
            $cate=$category_model->where(["status"=>['neq',-1]])->select();
            $this->assign('cate',$cate);
            $this->display();
        }
    }

    /**
     * 修改文章
     * @param $id
     */
    public function edit($id)
    {
        //创建模型
        $article_model=D('Article');
        if (IS_POST) {
            //搜集数据
            if (!$article_model->create()) {
                $this->error($article_model->getError());
            }
            //存入数据库
            if (!$article_model->saveActicle()) {
                $this->error($article_model->getError());
            }
            //跳转
            $this->success('修改文章成功',U('index'));
        }else{
            //获取数据
            $row=$article_model->findMsg($id);
            //回显数据
            $category_model=D('ArticleCategory');
            $cate=$category_model->where(["status"=>['neq',-1]])->select();
            $this->assign('cate',$cate);
            $this->assign('row',$row[0]);
            $this->display('add');
        }
    }

    /**
     * 删除文章
     * @param $id
     */
    public function remove($id)
    {
        //创建模型
        $article_model=D('Article');
        //删除数据
        if (!$article_model->deleteArticle($id)) {
            $this->error($article_model->getError());
        }
        //页面跳转
        $this->success("删除成功",U('index'));
    }
}