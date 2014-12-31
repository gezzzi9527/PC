<?php
namespace Home\Model;
use Think\Model;
class MemberModel extends Model{
    protected $connection = 'DB_CONFIG1';
	 
	// 定义自动验证
    protected $_validate = array(
            array('name','require','用户名必须'),
            array('passwd','require','密码必填！'),
            array('name','','姓名已存在！',0,'unique',self::MODEL_INSERT),
        );
    // 定义自动完成
    protected $_auto = array(
            array('passwd','md5',1,'function') ,
            array('createtime','reg_time',self::MODEL_INSERT,'callback'),
        );
    public function login($name, $pwd){
        $res = $this->where("name=%s and passwd=%s", array($name, $pwd))->select();
        return $res;     
    }
    public function reg_time(){
        return date('Y-m-d H:i:s');
    }
}

?>