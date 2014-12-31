<?php

class gzmongo{
	private $replicaSet;//集群名称
	private $servers;//数据库地址
	private $mongoCli;//数据库连接对象
	public $err = '';
	/**
	 * [
	 * replicaSet  集群名称
	 * servers array(array('x.x.x.x',5000))  节点列表
	 * ]
	 **/
	public function __construct( $aSer, $aOpt = array()){
		if($aSer && is_string($aSer)){//简单配置写法
			$this->servers = $aSer;
			return;
		}
		if($aSer['replicaSet']){
			$this->replicaSet = $aSer['replicaSet'];
		}
		
		$aV = array();
		foreach((array)$aSer['servers'] as $row){
			$aV[] = $row[0] . ':' . $row[1];
		}

		$this->servers = implode(',', $aV);
	}

	//是否启用集群 长连接
	public function isReplicaSet(){
		return false;
	}
	
	//连接mongodb数据库
	public function doconn( $try){

		if(!$this->servers){
			return false;
		}

		if( ! is_object($this->mongoCli)){
			$servers = 'mongodb://' . $this->servers;
			$aOptions = array();//连接选项
			$aOptions['connect'] = true;//构造器是否应该在返回前连接
			$aOptions['connectTimeoutMS'] = 1000;//打开连接超时的时间
			$aOptions['socketTimeoutMS'] = 5000;//在套接字上发送或接收超时的时间。
			if($this->replicaSet && $this->isReplicaSet()){
				$aOptions['replicaSet'] = $this->replicaSet;//集群名称
			}
			
			//开关 bakIdcIsMaster 备机房是业务节点
			if((SERVER_TYPE === 'bak') && (oo::$config['bakIdcIsMaster'] <= 0)){
				//备机房 且 不是也是主节点
				$aOptions['readPreference'] = MongoClient::RP_SECONDARY;//是从副本节点读取
			}elseif((SERVER_TYPE === 'on') && (oo::$config['bakIdcIsMaster'] == 1)){
				//主机房 且 备机房是业务主节点(即主机房不是业务节点)
				$aOptions['readPreference'] = MongoClient::RP_SECONDARY;//是从副本节点读取
			}

			try{
				$this->mongoCli = new \MongoClient($servers, $aOptions);
			}catch(MongoConnectionException $e){
				($try == 2) && $this->exceptionLog($e, "try:{$try} ", 0);
				$this->err = $e->getMessage();
				$this->mongoCli = null;
			}catch(Exception $e){
				($try == 2) && $this->exceptionLog($e, "try:{$try} ", 0);
				$this->err = $e->getMessage();
				$this->mongoCli = null;
			}
		//有对象 无连接
		}elseif(is_object($this->mongoCli) && ( ! is_array($this->mongoCli->getConnections()))){
			try{
				$this->mongoCli->connect();
			}catch(MongoConnectionException $e){
				($try == 2) && $this->exceptionLog($e, "try:{$try} ", 0);
				$this->err = $e->getMessage();
				$this->mongoCli = null;
			}catch(Exception $e){
				($try == 2) && $this->exceptionLog($e, "try:{$try} ", 0);
				$this->err = $e->getMessage();
				$this->mongoCli = null;
			}
		}
		if(is_object($this->mongoCli) && is_array($this->mongoCli->getConnections())){
			return true;
		}else{
			return false;
		}
	}
	//连接
	public function connect(){
		for($try = 0; $try < 3; $try++){
			if($try == 2){
				$this->mongoCli = null;
			}
			if($con = $this->doconn( $try)){
				break;
			}
		}
		return $con;
	}

	//获取已连接的数据库
	public function getConnections(){
		$arr = array();
		if(is_object($this->mongoCli)){
			$arr = $this->mongoCli->getConnections();
		}
		return $arr;
	}
	public function close(){
		$connections = $this->getConnections();
		foreach ( $connections as $con ){
			// 遍历所有连接，关闭
			$closed = $this->mongoCli->close( $con['hash'] );
		}
		return true;
	}
	//所有关联主机的状态信息
	public function getHosts(){
		$arr = array();
		if($this->connect()){
			$arr = $this->mongoCli->getHosts();
		}
		return $arr;
	}
	//列出所有有效数据库
	public function listDBs(){
		$arr = array();
		if($this->connect()){
			$arr = $this->mongoCli->listDBs();
		}
		return $arr;
	}
	//选择一个数据库，返回数据库对象
	public function selectDB( $db){
		$dbObj = null;
		if($db && $this->connect()){
			try{
				$dbObj = $this->mongoCli->selectDB( $db);
			}catch(Exception $e){
				$this->exceptionLog($e, "");
				$dbObj = null;
			}

		}
		return $dbObj;
	}
	//解析出指定mongo数据库和集合
	public function explodeColl( $table){
		if( (! $table = trim($table)) || ( ! $aTale = explode('.', $table)) || (count($aTale) != 2)){
			return "table:$table is error. example:'texas_57.minfo'";
		}
		return $aTale;
	}
	//获取数据库的文档集
	public function selectCollection( $table){
		$aTale = $this->explodeColl( $table);
		if( ! is_array($aTale)){
			$this->err = $aTale;
			return null;
		}

		$db = $aTale[0];
		$coll = $aTale[1];

		$collObj = null;
		if($db && $coll && $this->connect()){
			try{
				$collObj = $this->mongoCli->selectCollection( $db, $coll);
			}catch(Exception $e){
				$this->exceptionLog($e, "");
				$collObj = null;
			}
		}
		return $collObj;
	}
	//返回某个db的结果集的对象
	public function listCollections( $db){
		if($db && $dbObj = $this->selectDB($db)){
			try{
				$aRet = $dbObj->listCollections();
			}catch(Exception $e){
				$this->exceptionLog($e, "");
			}
		}
		return $aRet;
	}
	//返回某个db的结果集的数组
	public function getCollectionNames( $db){
		if($db && $dbObj = $this->selectDB($db)){
			try{
				$aRet = $dbObj->getCollectionNames();
			}catch(Exception $e){
				$this->exceptionLog($e, "");
			}
		}
		return $aRet;
	}
	
}