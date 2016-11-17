<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/11/6
 * Time: 11:39
 */

namespace Admin\Model;


use Think\Model;
use Think\Page;

class ArticleCategorymodel extends  Model
{
    protected  $_validate=[
        ['name','require','分类名不能为空'],
    ];
    /**
     * 获得分页数据
     * @param array $condition
     * @return array
     */
    public function getPage(array $condition=[])
    {
        //获取分页工具
        $count =$this->where($condition)->count();
        $page=new Page($count,C('PAGE.SIZE'));
        //分页工具配置
        $page->setConfig('theme',C('PAGE.THEME'));
        $page_html=$page->show();
        //获得数据
        $rows=$this->where($condition)->where(["status"=>['neq',-1]])->page(I("get.p"),C('PAGE.SIZE'))->order('sort')->select();
        //返回数据
        return [
            'page_html'=>$page_html,
            'rows'=>$rows
        ];
    }
}