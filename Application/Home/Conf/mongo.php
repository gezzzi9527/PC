<?php
return array(
	//'配置项'=>'配置值'
	'MongoUser' => 'root',
	'MongoPwd' => '123456',
	'MongoSer' => array( 'replicaSet' => 'pktest', 'servers' => array ( 0 => array ( 0 => '127.0.0.1', 1 => 27017, ), 1 => array ( 0 => '127.0.0.1', 1 => 27017, ), ), ),
);