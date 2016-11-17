<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/11/6
 * Time: 14:09
 */

namespace Admin\Model;


use Think\Model;
use Think\Page;

class ArticleModel extends Model
{
    public function getPage($keyword)
    {
        //获取分页工具
        $condition=[];
        if($keyword){
            $condition['name']=['like','%'.$keyword.'%'];
            $condition2['A.name']=['like','%'.$keyword.'%'];
        }
        $count =$this->where($condition)->count();//var_dump($count);exit;
        $page=new Page($count,C('PAGE.SIZE'));
        //分页工具配置
        $page->setConfig('theme',C('PAGE.THEME'));
        $page_html=$page->show();
        //获得数据
        $rows=$this->table('shop_article as A')->join('shop_article_category as C on A.article_category_id=C.id')->field('a.*,C.name as catename')->where($condition2)->page(I("get.p"),C('PAGE.SIZE'))->order('sort')->select();
        foreach($rows as $k=>$v){
            $rows[$k]['inputtime']=date('Y-m-d H:i:s',$v['inputtime']);
        }
        return [
            'page_html'=>$page_html,
            'rows'=>$rows
        ];
    }

    /**
     * 添加文章
     * @return mixed
     */
    public function addArticle()
    {
        if (($id=$this->add())===false) {
            $this->error='保存基本信息失败';
        }
        $data=[
            'article_id'=>$id,
            'content'=>I('post.content')
        ];
        if (M('ArticleContent')->add($data)===false) {
            $this->error='保存文章内容息失败';
        }
        return $id;
    }

    /**
     * 查找数据
     * @param $id
     * @return mixed
     */
    public function findMsg($id)
    {
        $rows=$this->table('shop_article as A')->join('shop_article_category as C on A.article_category_id=C.id')->join('shop_article_content as T on A.id=T.article_id')->field('a.*,C.name as catename,T.content as content')->where(['A.id'=>$id])->select();
        return $rows;
    }
    /**
     * 修改文章
     * @return bool
     */
    public function saveActicle()
    {
        //开启事务
        $this->startTrans();
        //保存基本信息 并且保存id
        $id=$this->data['id'];
        if ($this->save()===false) {
            $this->error='修改基本信息失败';
            $this->rollback();
            return false;
        }
        $data=[
          'article_id'=>$id,
            'content'=>I('post.content')
        ];
        if (M('ArticleContent')->save($data)===false) {
            $this->error='修改文章详细内容失败';
            $this->rollback();
            return false;
        }
        return true;
    }
    /**
     * 删除文章 包括内容
     * @param $id
     * @return bool
     */
    public function deleteArticle($id)
    {
        $this->startTrans();
        if ($this->delete($id)===false) {
            $this->error='删除文章基本信息失败';
            $this->rollback();
            return false;
        }
        if (M('ArticleContent')->where(['article_id'=>$id])->delete()===false) {
            $this->error='删除文章内容失败';
            $this->rollback();
            return false;
        }
        return true;
    }
}