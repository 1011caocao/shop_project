<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/11/14
 * Time: 18:37
 */

namespace Admin\Model;


use Think\Model;
use Think\Page;

class RoleModel extends Model
{
    public function getPage(array $condition)
    {
        //获得分页工具条
        $count=$this->where($condition)->count();
        $page=new Page($count,C('PAGE.SIZE'));
        //分页工具条样式
        $page->setConfig('theme',C('PAGE.THEME'));
        $page_html=$page->show();
        $rows=$this->where($condition)->select();
        $data=[
            'rows'=>$rows,
            'page_html'=>$page_html
        ];
        return $data;
    }

    /**
     * 添加角色
     * @return bool
     */
    public function addRole()
    {
        //开启事务 重复操作数据库
        $this->startTrans();
        //保存基本信息 并保存id
        if (($role_id=$this->add())===false) {
            $this->error='保存基本信息失败';
            $this->rollback();
            return false;
        }
        //保存角色权限关联关系 权限为数据 so 遍历数据
        $permission_ids=I('post.permission_id');
        //如果没有权限就没有id直接提交
        if (empty($permission_ids)) {
            $this->commit();
            return true;
        }
        //权限为数据 so 遍历数据
        $data=[];
        foreach($permission_ids as $v){
            $data[]=[
                'role_id'=>$role_id,
                'permission_id'=>$v
            ];
        }
        if (M('RolePermission')->addAll($data)===false) {
            $this->error='保存权限关系失败';
            $this->rollback();
            return false;
        }
        $this->commit();
        return true;
    }

    /**
     * 查询角色基本信息 和该角色对应的权限id
     * @param $id
     * @return mixed
     */
    public function getRoleMsg($id)
    {
        //查询基本信息
        $row = $this->find($id);
        //连表查询权限
        $permission_ids=M('RolePermission')->where(['role_id'=>$id])->getField('permission_id',true);
        //var_dump($permission_ids);exit;
        $row['permission_ids']=json_encode($permission_ids);
        return $row;
    }

    /**
     * 修改角色
     * @param $id
     * @return bool
     */
    public function saveRole($id)
    {
        $this->startTrans();
        //修改基本信息
        if ($this->save()===false) {
            $this->error='修改基本信息失败';
            $this->rollback();
            return false;
        }
        //删除老的角色权限关系 保存新的角色关系
        $role_permission_model=M('RolePermission');
        if ($role_permission_model->where(['role_id'=>$id])->delete()===false) {
            $this->error='删除角色权限关系失败';
            $this->rollback();
            return false;
        }
        //添加新的关系
        $permission_ids=I('post.permission_id');
        //如果没有权限就没有id直接提交
        if (empty($permission_ids)) {
            $this->commit();
            return true;
        }
        //权限为数据 so 遍历数据
        $data=[];
        foreach($permission_ids as $v){
            $data[]=[
                'role_id'=>$id,
                'permission_id'=>$v
            ];
        }
        if (M('RolePermission')->addAll($data)===false) {
            $this->error='保存权限关系失败';
            $this->rollback();
            return false;
        }
        $this->commit();
        return true;
    }

    /**
     * 删除角色
     * @param $id
     * @return bool
     */
    public function deleteRole($id)
    {
        $this->startTrans();
        //删除基本信息
        if ($this->delete($id)===false) {
            $this->error='删除基本信息失败';
            $this->rollback();
            return false;
        }
        if (M('RolePermission')->where(['role_id'=>$id])->delete()===false) {
            $this->error='删除关联关系失败';
            $this->rollback();
            return false;
        }
        $this->commit();
        return true;
    }

    public function getList()
    {
        return $this->order('sort')->select();
    }

}