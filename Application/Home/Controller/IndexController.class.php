<?php
namespace Home\Controller;
use Think\Controller;
import('Home.Lib.gzmongo');
import('Home.Lib.gzlogs');

class IndexController extends Controller {
    public function index(){
        //$msg = 'sdf';
        //E($msg);
        pp(1);
        //$log = date( 'Y-m-d H:i:s' );
        //var_export(\functions::file( $log, 'shutdown.txt' )); die;//长于配置值的将会被记录
        //var_export(\functions::checkChinese('尼玛'));die;
        //var_export(\gzlogs::debug('sdf'));die;
        $oo = new \gzmongo(C('MongoSer'));
        $db = 'testdb';
        $coll = 'user1';
        $table = $db.'.'.$coll;
        //echo C('DB_PORT');die;
        ($oo->createCollection($table));die;
        //var_export($oo->save($table, array('name' => 'sb', 'age' => 12)));
        //var_dump($oo->update($table, array('name' => 'sb1',),  array('$inc' => array('age' => 5))));
        //var_dump($oo->remove($table, array('name' => 'sb1')));
        //var_dump($oo->findAndModify($table, array('name' => 'sb'), array('$inc' => array('age' => 13))));
        //var_export($oo->findOne($table, array('name' => 'sb')));
        //var_export($oo->listDBs());die;
        //var_dump($oo->getHosts());
        //var_export($oo->getOne($table, array('name' => 'sb')));die;
        //var_export($oo->count($table, array('name' => 'sb')));die;
        $this->checkOnline();
        $this->display();
    }

    public function checkOnline(){
        if (session('?user')){
            $this->assign('loginName', '退出');
            $this->assign('loginUrl', 'doLogout');
            $this->assign('regName', session('user'));
            $this->assign('regUrl', 'uCenter');
        } else {
            $this->assign('loginName', '登录');
            $this->assign('loginUrl', 'login');
            $this->assign('regName', '注册');
            $this->assign('regUrl', 'reg');
        }
    }

    public function reg(){
        $this->display('reg');
    }

    public function login(){
        $this->display('login');
    }

    public function doLogin(){
        $name = I('name');
        if (D(Member)->login(I('name'), I('passwd'))){
            session('user', $name);
        } else {
            $this->error('用户名或密码不正确！！'); 
        } 
        echo "<script>window.location.href='".C('DEV_HOME')."';</script>";
    }

    public function uCenter(){
        $this->display('info');
    }

    public function doLogout(){
        session('user',null);
        echo "<script>window.location.href='".C('DEV_HOME')."';</script>";
    }

    public function insert(){
        $this->checkOnline();
        if(!$this->check_verify($_POST['verify'])){  
            $this->error('验证码错误！！');         
        } 
        load('extend'); 
        $data =D(Member);               
        if($data->create()){
            if(false !==$data->add()) { 
                $this->success('数据添加成功！'); 
            }else{ 
                $this->error('数据写入错误'); 
            }      
        } else {
            header("Content-Type:text/html; charset=utf-8"); 
            echo($data->getError()); 
        }
        echo "<script>window.location.href='".C('DEV_HOME')."';</script>";
    }

    public function verify(){
        $config =    array(
            'fontSize'    =>    16,
            'length'      =>    4,     
            'useNoise'    =>    false, 
            'imageW'      =>    100,
            'imageH'      =>    45,
            'useImgBg'    =>    false,
            'codeSet'     =>    '0123456789',
            );
        $Verify = new \Think\Verify($config);
        $Verify->entry();
    }

    public function check_verify($code, $id = ''){
        $verify = new \Think\Verify();
        return $verify->check($code, $id);
    }
}