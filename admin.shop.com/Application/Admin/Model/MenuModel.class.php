<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/11/15
 * Time: 20:53
 */

namespace Admin\Model;


use Admin\Logic\MySQLORM;
use Admin\Logic\NestedSets;
use Think\Model;

class MenuModel extends  Model
{
    public function getList()
    {
        return $this->order('lft')->select();
    }

    /**
     * 获取修改时的回显数据
     * @param $id
     * @return mixed
     */
    public function getMenuInfo($id)
    {
        $row=$this->find($id);
        $row['permission_ids'] = json_encode(M('MenuPermission')->where(['menu_id' => $id])->getField('permission_id',true));
        return $row;
    }
    /**
     * 添加菜单
     * @return bool
     */
    public function addMenu()
    {
        $this->startTrans();
        //创建orm
        $orm=new MySQLORM();
        //创建nestedsets
        $nestedsets=new NestedSets($orm,$this->getTableName(),'lft','rght','parent_id','id','level');
        //执行添加
        if (($id=($nestedsets->insert($this->data['parent_id'],$this->data,'bottom')))===false) {
            $this->error='添加菜单失败';
            $this->rollback();
            return false;
        }
        //保存菜单权限关系
        $menu_permission_model=M('MenuPermission');
        //搜集数据
        $permissions=I('post.permission_id');
        //如果没有权限
        if (empty($permissions)) {
            $this->commit();
            return true;
        }
        $data=[];
        foreach($permissions as $v){
            $data[]=[
                'menu_id'=>$id,
                'permission_id'=>$v,
                ];
        }
        if ($menu_permission_model->addAll($data)===false) {
            $this->error='保存关联关系失败';
            $this->rollback();
            return false;
        }
        $this->commit();
        return true;
    }
    /**
     * 修改菜单
     * @param $id
     * @return bool
     */
    public function saveMenu($id)
    {
        $this->startTrans();
        //判断是否修改父级菜单
        $old_parent_id=$this->where(['id'=>$id])->getField('parent_id');
        if($old_parent_id!=$this->data['parent_id']){
            //创建orm
            $orm=new MySQLORM();
            //创建nestedsets
            $nestedsets=new NestedSets($orm,$this->getTableName(),'lft','rght','parent_id','id','level');
            //执行修改
            if ($nestedsets->moveUnder($this->data['id'],$this->data['parent_id'],'bottom')===false) {
                $this->error='不能将自己移动到子分类中';
                $this->rollback();
                return false;
            }
        }
        $menu_permission_model=M('MenuPermission');
        //删除旧的关联关系
        if ($menu_permission_model->where(['menu_id'=>$id])->delete()===false) {
            $this->error='删除关联关系失败';
            $this->rollback();
            return false;
        }
        //保存菜单权限关系 搜集数据
        $permissions=I('post.permission_id');
        //如果没有权限
        if (empty($permissions)) {
            $this->commit();
            return true;
        }
        $data=[];
        foreach($permissions as $v){
            $data[]=[
                'menu_id'=>$id,
                'permission_id'=>$v,
            ];
        }
        if ($menu_permission_model->addAll($data)===false) {
            $this->error='保存关联关系失败';
            $this->rollback();
            return false;
        }
        $this->commit();
        return true;
    }

    /**
     * 删除菜单
     * @param $id
     * @return bool
     */
    public function deleteMenu($id)
    {
        //判断该菜单是否有子类
        $kid_ids=$this->where(['parent_id'=>$id])->getField('id',true);
        //删除菜单权限表中的关联数据
        if (M('MenuPermission')->where(['menu_id'=>$id])->delete()===false) {
            $this->error='删除关联关系失败';
            $this->rollback();
            return false;
        }
        if($kid_ids){
            //如果有孩子则删除关联表中的孩子
            if (M('MenuPermission')->where(['menu_id'=>['in',$kid_ids]])->delete()===false) {
                $this->error='删除关联关系失败';
                $this->rollback();
                return false;
            }
        }
        //创建orm
        $orm=new MySQLORM();
        //创建nestedsets
        $nestedsets=new NestedSets($orm,$this->getTableName(),'lft','rght','parent_id','id','level');
        //执行删除
        if ($nestedsets->delete($id)===false) {
            $this->error='删除菜单失败';
            $this->rollback();
            return false;
        }
        $this->commit();
        return true;
    }
    /**
     * 获取可见的菜单列表
     */
    public function getVisableMenu()
    {
        $pids=session('ADMIN_PIDS');
        //如果是超级管理员 则可以访问所有
        $admin=session('ADMIN');
        if ($admin['username']!='admin') {
            //获取当前权限列表
            return $this->distinct(true)->field('m.id,m.name,m.path,m.parent_id,m.level')->alias('m')->join('__MENU_PERMISSION__ as mp on
            m.id=mp.menu_id')->where(['permission_id'=>['in',$pids]])->select();
        }else{
            //超级管理员
            return $this->distinct(true)->field('m.id,m.name,m.path,m.parent_id,m.level')->alias('m')->select();
        }
    }
}