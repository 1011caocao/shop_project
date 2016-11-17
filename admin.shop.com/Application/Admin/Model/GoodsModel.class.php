<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/11/9
 * Time: 15:15
 */

namespace Admin\Model;


use Think\Model;
use Think\Page;

class GoodsModel extends Model
{
    //自动完成 添加时间
    protected  $_auto  =array(
        array('inputtime',NOW_TIME),
    );
    /**
     * 取得数据
     * @return array
     */
    public function getPage()
    {
        //获取分页工具
        $count =$this->count();
        $page=new Page($count,C('PAGE.SIZE'));
        //分页工具配置
        $page->setConfig('theme',C('PAGE.THEME'));
        $page_html=$page->show();
        //获得数据
        $rows=$this->where(["status"=>['neq',-1]])->page(I("get.p"),C('PAGE.SIZE'))->order('sort')->select();
        //遍历取得精品，新品，热销 利用或运算
        foreach($rows as $key=>$val){
            $rows[$key]['is_best']=$val['goods_status']&1?1:0;
            $rows[$key]['is_new']=$val['goods_status']&2?1:0;
            $rows[$key]['is_hot']=$val['goods_status']&4?1:0;
        }
        //返回数据
        return [
            'page_html'=>$page_html,
            'rows'=>$rows
        ];
    }

    /**
     * 获得sn 货号
     * @return string
     */
    public function calc_sn()
    {
        //获得当前的年月日
        $day=date('Ymd');
        //获取当天插入的数据记录条数
        $goods_day_count=M("GoodsDayCount");
        //判断是否为第一条数据  通过day查找对应的统计条数
        if(!$count=$goods_day_count->getFieldByDay($day,'count')){
            $count=1;
            $data=[
                'day'=>$day,
                'count'=>$count
            ];
            $goods_day_count->add($data);
        }else{
            //如果不是第一条数据，则修改数据 总数  +1
            $count++;
            $goods_day_count->where(['day'=>$day])->save(['count'=>$count]);
        }
        //返回货号   str_pad() 函数把字符串填充为指定的长度   STR_PAD_LEFT 填充左边
        return 'SN'.$day.str_pad($count,5,'0',STR_PAD_LEFT);
    }
    /**
     * 添加商品信息
     * @return bool|mixed
     */
    public function addGoods()
    {
        $this->startTrans();
        //保存基本信息 商品状态 精品新品热销
        foreach($this->data['goods_status'] as $v){
            $this->data['goods_status']|=$v;
        }
        $this->data['sn']=$this->calc_sn();
        //var_dump($this->data['goods_status']);exit;
        if(($id=$this->add())===false){
            $this->error='添加商品基本信息失败';
            $this->rollback();
            return false;
        }
        //保存商品详细信息
        $date=[
            'goods_id'=>$id,
            'content'=>I('post.content','',false),
        ];
        if(M('GoodsIntro')->add($date)===false){
            $this->error="添加商品详情出错";
            $this->rollback();
            return false;
        }
        //保存商品相册 用addAll
        $path=I('post.path');
        $gallery=[];
        foreach($path as $v){
            $data=[
                'goods_id'=>$id,
                'path'=>$v,
            ];
            $gallery[]=$data;
        }
        if (M('GoodsGallery')->addAll($gallery)===false) {
            $this->error="添加商品相册出错";
            return false;
        }
        return $id;
    }
    /**
     * 保存商品修改
     * @return bool
     */
    public function saveGoods()
    {
        //保存基本信息 并且保存id
        $id=$this->data['id'];
        foreach($this->data['goods_status'] as $v){
            $this->data['goods_status']|=$v;
        }
        if ($this->save()===false) {
            return false;
        }
        //保存修改的商品详细信息
        $data=[
            'goods_id'=>$id,
            'content'=>I('post.content','',false),
        ];
        if (M('GoodsIntro')->save($data)===false) {
            $this->error='保存商品详细描述失败';
            return false;
        }
        //保存修改的商品相册信息  在保存之前 先将数据库中goods_id为$id的删完
        M('GoodsGallery')->where(['goods_id'=>$id])->delete();
        $path=I('post.path');
        if($path){
            $gallery=[];
            foreach($path as $v){
                $data=[
                    'goods_id'=>$id,
                    'path'=>$v,
                ];
                $gallery[]=$data;
            }
            if (M('GoodsGallery')->addAll($gallery)===false) {
                $this->error="修改商品相册出错";
                return false;
            }
        }

        return true;
    }

    /**
     * 修改时商品的数据回显
     * @param $id
     * @return mixed
     */
    public function findGoods($id)
    {
        $row=$this->find($id);
        //为了更好的操作checkbox中的goods_status 将其转化
        $status=$row['goods_status'];
        //命名数组goods_status
        $row['goods_status']=array();
        //将二进制数字与运算
        if($status&1){
            $row['goods_status'][]=1;
        }
        if($status&2){
            $row['goods_status'][]=2;
        }
        if($status&4){
            $row['goods_status'][]=4;
        }
        //回显商品基本信息
        $row['goods_status']=json_encode($row['goods_status']);
        return $row;
    }
    /**
     * 移出商品  逻辑移出 不删出商品
     * @param $id
     * @return bool
     */
    public function removeGoods($id)
    {
        if ($this->where(['id'=>$id])->setField("status",-1)===false) {
            $this->error='删除信息失败';
            return false;
        }
        return true;
    }
}