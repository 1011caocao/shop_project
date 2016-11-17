<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/11/8
 * Time: 18:22
 */

namespace Admin\Logic;


class MySQLORM implements Orm
{
    public function connect()
    {
        echo __METHOD__ . '<br />';
    }

    public function disconnect()
    {
        echo __METHOD__ . '<br />';
    }

    public function free($result)
    {
        echo __METHOD__ . '<br />';
    }

    /**
     * 执行写操作
     * @param string $sql
     * @param array $args
     * @return type
     */
    public function query($sql, array $args = array())
    {
        //获取方法中参数的数组
        $args=func_get_args();
        $sql=$this->_buildSql($args);
        //执行
        return (M()->execute($sql));
    }

    /**执行插入操作
     * @param string $sql
     * @param array $args
     * @return mixed
     */
    public function insert($sql, array $args = array())
    {
        //数组下标为1的的值 为table
        $table=func_get_arg(1);
        //数组下标为1的的值 为table
        $data=func_get_arg(2);
        return M()->table($table)->add($data);

    }

    public function update($sql, array $args = array())
    {
        echo __METHOD__ . '<br />';
    }

    public function getAll($sql, array $args = array())
    {
        echo __METHOD__ . '<br />';
    }

    public function getAssoc($sql, array $args = array())
    {
        echo __METHOD__ . '<br />';
    }

    /**
     * @param string $sql
     * @param array $args
     * @return array 一行数据的关联数组
     */
    public function getRow($sql, array $args = array())
    {
        //获取方法中参数的数组
        $args=func_get_args();
        $sql=$this->_buildSql($args);
        return array_pop(M()->query($sql));
    }

    public function getCol($sql, array $args = array())
    {
        $args=func_get_args();
        echo '<pre>';
        var_dump($args);
    }

    /**
     * @param string $sql
     * @param array $args
     * @return string 第一行的第一个元素
     */
    public function getOne($sql, array $args = array())
    {
        $args=func_get_args();
        $sql=$this->_buildSql($args);
        return array_pop(array_pop(M()->query($sql)));
    }

    /**
     * 拼一个sql语句
     * @param array $args
     * @return mixed|string
     */
    private function _buildSql(array $args)
    {
        // 函数删除数组中第一个元素,并返回被删除元素的值
        $sql=array_shift($args);
        //匹配分割数组
        $sqls=preg_split('/\?[FTN]/',$sql);
        //弹出最后的空字符串
        array_pop($sqls);
        $sql='';
        foreach($sqls as $k=>$v){
            $sql.=$v.$args[$k];
        }
        return $sql;
    }


}