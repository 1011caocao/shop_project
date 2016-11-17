<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/11/10
 * Time: 17:55
 */

namespace Admin\Model;


use Org\Util\String;
use Think\Model;
use Think\Page;

class AdminModel extends Model
{
    //自动验证
    protected $_validate        =   array(
        array('username','require','用户名不能为空'),
        array('password','require','用户密码不能为空'),
        array('password','6,12','用户密码必须在6-12位之间','','length'),
        array('repassword','password','两次密码输入不一致','','confirm'),
        array('email','email','邮箱格式不正确'),
    );
    //自动完成
    protected $_auto            =   array(
        array('salt','makeSalt','','callback'),
        array('add_time',NOW_TIME,'',),
    );

    /**
     * 产生随机字符串盐
     * @param $salt
     * @return string
     */
    public function makeSalt($salt){
        //原始串
        $str="1234567890QAZXSWEDCRFVBGTYHNMJUIKLOP";
        //打乱
        $str=str_shuffle($str);
        //返回盐 从0 截取四个盐的长度为4
        return $salt=substr($str,0,4);
    }

    /**
     * 获得数据
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
        $admin_list=$this->where($condition)->page(I("get.p"),C('PAGE.SIZE'))->select();
        //返回数据
        foreach($admin_list as $k=>$v){
            $admin_list[$k]['last_login_ip']=long2ip($v['last_login_ip']);
        }
        return [
            'page_html'=>$page_html,
            'admin_list'=>$admin_list
        ];
    }
    /**
     * 添加管理员
     */
    public function addAdmin()
    {
        //获得自动完成的盐
        $salt=$this->data['salt'];
        $password=$this->data['password'];
        //加密加盐
        $this->data['password']=salt_password($password,$salt);
        //添加到数据库
        $this->startTrans();
        if (($admin_id=$this->add())===false) {
            $this->error='添加管理员失败';
            $this->rollback();
            return false;
        }
        //添加管理员角色到关联表中
        $role_ids=I('post.role_id');
        //如果没有角色就直接提交
        if (empty($role_ids)) {
            $this->commit();
            return true;
        }
        //遍历数据 提交到关联表中
        $data=[];
        foreach($role_ids as $v){
            $data[]=[
                'admin_id'=>$admin_id,
                'role_id'=>$v
            ];
        }
        if (M('AdminRole')->addAll($data)===false) {
            $this->error='保存关系失败';
            $this->rollback();
            return false;
        }
        $this->commit();
        return true;
    }

    /**
     * 获得管理员信息  用于修改页面的回显数据
     * @param $id
     * @return mixed
     */
    public function getAdminMsg($id)
    {
        $row=$this->find($id);
        $role_ids=M('AdminRole')->where(['admin_id'=>$id])->getField('role_id',true);
        $row['role_ids']=json_encode($role_ids);
        return $row;
    }
    /**
     * 修改管理员
     * @param $id
     * @return bool
     */
    public function saveAdmin($id)
    {
        $this->startTrans();
        //保存基本信息
//        if ($this->save()==false) {
//            $this->error='修改基本信息失败';
//            $this->rollback();
//            return false;
//        }
        //删除旧的角色
        $admin_role_model = M('AdminRole');
        if ($admin_role_model->where(['admin_id'=>$id])->delete()===false) {
            $this->error='删除角色关联失败';
            $this->rollback();
            return false;
        }
        //添加新的关联
        $role_ids=I('post.role_id');
        //如果没有角色就没有id直接提交
        if (empty($role_ids)) {
            $this->commit();
            return true;
        }
        // 数组 so 遍历数据
        $data=[];
        foreach($role_ids as $v){
            $data[]=[
                'admin_id'=>$id,
                'role_id'=>$v
            ];
        }
        if (M('AdminRole')->addAll($data)===false) {
            $this->error='保存关系失败';
            $this->rollback();
            return false;
        }
        $this->commit();
        return true;
    }
    /**
     * 删除管理员
     */
    public function deleteAdmin($id)
    {
        $this->startTrans();
        //删除基本信息
        if ($this->delete($id)===false) {
            $this->error='删除基本信息失败';
            $this->rollback();
            return false;
        }
        //删除关联
        if (M('AdminRole')->where(['admin_id'=>$id])->delete()===false) {
            $this->error='删除关联信息失败';
            $this->rollback();
            return false;
        }
        $this->commit();
        return true;

    }
    /**
     * 验证登录
     * @return bool|mixed
     */
    public function login() {
        //判断用户名和密码是否匹配
        $username=$this->data['username'];
        $pwd=$this->data['password'];
        $admin = $this->where(['username'=>$username])->find();
        //如果能查找到username
        if($admin){
            $salt = $admin['salt'];
            if(salt_password($pwd,$salt)==$admin['password']){
                $id=$admin['id'];
                //修改最后登录的时间和ip
                $data=[
                    'id'=>$id,
                    'last_login_time'=>NOW_TIME,
                    'last_login_ip'=>ip2long($_SERVER['REMOTE_ADDR'])
                ];
                $this->save($data);
                //保存到session
                session('ADMIN',$admin);
                //保存当前的管理员的权限信息
                $this->_savePermission();
                $this->_saveToken($admin,I('post.remember'));
                return $admin;
            }else{
                $this->error='密码错误';
            }
        }else{
            $this->error='用户不存在';
        }
        return false;
    }

    /**
     * 生成令牌 保存到cookie中 保存到数据库中
     * @param $admin
     */
    public function _saveToken($admin,$is_remember=false)
    {
        //勾选了记住密码
        if($is_remember){
            //生成随机字符串 token(令牌)
            $token=String::randString(32);
            //存到cookie
            $data=[
                'id'=>$admin['id'],
                'token'=>$token
            ];
            //保存时间为7天
            cookie('AUTO_LOGIN_TOKEN',$data,604800);
            // 存到数据库
            $this->save($data);
        }

    }

    /**
     * 用户自动登录
     * @return bool|mixed
     */
    public function autoLogin()
    {
        //用于获取数据
        $cookie=cookie('AUTO_LOGIN_TOKEN');
        if(empty($cookie)){
            return [];
        }
        //检查cookie中的值是否和数据库中的相同
        if ($admin=$this->where($cookie)->find()) {
            //更新令牌
            $this->_saveToken($admin,true);
            //保存信息到session
            session('ADMIN',$admin);
            return $admin;
        }else{
            return [];
        }
    }

    /**
     * 用户登录成功后保存权限列表和权限id
     */
    public function _savePermission()
    {
        $admin=session('ADMIN');
        //获得当前的登录的用户权限
        $permission=M('AdminRole')->alias('ar')->field('p.id,path')->join('__ROLE_PERMISSION__ as rp using(`role_id`)')->
        join('__PERMISSION__ as p on p.id=rp.permission_id')->
        where(['ar.admin_id'=>$admin['id']])->select();
        $pathes=$permission_ids=[];
        //循环取得当前管理员的权限
        foreach($permission as $v){
            $pathes[]=$v['path'];
            $permission_ids[]=$v['id'];
        }
        //保存路径到session中
        session('ADMIN_PATH',$pathes);
        session('ADMIN_PIDS',$permission_ids);
    }
}