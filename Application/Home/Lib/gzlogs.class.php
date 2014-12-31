<?php

class gzlogs{
	/**
	 * 写记录
	 * @param String/Array $params 要记录的数据
	 * @param String $fname 文件名.
	 * @param Int $fsize 文件大小M为单位.默认为1M
	 * @return null
	 */
	public function debug( $params, $fname = 'debug.txt', $fsize = 1){
		is_scalar( $params ) or ($params = var_export( $params, true ));
		if( !$params ){
			return false;
		}
		clearstatcache();
		$file = C('PATH_DAT') . $fname . '.php';
		$dir = dirname( $file );
		if( !is_dir( $dir ) ) mkdir( $dir, 0775, true );
		$size = file_exists( $file ) ? @filesize( $file ) : 0;
		$flag = $size < max( 1, $fsize ) * 1024 * 1024; //标志是否附加文件.文件控制在1M大小
		if( !$flag){//文件超过大小自动备份
			$bak = $dir . '/bak/';
			if( !is_dir( $bak ) ) mkdir( $bak, 0775, true );
			$fname = explode( '/', $fname );
			$fname = $fname[count( $fname ) - 1];
			$bak .= $fname . '-' . date( 'YmdHis' ) . '.php';
			copy( $file, $bak );
		}
		@file_put_contents( $file, $params . "\n", $flag ? FILE_APPEND : null  );
	}
}