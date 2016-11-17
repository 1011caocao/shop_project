<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/11/14
 * Time: 17:02
 */

namespace Admin\Model;


use Admin\Logic\MySQLORM;
use Admin\Logic\NestedSets;
use Think\Model;

class PermissionModel extends Model
{
    /**
     * 获取权限列表
     * @return mixed
     */
    public function getList()
    {
        return $this->order('lft')->select();
    }

    /**
     * 添加权限
     * @return bool
     */
    public function addPermission()
    {
        //使用nestedsets插件完成节点和层级的计算
        $orm=new MySQLORM();
        $nestedsets=new NestedSets($orm,$this->getTableName(),'lft','rght','parent_id','id','level');
        if ($nestedsets->insert($this->data['parent_id'],$this->data(),'bottom')===false) {
            $this->error='添加失败';
            return false;
        }
        return true;
    }

    /**
     * 修改权限
     * @param $id
     * @return bool
     */
    public function savePermission($id)
    {
        //判断是否修改父级权限
        $old_parent_id=$this->where(['id'=>$id])->getField('parent_id');
        if($old_parent_id!=$this->data['parent_id']){
            $orm=new MySQLORM();
            $nestedsets=new NestedSets($orm,$this->getTableName(),'lft','rght','parent_id','id','level');
            if ($nestedsets->moveUnder($id,$this->data['parent_id'],'bottom')===false) {
                $this->error = '不能将分类移动到自身或后代分类中';
                return false;
            }
        }
        $this->save();
        return true;
    }

    /**
     * 删除权限 和关联权限
     * @param $id
     * @return bool
     */
    public function deletePermission($id)
    {
        $orm=new MySQLORM();
        $nestedsets=new NestedSets($orm,$this->getTableName(),'lft','rght','parent_id','id','level');
        if ($nestedsets->delete($id)===false) {
            $this->error = '删除权限失败';
            return false;
        }
        //删除关联
        if (M('RolePermission')->where(['permission_id'=>$id])->delete()===false) {
            $this->error = '删除关联失败';
            return false;
        }
        return true;
    }
}