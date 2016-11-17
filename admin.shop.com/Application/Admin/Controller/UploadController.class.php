<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/11/5
 * Time: 21:33
 */

namespace Admin\Controller;


use Think\Controller;
use Think\Upload;

class UploadController extends Controller
{
    public function upload()
    {
        //搜集数据
        $conf=[
            'mimes'         =>  array('image/jpeg','image/png','image/gif'), //允许上传的文件MiMe类型
            'maxSize'       =>  0, //上传的文件大小限制 (0-不做限制)
            'exts'          =>  array('jpg','jpeg','jpg'), //允许上传的文件后缀
            'autoSub'       =>  true, //自动子目录保存文件
            'subName'       =>  array('date', 'Y-m-d'), //子目录创建方式，[0]-函数名，[1]-参数，多个参数使用数组
            'rootPath'      =>  './', //保存根路径
            'savePath'      =>  'Uploads/', //保存路径
            'saveName'      =>  array('uniqid', ''), //上传文件命名规则，[0]-函数名，[1]-参数，多个参数使用数组
            'saveExt'       =>  '', //文件保存后缀，空则使用原后缀
            'replace'       =>  false, //存在同名是否覆盖
            'hash'          =>  true, //是否生成hash编码
            'callback'      =>  false, //检测文件是否存在回调，如果存在返回文件信息数组
            //'driver'        =>  'Qiniu', // 文件上传驱动
            'driver'        =>  '', // 文件上传驱动
            'driverConfig'  =>  array(   // 上传驱动配置
//                'secretKey' => 'KBYoPnqTbgX4a65rXNI9f-6_kCKwwnHMSnLOGLNk', //七牛服务器
//                'accessKey' => 'qJHe4wo24q6X6AWSXsv-syl8PkhHjo6i5WXc-to5', //七牛用户
//                'domain'    => 'og5tgt5wl.bkt.clouddn.com', //域名
//                'bucket'    => 'tp0813', //空间名称
//                'timeout'   => 30, //超时时间
            ),
        ];
        $upload=new Upload($conf);
        //保存
        $fileinfo=$upload->upload();
        //返回结果
            $fileinfo=array_pop($fileinfo);
       // $this->ajaxReturn($fileinfo);exit;
            $data=[];
        if(!$fileinfo){
            $data=[
                'status'=>false,
                'msg'=>$upload->getError(),
                'url'=>''
            ];
        }else{
            if($upload->driver=='Qiniu'){
                $data=[
                    'status'=>true,
                    'msg'=>"上传成功啦",
                    'url'=>$fileinfo['url'],
                ];
            }else{
                $data=[
                    'status'=>true,
                    'msg'=>"上传成功啦",
                    'url'=>C('BASE_URL').$upload->rootPath.$fileinfo['savepath'].$fileinfo['savename'],
                ];
            }

        }
        $this->ajaxReturn($data);
    }

}