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
			//$servers = 'mongodb://' .C('MongoUser').':'.C('MongoPwd').'@'. $this->servers;
			$servers = 'mongodb://' . $this->servers;
			$aOptions = array();//连接选项
			$aOptions['connect'] = true;//构造器是否应该在返回前连接
			$aOptions['connectTimeoutMS'] = 1000;//打开连接超时的时间
			$aOptions['socketTimeoutMS'] = 5000;//在套接字上发送或接收超时的时间。
			if($this->replicaSet && $this->isReplicaSet()){
				//$aOptions['replicaSet'] = $this->replicaSet;//集群名称
			}

			try{
				$this->mongoCli = new MongoClient($servers, $aOptions);
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
			return "table:$table is error. example:'db.user'";
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

	//创建集合
	public function createCollection($table, $options = array()){
		if( ! $table){
			return false;
		}
		$aTale = $this->explodeColl( $table);
		if( ! is_array($aTale)){
			$this->err = $aTale;
			return false;
		}

		$db = $aTale[0];
		$coll = $aTale[1];
		$dbObj = $this->selectDB($db);
		if($dbObj){
			try{
				$objColl = $dbObj->createCollection($coll, $options);
			}catch(Exception $e){
				$this->exceptionLog($e, "");
			}
			
		}
		return $objColl;
	}

	//插入数据
	/**
	 * $table 数据库.文档集\表
	 * $arr  数组 可以是多维的 如果 _id 存在则不会执行插入
	 * $safe 是否安全插入 false 离铉之箭 true 返回前刷新磁盘 很慢
	 * w 0 离铉之箭，不关心是否成功
		 1 写操作，会被服务器确认
		 N 写操作，主服务器必须确认，然后复制到N-1
		 majority 写操作需所有副本确认，是个特殊保留字符
		 j=true 写操作被主确认，并根据日志同步副本
	 * j 写日志的方式 0 异步写日志 1同步写日志
	 **/
	public function insert($table, $arr, $safe = false){
		$collObj = $this->selectCollection($table);
		if( ! $collObj){
			return $this->genRet(0, 0, $collObj, $this->err, __line__);
		}
		$op = $ret = array();
		if($safe){
			$op['fsync'] = false;// FALSE 返回前刷入磁盘 FALSE. Forces the insert to be synced to disk before returning success
			$op['j'] = 0;//false 返回前写入日志
			$op['w'] = 1;
		}else{
			$op['fsync'] = false;
			$op['j'] = 0;
			$op['w'] = 0;
		}
		if(empty($arr)){
			$this->exceptionLog($arr, "insert", 1000);
			return $this->genRet(0, 0, $collObj, $this->err, __line__);
		}

		try{
			$ret = $collObj->insert($arr, $op);
			if( ! is_array($ret)){
				$tempRet = intval($ret);
				$ret = array();
				$ret['ok'] = $tempRet;
			}
		}catch( MongoCursorTimeoutException $e){
			$this->exceptionLog($e, "insert");
			$errExcep = $e->getMessage();
		}catch(MongoCursorException $e){
			$this->exceptionLog($e, "insert");
			$errExcep = $e->getMessage();
		}catch(MongoException $e){
			$this->exceptionLog($e, "insert");
			$errExcep = $e->getMessage();
		}catch(MongoDuplicateKeyException $e){
			$this->exceptionLog($e, "insert");
			$errExcep = $e->getMessage();
		}catch(Exception $e){
			$this->exceptionLog($e, "insert");
			$errExcep = $e->getMessage();
		}

		if($ret['ok'] && ( ! $ret['err'])){
			$sta = 1;
		}else{
			$sta = 0;
		}
		return $this->genRet($sta, $sta, $ret, $errExcep, __line__);
	}

	//批量插入数据 (单纯的插入， 不会尝试去更新)
	/**
	 * $db   数据库
	 * $coll 文档集\表
	 * $arr  数组 可以是多维的 如果 _id 存在则不会执行插入
	 * $safe 是否安全插入 false 离铉之箭 true 返回前刷新磁盘 很慢
	 * w 0 离铉之箭，不关心是否成功
		 1 写操作，会被服务器确认
		 N 写操作，主服务器必须确认，然后复制到N-1
		 majority 写操作需所有副本确认，是个特殊保留字符
		 j=true 写操作被主确认，并根据日志同步副本
	 * j 写日志的方式 0 异步写日志 1同步写日志
	 **/
	public function batchInsert($table, $arr, $safe = false){
		$collObj = $this->selectCollection($table);
		if( ! $collObj){
			return $this->genRet(0, 0, $collObj, $this->err, __line__);
		}
		$op = $ret = array();
		if($safe){
			$op['fsync'] = false;// FALSE 返回前刷入磁盘 FALSE. Forces the insert to be synced to disk before returning success
			$op['j'] = 0;//false 返回前写入日志
			$op['w'] = 1;
		}else{
			$op['fsync'] = false;
			$op['j'] = 0;
			$op['w'] = 0;
		}
		$op['continueOnError'] = true;
		if(empty($arr)){
			$this->exceptionLog($arr, "batchInsert", 1000);
			return $this->genRet(0, 0, $collObj, $this->err, __line__);
		}

		try{
			$ret = $collObj->batchInsert($arr, $op);
			if( ! is_array($ret)){
				$tempRet = intval($ret);
				$ret = array();
				$ret['ok'] = $tempRet;
			}
		}catch( MongoCursorTimeoutException $e){
			$this->exceptionLog($e, "insert");
			$errExcep = $e->getMessage();
		}catch(MongoCursorException $e){
			$this->exceptionLog($e, "insert");
			$errExcep = $e->getMessage();
		}catch(MongoException $e){
			$this->exceptionLog($e, "insert");
			$errExcep = $e->getMessage();
		}catch(MongoDuplicateKeyException $e){
			$this->exceptionLog($e, "insert");
			$errExcep = $e->getMessage();
		}catch(Exception $e){
			$this->exceptionLog($e, "insert");
			$errExcep = $e->getMessage();
		}

		if($ret['ok'] && ( ! $ret['err'])){
			$sta = 1;
		}else{
			$sta = 0;
		}
		return $this->genRet($sta, $sta, $ret, $errExcep, __line__);
	}

	//如果对象存在数据库，则更新现有的数据库对象，否则插入对象。
	public function save($table, $arr, $safe = false){
		$collObj = $this->selectCollection( $table);
		if( ! $collObj){
			return $this->genRet(0, 0, $collObj, $this->err, __line__);
		}
		$op = $ret = array();
		if($safe){
			$op['fsync'] = false;// FALSE 返回前刷入磁盘 FALSE. Forces the insert to be synced to disk before returning success
			$op['j'] = 0;//false 返回前写入日志
			$op['w'] = 1;
		}else{
			$op['fsync'] = true;
			$op['j'] = 0;
			$op['w'] = 0;
		}
		if(empty($arr)){
			$this->exceptionLog($arr, "save", 1000);
			return $this->genRet(0, 0, $collObj, $this->err, __line__);
		}
		try{
			$ret = $collObj->save($arr, $op);
		}catch(Exception $e){
			$this->exceptionLog($e, "save");
			$errExcep = $e->getMessage();
		}
		if($ret['ok'] && ( ! $ret['err'])){
			$sta = 1;
		}else{
			$sta = 0;
		}
		return $this->genRet($sta, $sta, $ret, $errExcep, __line__);
	}

	//检查索引应用
	public function checkIndex($table, $criteria){
		if( empty( $criteria)){
			return true;
		}

		$cfgIndex = array( array('k' => 1));
		$cfgIndex[] = array('from' => 1,'back' => 1);
		$cfgIndex[] = array('from' => 1,'t' => 1);
		$cfgIndex[] = array('tit' => 1);
		$cfgIndex[] = array('type' => 1);
		
		( ! $cfgIndex) && ( $cfgIndex = array());
		$cfgIndex[] = array('_id' => 1);//添加默认_id 索引
		$isIndex = false;
		foreach($cfgIndex as $akey){
			$indexField = key($akey);
			$ret = $this->isIncludeIndex($indexField, $criteria);
			if($ret === true){
				$isIndex = true;
				break;
			}
		}
		return $isIndex;
	}
	//$indexField	索引字段
	//$criteria		查询条件
	public function isIncludeIndex($indexField, $criteria){
		if(( ! $indexField) || (!is_array($criteria))){
			return false;
		}
		$ret = false;
		foreach($criteria as $key => $row){
			//操作符
			if(substr($key, 0, 1) === '$'){
				$ret = $this->isIncludeIndex($indexField, $row);
			}elseif($key == $indexField){
				$ret = true;
				break;
			}
		}
		return $ret;
	}

	//更新数据
	/**
	 *$db 		数据库
	 *$coll 	文档集合 数据表
	 *$criteria 更新对象描述，即更新条件
	 * ==表达式运算符==
	 * $gt  大于
	 * $gte 大于等于
	 * $in  在指定的集合\集合中
	 * $nin 不在指定的集合\集合中
	 * $lt  小于
	 * $lte 小于等于
	 * $ne 不等于
	 * 等于则直接表示
	 * 语法：{field:{[$op]:value}}
	 * 如：array('uid' => 100,'age' => array('$gt' => 18,'$lt' => 30))
	 * 如上条件表示 uid为100 且 age大于18 且 age小于30
	 * ==逻辑运算符==
	 * $or  或     语法 {$or:{{field:{[$op]:value}},{field:{[$op]:value}}}}
	 * $and 且(所有满足)     语法 {$and:{{field:{[$op]:value}},{field:{[$op]:value}}}}
	 * $not 不匹配 语法 {field:$not:{{[$op]:value}}}
	 * $nor 执行逻辑NOR运算,指定一个至少包含两个表达式的数组，选择出都不满足该数组中所有表达式的文档。
	 * ==元素查询操作符==
	 * $exists 字段是否存在  true/false 语法 { field: { $exists: <boolean> } }
	 * $type   字段值类型 { field: { $type: <BSON type> } } http://docs.mongodb.org/manual/reference/operator/query/type/
	 * ==复杂操作符==
	 * $mod   取模操作 { field: { $mod: [ divisor, remainder ]} }  将字段值对divisor取模 等于 remainder
	 * $regex 正则操作
	 * $where 支持javascript
	 * ==数组查询==
	 * $all 匹配那些指定键的键值中包含数组，而且该数组包含条件指定数组的所有元素的文档。
	 *       { field: { $all: [ <value> , <value1> ... ] }
	 *
	 * $new_object 更新对象
	 * 操作符
	 * $set 覆盖式更新 {$set:{<field>:<value>}}}
	 * $inc 累加(正数)/减(负数)操作，支持整形和浮点数 不可对非数值类型字段进行此操作 {$inc:{<field>:<value>}}}
	 *	$unset	移除指定字段{‘$unset’:{<field>:1}}
	 * $rename 修改字段名{‘$rename’:{<oldfield>:<newfield>}} 不可在同一语句中改值又改名
	 * 数组操作符：
	 * $push 往数组中增加元素{$push :{<field>:<value>}}
	 * $pop数组头(-1)/尾(1)移除元素 {‘$pop:{<field>:<1/-1>}}
	 * $addToSet 往数组增加不存在的元素，相当于集合
	 *
	 * $options  选项
	 * upsert 		true 不存在则插入， false不存在不插入 默认为 true
	 * multiple 	true 更新满足条件的多条记录，false只更新首条 默认为true
	 * safe			true 需要获得服务器确认， false 不需要服务器确认，性能极佳  默认false
	 **/
	public function update($table, $criteria, $new_object, $options = array()){
		$sactime = microtime(true);
		$collObj = $this->selectCollection($table);
		if( ! $collObj){
			return $this->genRet(0, 0, $collObj, $this->err, __line__);
		}
		$op = $ret = array();
		// 1 更新 不存在 则插入
		$op['upsert'] = ($options['upsert'] === false) ? false : true;
		// 1 批量更新
		$op['multiple'] = isset($options['multiple']) && in_array($options['multiple'], array(0, 1)) ? $options['multiple'] : 1;
		$safe = false;
		if(isset($options['safe'])){
			$safe = $options['safe'];
		}
		if($safe){
			$op['fsync'] = false;
			$op['j'] = 0;
			$op['w'] = 1;
		}else{
			$op['fsync'] = false;
			$op['j'] = 0;
			$op['w'] = 0;
		}
		$aModifiers = array('$set', '$unset', '$inc', '$push', '$pop', '$pull',
							'$addToSet', '$each','$setOnInsert','$rename','$min','$max');
		$object = $new_object;
		foreach($new_object as $act => $row){
			if( ! in_array($act, $aModifiers, true)){
				unset($new_object[$act]);
				continue;
			}
		}
		if(empty($new_object)){
			$this->exceptionLog($object, "save", 1000);
			return $this->genRet(0, 0, $collObj, $this->err, __line__);
		}
		//索引检测
		if( ! $this->checkIndex($table, $criteria)){
			//"no index";	
		}
		
		try{
			$ret = $collObj->update($criteria, $new_object, $op);
			if( ! is_array($ret)){
				$tempRet = intval($ret);
				$ret = array();
				$ret['ok'] = $tempRet;
			}
		}catch(MongoCursorException $e){
			$this->exceptionLog($e, "update");
			$errExcep = $e->getMessage();
		}catch(MongoCursorTimeoutException $e){
			$this->exceptionLog($e, "update");
			$errExcep = $e->getMessage();
		}catch(Exception $e){
			$this->exceptionLog($e, "update");
			$errExcep = $e->getMessage();
		}
		if($ret['ok'] && ( ! $ret['err'])){
			$sta = 1;
		}else{
			$sta = 0;
		}
		return $this->genRet($sta, (int)$ret['n'], $ret, $errExcep, __line__);
	}

	//删除记录
	public function remove($table, $criteria, $justOne = false, $safe = false){
		$collObj = $this->selectCollection($table);
		if( ! $collObj){
			return $this->genRet(0, 0, $collObj, $this->err, __line__);
		}
		$op = $ret = array();
		if($safe){
			$op['fsync'] = false;
			$op['j'] = 0;
			$op['w'] = 1;
		}else{
			$op['fsync'] = false;
			$op['j'] = 0;
			$op['w'] = 0;
		}
		$op['justOne'] = $justOne ? 1 : 0;
		
		//索引检测
		if( ! $this->checkIndex($table, $criteria)){
			//"no index";
		}
		
		try{
			$ret = $collObj->remove($criteria, $op);
			if( ! is_array($ret)){
				$tempRet = intval($ret);
				$ret = array();
				$ret['ok'] = $tempRet;
			}
		}catch(MongoCursorException $e){
			$this->exceptionLog($e, "remove");
			$errExcep = $e->getMessage();
		}catch(MongoCursorTimeoutException $e){
			$this->exceptionLog($e, "remove");
			$errExcep = $e->getMessage();
		}catch(Exception $e){
			$this->exceptionLog($e, "remove");
			$errExcep = $e->getMessage();
		}
		if($ret['ok'] && ( ! $ret['err'])){
			$sta = 1;
		}else{
			$sta = 0;
		}
		return $this->genRet($sta, $sta, $ret, $errExcep, __line__);
	}

	//更新并返回  $criteria, $new_object 见update说明
	public function findAndModify($table, $criteria, $new_object, array $fields = array(), array $options = array()){
		$sactime = microtime(true);
		$collObj = $this->selectCollection( $table);
		if( ! $collObj){
			return $this->genRet(0, array(), $collObj, $this->err, __line__);
		}
		$op = $aRet = array();
		// 删除 并返回
		if(isset($options['remove'])){
			$op['remove'] = $options['remove'] ? true : false;
		}
		//更新
		if(isset($options['update'])){
			$op['update'] = $options['update'];
		}
		//为TRUE时，返回修改后的文件，而不是原来的。该findAndModify方法会忽略删除操作的新选项。默认值为FALSE。

		$op['new'] = ($options['new'] === false) ? false : true;

		//使用与更新域结合。为TRUE时，如果查询没有返回的文档，findAndModify命令创建一个新的文档，
		//默认值为false。在MongoDB中2.2中，findAndModify命令将返回NULL更新插入时为TRUE。
		$op['upsert'] = ($options['upsert'] === false) ? false : true;
		//排序
		if(isset($options['sort'])){
			$op['sort'] = $options['sort'];
		}

		$safe = false;
		if(isset($options['safe'])){
			$safe = $options['safe'];
		}

		$aModifiers = array('$set', '$unset', '$inc', '$push', '$pop', '$pull', '$ne',
							'$addToSet', '$each','$setOnInsert','$rename');
		foreach($new_object as $act => $row){
			if( ! in_array($act, $aModifiers, true)){
				unset($new_object[$act]);
				continue;
			}
		}
		$aFields = array();
		if(is_array($fields) && ! empty($fields)){
			foreach($fields as $f){
				$aFields[$f] = 1;
			}
		}
		//索引检测
		if( ! $this->checkIndex($table, $criteria)){
			//"no index";
		}
		
		try{
			$aRet = $collObj->findAndModify($criteria, $new_object, $aFields, $op);
		}catch(MongoResultException $e){
			$this->exceptionLog($e, "findAndModify");
			$errExcep = $e->getMessage();
		}catch(Exception $e){
			$this->exceptionLog($e, "findAndModify");
			$errExcep = $e->getMessage();
		}
		return $this->genRet($errExcep ? 0 : 1, $aRet, '', $errExcep, __line__);
	}

	//查询单条记录
	/**
	 * $criteria 查询对象描述，即查询条件条件  见 update
	 **/
	public function findOne($table, $criteria, $fields = array()){
		$sactime = microtime(true);
		$collObj = $this->selectCollection($table);
		if( ! $collObj){
			return $this->genRet(0, array(), $collObj, $this->err, __line__);
		}
		$aFields = array();
		if(is_array($fields) && ! empty($fields)){
			foreach($fields as $f){
				$aFields[$f] = 1;
			}
		}
		//索引检测
		if( ! $this->checkIndex($table, $criteria)){
			//"no index";
		}
		
		try{
			$aRet = $collObj->findOne($criteria, $aFields);
		}catch(MongoConnectionException $e){
			$this->exceptionLog($e, "findOne");
			$errExcep = $e->getMessage();
		}catch(MongoCursorException $e){
			$this->exceptionLog($e, "findOne");
			$errExcep = $e->getMessage();
		}catch(Exception $e){
			$this->exceptionLog($e, "findOne");
			$errExcep = $e->getMessage();
		}
		return $this->genRet($errExcep ? 0 : 1, $aRet, '', $errExcep, __line__);
	}

	//查询单条记录
	public function getOne($table, $criteria, $fields = array()){
		$aMongoRet = $this->findOne($table, $criteria, $fields);
		if(($aMongoRet['sta'] == 1) && $aMongoRet['data']){
			return (array)$aMongoRet['data'];
		}
		return array();
	}

	//统计文档符合条件的文档数
	public function count($table, $criteria, $limit = 0, $skip = 0){
		$collObj = $this->selectCollection($table);
		if( ! $collObj){
			return $this->genRet(0, 0, $collObj, $this->err, __line__);
		}
		//索引检测
		if( ! $this->checkIndex($table, $criteria)){
			//"no index";
		}
		
		try{
			$count = $collObj->count($criteria, $limit, $skip);
		}catch(MongoConnectionException $e){
			$this->exceptionLog($e, "count");
			$errExcep = $e->getMessage();
		}catch(MongoCursorException $e){
			$this->exceptionLog($e, "count");
			$errExcep = $e->getMessage();
		}catch(Exception $e){
			$this->exceptionLog($e, "count");
			$errExcep = $e->getMessage();
		}
		return $this->genRet($errExcep ? 0 : 1, $count, '', $errExcep, __line__);
	}

	//查询记录数
	public function getCount($table, $criteria, $limit = 0, $skip = 0){
		$aMongoRet = $this->count($table, $criteria, $limit, $skip);
		if(($aMongoRet['sta'] == 1) && isset($aMongoRet['data'])){
			return (int)$aMongoRet['data'];
		}
		return 0;
	}

	//聚合运算
	public function group($table, $keys, $initial ,$reduce, $options){
		$collObj = $this->selectCollection($table);
		if( ! $collObj){
			return $this->genRet(0, 0, $collObj, $this->err, __line__);
		}
		try{
			$aRet = $collObj->group($keys, $initial ,$reduce, $options);
		}catch(MongoConnectionException $e){
			$this->exceptionLog($e, "group");
			$errExcep = $e->getMessage();
		}catch(MongoCursorException $e){
			$this->exceptionLog($e, "group");
			$errExcep = $e->getMessage();
		}catch(Exception $e){
			$this->exceptionLog($e, "group");
			$errExcep = $e->getMessage();
		}
		return $this->genRet($errExcep ? 0 : 1, $aRet, '', $errExcep, __line__);
	}

	//查询
	/**
	 * $criteria 一般查询见 update 条件规则
	 * ==空间查询== http://docs.mongodb.org/manual/reference/operator/query/geoWithin/
	 **/
	public function find($table, $criteria, $fields = array(), $aSort = array(), $aLimit = array()){
		$collObj = $this->selectCollection($table);
		if( ! $collObj){
			return $this->genRet(0, array(), $collObj, $this->err, __line__);
		}
		$aData = array();
		$aFields = array();
		if(is_array($fields) && ! empty($fields)){
			foreach($fields as $f){
				$aFields[$f] = 1;
			}
		}
		//索引检测
		if( ! $this->checkIndex($table, $criteria)){
			//"no index";
		}
		
		try{
			$retCursor = $collObj->find($criteria, $aFields);
			if( (!empty($aSort) ) && is_array($aSort)){
				foreach($aSort as $k => $v){
					$aSort[$k] = $v > 0 ? 1 : -1;//1升序 -1 降序
				}
				$retCursor = $retCursor->sort( $aSort);
			}
			//跳过或限制返回数量
			if( (!empty($aLimit) ) && is_array($aLimit)){
				list($skip, $limit) = $aLimit;
				if($skip > 0){
					$retCursor = $retCursor->skip( $skip);
				}
				if($limit > 0){
					$retCursor = $retCursor->limit( $limit);
				}
			}
			while($arr = $retCursor->getNext()){
				$arr['_id'] = $retCursor->key();
				$aData[] = $arr;
			}
		}catch(MongoConnectionException $e){
			$this->exceptionLog($e, "find");
			$errExcep = $e->getMessage();
		}catch(MongoCursorException $e){
			$this->exceptionLog($e, "find");
			$errExcep = $e->getMessage();
		}catch(Exception $e){
			$this->exceptionLog($e, "find");
			$errExcep = $e->getMessage();
		}
		return $this->genRet($errExcep ? 0 : 1, $aData, '', $errExcep, __line__);
	}
	
	//查询多条记录
	public function getAll($table, $criteria, $fields = array(), $aSort = array(), $aLimit = array()){
		$aMongoRet = $this->find($table, $criteria, $fields, $aSort, $aLimit);
		if(($aMongoRet['sta'] == 1) && $aMongoRet['data']){
			return (array)$aMongoRet['data'];
		}
		return array();
	}

	//返回处理
	public function genRet($sta, $data, $resRet, $err = ''){
		$aRet = array();
		$aRet['sta'] = $sta;
		$aRet['data'] = $data;
		$aRet['resRet'] = $resRet;
		$aRet['err'] = $err;
		return $aRet;
	}

	public function exceptionLog($e, $appmsg, $errLevel = 100){
		$callRes = '';
		if($errLevel == 1000){
			$aTra = debug_backtrace();
			$aTra = array_pop( $aTra); //取最后一条
			$callRes = implode("\n", array(date('Y-m-d H:i:s'), '[mongodb fata] '.$appmsg, json_encode($e), $_SERVER["PHP_SELF"], 'file:'.$aTra['file'], 'line:'.$aTra['line'], 'function:'.$aTra['function'], 'args:'.implode(',', (array)$aTra['args']) ) );
			gzlogs::debug($callRes, 'gzmongo_err.txt');
			//...报警处理
			var_dump( $e->getMessage());
			die("mongo error");
		}else{
			foreach((array)$e->getTrace() as $i => $row){
				$args = var_export($row['args'], true);
				$callRes .= "[{$row['file']};{$row['line']};{$row['function']};{$args};] \n";
			}
			$this->log($e->getCode(), $e->getMessage(), $appmsg, $callRes);
			if($errLevel == 0){
				$error = $e->getCode() . $e->getMessage() . ' '.  $appmsg . date("H:i:s") . '[mongodb fata]';
				gzlogs::debug($error, 'gzmongo_err.txt');
				//...报警处理
				var_dump( $e->getMessage());
				die("mongo error");
			}
		}
	}

	//错误日志
	public function log($syscode, $sysmsg, $appmsg, $callRes){
		$time = date("Ymd H:i:s");
		if($syscode == 11000) return true;
		$error = "{$time};[syscode]:{$syscode};[sysmsg]:{$sysmsg};[appmsg]:{$appmsg};[callRes]:\n{$callRes}";
		gzlogs::debug($error, 'mumongo.txt');
	}
	public function __destruct(){
		$this->close();
		
		//$this->isReplicaSet() or $this->close();
	}
	
}