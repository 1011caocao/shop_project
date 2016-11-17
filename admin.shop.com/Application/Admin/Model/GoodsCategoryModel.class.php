<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/11/8
 * Time: 16:32
 */

namespace Admin\Model;


use Admin\Logic\MySQLORM;
use Admin\Logic\NestedSets;
use Think\Model;

class GoodsCategoryModel extends Model
{

    /**获取分类列表
     * @return array
     */
    public function getList()
    {
         return $this->order('lft')->select();
    }
    /**
     * 添加分类
     * @return false|int
     */
    public function addCategory()
    {
        $orm=new MySQLORM();
        $nestedSets=new NestedSets($orm,$this->getTableName(),'lft','rght','parent_id','id','level');
        return $nestedSets->insert($this->data['parent_id'],$this->data,'bottom');
    }

    public function saveCategory()
    {
        //还是使用nestedSets接口
        //判断是否修改了父级分类
        //获取原来的父级分类
        $old_p_id=$this->where(['id'=>$this->data['parent_id']])->getField('parent_id');
        if($old_p_id!=$this->data['parent_id']){
            $orm=new MySQLORM();
            $nestedSets=new NestedSets($orm,$this->getTableName(),'lft','rght','parent_id','id','level');
            if (!$nestedSets->moveUnder($this->data['id'],$this->data['parent_id'],'buttom')) {
                $this->error='不能讲分类移动后代分类中';
                return false;
            }
        }
        return $this->save();

    }
    /**
     * 删除
     * @param $id
     * @return bool
     */
    public function deleteCategory($id)
    {
        $orm=new MySQLORM();
        $nestedSets=new NestedSets($orm,$this->getTableName(),'lft','rght','parent_id','id','level');
        if ($nestedSets->delete($id)===false) {
            $this->error = '删除失败';
            return false;
        }
        return true;
    }
}