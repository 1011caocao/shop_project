<?php
/**
 * 将数据库中取出的结果形成下拉列表
 * @param array $data  二位数组
 * @param $name 提交表单的名字
 * @param string $value_filed 属性 option里面的value值
 * @param string $name_filed option里面的选项值
 * @return string
 */
function arr2select(array $data,$name,$value_filed='id',$name_filed='name',$selectid){
    $html='<select name="'.$name.'" id="'.$selectid.'"">';
    $html.='<option value="">请选择。。。</option>';
    foreach($data as $v){
        $html.='<option value="'.$v[$value_filed].'">'.$v[$name_filed].'</option>';
    }
    $html.='</select>';
    return $html;
}

/**
 * 当提示错误为数组的时候，批量提示错误
 * @param $userModel
 * @return string
 */
function dealErrorStr($userModel)
{
    $errors = $userModel->getError();
    $errorStr = "<ul>";
    //判断是否批量验证的数组错误信息
    if (is_array($errors)) {
        foreach($errors as $error){
            $errorStr .= "<li>$error</li>";
        }
    }else{
        $errorStr .= "<li>$errors</li>";
    }
    $errorStr .= "</ul>";
    return $errorStr;
}

/**
 * 加盐加密
 * @param $password
 * @param $salt
 * @return string
 */
function salt_password($password,$salt){
    return md5(md5($password).$salt);
}