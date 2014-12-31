<?php
namespace Home\Controller;
use Think\Controller;
import('Home.Lib.gzmongo');

class MobileController extends Controller {
    public function index(){
        die;
    }

    public function login(){
        header("Access-Control-Allow-Origin:null");
        $ret = array();
        if (D(Member)->login(I('name'), I('passwd'))){
            $ret['ok'] = 1;
            echo json_encode($ret);
            die;
        } else {
            $ret['ok'] = 0;
            echo json_encode($ret);
            die;
        }
    }
}