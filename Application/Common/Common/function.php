<?php
function p($vars, $label = '', $return = false) {
	/*
	    if (ini_get('html_errors')) {
	        $content = "<pre>\n";
	        if ($label != '') {
	            $content .= "<strong>{$label} :</strong>\n";
	        }
	        $content .= htmlspecialchars(print_r($vars, true));
	        $content .= "\n</pre>\n";
	    } else {
	        $content = $label . " :\n" . print_r($vars, true);
	    }
	    if ($return) { return $content; }
	    echo $content;
	    die;
	*/
	echo '<pre>';
	$p = func_get_args();
	if(func_num_args() === 1) $p = $p[0];
	print_r($p);
	die();
}

class functions{
	public static $curlErrorNo;		//curl错误码
	public static $curlError = 1;	//curl错误

	/**
	 * 设置COOKIE
	 * @param  $name
	 * @param  $value
	 * @param  $time 过期时间,0则关闭浏览器失效
	 */
	public static function setCookie( $name, $value, $time = 0, $path = '/' ){
		$expires = $time ? self::time() + (int) $time : 0;
		setcookie( $name, $value, $expires, $path );
	}

	/*生成唯一ID*/
	public static function creatUuid( $prefix = ''){
		$prefix.= mt_rand(1,9999999);
		$num = sprintf('%-010s',crc32( uniqid( $prefix, true)));
		$num = floor($num / 10);
		return date('y').sprintf('%03s',date('z')).$num.mt_rand(0,9) + 0;
	}

	//长整型16位(唯一)
	public static function getLongNumber() {
		list($usec, $sec) = explode(" ", microtime());
		list($whole, $decimal) = sscanf(number_format($usec, 6), '%d.%d');
		return intval($sec.$decimal);
	}

	/**
	 * 发送UTF-8头
	 */
	public static function header(){
		header( "Content-Type:text/html;charset=utf-8" );
	}

	public static function nocache(){
		header( "Pragma:no-cache" );
		header( "Cache-Type:no-cache, must-revalidate" );
		header( "Expires: -1" );
	}

	public static function dp3p(){
		header( "P3P:CP='ALL DSP CURa ADMa DEVa CONi OUT DELa IND PHY ONL PUR COM NAV DEM CNT STA PRE'" );
	}

	public static function keep(){
		header( "Connection:keep-alive" );
	}

	public static function getip(){
		if( isset( $_SERVER['HTTP_QVIA'] ) ){
			$ip = self::qvia2ip( $_SERVER['HTTP_QVIA'] );
			if( $ip ){
				return trim($ip);
			}
		}
		if( isset( $_SERVER['HTTP_CLIENT_IP'] ) && !empty( $_SERVER['HTTP_CLIENT_IP'] ) ){
			return self::checkIP( $_SERVER['HTTP_CLIENT_IP'] ) ? trim($_SERVER['HTTP_CLIENT_IP']) : '0.0.0.0';
		}
		if( isset( $_SERVER['HTTP_TRUE_CLIENT_IP'] ) && !empty( $_SERVER['HTTP_TRUE_CLIENT_IP'] ) ){
			return self::checkIP( $_SERVER['HTTP_TRUE_CLIENT_IP'] ) ? $_SERVER['HTTP_TRUE_CLIENT_IP'] : '0.0.0.0';
		}
		if( isset( $_SERVER['HTTP_X_FORWARDED_FOR'] ) && !empty( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ){
			$ip = strtok( $_SERVER['HTTP_X_FORWARDED_FOR'], ',' );
			do{
				$tmpIp = explode( '.', $ip );
				//-------------------
				// skip private ip ranges
				//-------------------
				// 10.0.0.0 - 10.255.255.255
				// 172.16.0.0 - 172.31.255.255
				// 192.168.0.0 - 192.168.255.255
				// 127.0.0.1, 255.255.255.255, 0.0.0.0
				//-------------------
				if( is_array( $tmpIp ) && count( $tmpIp ) == 4 ){
					if( ($tmpIp[0] != 10) && ($tmpIp[0] != 172) && ($tmpIp[0] != 192) && ($tmpIp[0] != 127) && ($tmpIp[0] != 255) && ($tmpIp[0] != 0) ){
						return trim($ip);
					}
					if( ($tmpIp[0] == 172) && ($tmpIp[1] < 16 || $tmpIp[1] > 31) ){
						return trim($ip);
					}
					if( ($tmpIp[0] == 192) && ($tmpIp[1] != 168) ){
						return trim($ip);
					}
					if( ($tmpIp[0] == 127) && ($ip != '127.0.0.1') ){
						return trim($ip);
					}
					if( $tmpIp[0] == 255 && ($ip != '255.255.255.255') ){
						return trim($ip);
					}
					if( $tmpIp[0] == 0 && ($ip != '0.0.0.0') ){
						return trim($ip);
					}
				}
			} while( ($ip = strtok( ',' ) ) );
		}
		if( isset( $_SERVER['HTTP_PROXY_USER'] ) && !empty( $_SERVER['HTTP_PROXY_USER'] ) ){
			return self::checkIP( $_SERVER['HTTP_PROXY_USER'] ) ? trim($_SERVER['HTTP_PROXY_USER']) : '0.0.0.0';
		}

		if( isset( $_SERVER['REMOTE_ADDR'] ) && !empty( $_SERVER['REMOTE_ADDR'] ) ){
			return self::checkIP( $_SERVER['REMOTE_ADDR'] ) ? trim($_SERVER['REMOTE_ADDR']) : '0.0.0.0';
		}else{
			return '0.0.0.0';
		}
	}

	/**
	 * 获取网通代理或教育网代理带过来的客户端IP
	 * @return        string/flase    IP串或false
	 */
	public static function qvia2ip( $qvia ){
		if( strlen( $qvia ) != 40 ){
			return false;
		}
		$ips = array( hexdec( substr( $qvia, 0, 2 ) ), hexdec( substr( $qvia, 2, 2 ) ), hexdec( substr( $qvia, 4, 2 ) ), hexdec( substr( $qvia, 6, 2 ) ) );
		$ipbin = pack( 'CCCC', $ips[0], $ips[1], $ips[2], $ips[3] );
		$m = md5( 'QV^10#Prefix' . $ipbin . 'QV10$Suffix%' );
		if( $m == substr( $qvia, 8 ) ){
			return implode( '.', $ips );
		}else{
			return false;
		}
	}

	/**
	 * 验证ip地址
	 * @param        string    $ip, ip地址
	 * @return        bool    正确返回true, 否则返回false
	 */
	public static function checkIP( $ip ){
		$ip = trim( $ip );
		$pt = '/^(([0-9]|[1-9][0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-5])\.){3}([0-9]|[1-9][0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-5])$/';
		if( preg_match( $pt, $ip ) === 1 ){
			return true;
		}
		return false;
	}

	/**
	 * 获取操作系统所有网卡的IP(除127.0.0.1),优先返回外网IP
	 * 私有IP：A类  10.0.0.0-10.255.255.255
	  B类  172.16.0.0-172.31.255.255
	  C类  192.168.0.0-192.168.255.255
	  127这个网段是环回地址
	 * @return String 包括所有IP.如果有多个IP则以-分割
	 */
	public static function osip(){
		preg_match_all( '/inet\s+addr:([\d\.]+)/i', `/sbin/ifconfig -a|grep -v '127.0.0.1'`, $matches );

		return implode( '-', array_unique( (array) $matches[1] ) );
	}

	/**
	 * 判断IP是否是外网: http://khlo.co.uk/index.php/25-php-determining-if-a-serverdomainip-is
	 * @param String $ip
	 * @return Boolean
	 */
	public static function isPublicIp( $ip ){
		$long = ip2long( $ip );
		return ($long >= 167772160 && $long <= 184549375) ||
				($long >= -1408237568 && $long <= -1407188993) ||
				($long >= -1062731776 && $long <= -1062666241) ||
				($long >= 2130706432 && $long <= 2147483647) ||
				($long == -1) ? false : true;
	}

	/**
	 * 返回浏览器信信息.ver为版本号,nav为浏览器
	 */
	public static function getbrowser(){
		$browsers = array ('mozilla','msie','gecko','firefox','konqueror','safari','netscape','navigator','opera','mosaic','lynx','amaya','omniweb','chrome');
		$nua = strtolower( $_SERVER['HTTP_USER_AGENT'] );
		$l = strlen( $nua );
		foreach( $browsers as $browser ){
			$n = stristr( $nua, $browser );
			if( strlen( $n ) > 0 ){
				$aInfo["ver"] = "";
				$aInfo["nav"] = $browser;
				$j = strpos( $nua, $aInfo["nav"] ) + $n + strlen( $aInfo["nav"] ) + 1;
				for(; $j <= $l; $j++ ){
					if( is_numeric( $aInfo["ver"] . $nua{$j} ) ){
						$aInfo["ver"] .= $nua{$j};
					}else{
						break;
					}
				}
			}
		}
		return $aInfo;
	}

	/**
	 * 获取操作系统
	 * @return String
	 */
	public static function getsystem(){
		$os = $_SERVER["HTTP_USER_AGENT"]; //浏览者操作系统及浏览器

		if( strpos( $os, "Windows NT 5.0" ) ) $os = "windows2000";
		elseif( strpos( $os, "Windows NT 6.1" ) ) $os = "windows7";
		elseif( strpos( $os, "Windows NT 6.0" ) ) $os = "windowsvista";
		elseif( strpos( $os, "Windows NT 5.1" ) ) $os = "windowsxp";
		elseif( strpos( $os, "Windows NT 5.2" ) ) $os = "windows2003";
		elseif( strpos( $os, "Windows NT" ) ) $os = "windowsnt";
		elseif( strpos( $os, "Windows 9" ) ) $os = "windows98";
		elseif( strpos( $os, "unix" ) ) $os = "unix";
		elseif( strpos( $os, "linux" ) ) $os = "linux";
		elseif( strpos( $os, "SunOS" ) ) $os = "sunos";
		elseif( strpos( $os, "BSD" ) ) $os = "freebsd";
		elseif( strpos( $os, "Mac" ) ) $os = "mac";
		else $os = "other";

		return $os;
	}

	/**
	 * 根据magic_quote判断是否为变量添加斜杠
	 * @param mix $mixVar
	 * @return mix
	 */
	public static function magic_quote( $mixVar ){
		if( !get_magic_quotes_gpc() ){
			return self::add_slashes($mixVar);
		}else{
			return $mixVar;
		}
	}

	public static function add_slashes($mixVar){
		if( is_array($mixVar) ){
			foreach($mixVar as $key => $value){
				$temp[$key] = self::add_slashes($value);
			}
		}else{
			$temp = addslashes($mixVar);
		}
		return $temp;
	}

	/**
	 * 根据magic_quote判断是否为变量去除斜杠
	 * @param mix $mixVar
	 * @return mix
	 */
	public static function no_quote( $mixVar ){
		static $no_quote_counter = 0;
		if( ++$no_quote_counter > 500 ){
			die( 'possible deep recursion attack!' );
		}
		if( get_magic_quotes_gpc() ){
			if( is_array( $mixVar ) ){
				foreach( $mixVar as $key => $value ){
					$temp[$key] = self::no_quote( $value );
				}
			}else{
				$temp = stripslashes( $mixVar );
			}
			return $temp;
		}else{
			return $mixVar;
		}
	}

	/**
	 * 计算字符串的CRC32值.范围为0~4294967296
	 */
	public static function crc32( $str ){
		return sprintf( "%u", crc32( $str ) );
	}

	/**
	 * arr的长和宽等比例缩小至$arrTo resize(array($array['width'],$array['height']), array(160,120))
	 * @return Array
	 */
	public static function resize( $arr, $arrTo ){
		$arr[0] = $arr[0] > 10 ? $arr[0] : $arrTo[0];
		$arr[1] = $arr[1] > 10 ? $arr[1] : $arrTo[1];
		$arrTo[0] = $arrTo[0] <= 0 ? 160 : $arrTo[0];
		$arrTo[1] = $arrTo[1] <= 0 ? 120 : $arrTo[1];
		$temp = $arr;

		if( $arr[0] > $arrTo[0] ){ //如果宽度超出
			$temp[0] = $arrTo[0];
			$temp[1] = (int) ($temp[0] * $arr[1] / $arr[0]);
			if( $temp[1] > $arrTo[1] ){
				$temp[1] = $arrTo[1];
				$temp[0] = (int) ($arr[0] * $temp[1] / $arr[1]);
			}
		}
		if( $arr[1] > $arrTo[1] ){ //如果高度超出
			$temp[1] = $arrTo[1];
			$temp[0] = (int) ($arr[0] * $temp[1] / $arr[1]);
			if( $temp[0] > $arrTo[0] ){
				$temp[0] = $arrTo[0];
				$temp[1] = (int) ($temp[0] * $arr[1] / $arr[0]);
			}
		}
		return $temp;
	}

	/**
	 * 返回UNIX时间戳
	 * @param boolen $float 是否精确到微秒
	 * @return int/float
	 */
	public static function time( $float = false ){
		return $float ? microtime( true ) : time();
	}

	/**
	 * 正整型
	 */
	public static function uint( $num ){
		return max( 0, (int) $num );
	}

	/**
	 * 完成日志
	 */
	public static function shutdownlog( $params ){
		$time = round( functions::time( true ) - $params['time'], 10 );
		$log = date( 'Y-m-d H:i:s' ) . "\ttime:" . $time . "\tfile:" . $params['file'] . (empty( $GLOBALS['method'] ) ? '' : "\tmethod:" . $GLOBALS['method']) . (empty( $GLOBALS['mid'] ) ? '' : "\tmid:" . $GLOBALS['mid']);
		($time >= $params['min']) ? functions::file( $log, 'shutdown.txt' ) : ''; //长于配置值的将会被记录
	}

	/**
	 * 写日志
	 * @param unknown_type $params
	 * @param unknown_type $file
	 */
	public static function file( $params, $file = 'logs.txt'){
		clearstatcache();
		$file = PATH_DAT . $file . '.php';
		$size = file_exists( $file ) ? @filesize( $file ) : 0;
		$flag = $size < 1024 * 1024; //标志是否附加文件.文件控制在1M大小
		$prefix = $size && $flag ? '' : "<?php (isset(\$_GET['p']) && (md5('&%$#'.\$_GET['p'].'**^')==='8b1b0c76f5190f98b1110e8fc4902bfa')) or die();?>\n"; //有文件内容并且非附加写
		is_scalar( $params ) or ($params = var_export( $params, true )); //是简单数据

		@file_put_contents( $file, $prefix . $params . "\n", $flag ? FILE_APPEND : null  );
	}

	/**
	 * 检查邮件地址是否合法
	 */
	public static function checkEmail( $email ){
		return preg_match( '/^([_a-z0-9]([\._a-z0-9-])*)@([a-z0-9]{2,}(\.[a-z0-9-]{2,})*\.[a-z]{2,3})$/i', $email ) ? true : false;
	}

	/**
	 * 检查是否全中文.仅限UTF-8编码
	 * @param String $string
	 * @return Boolean
	 */
	public static function checkChinese( $string ){
		return preg_match( "/^[\x{4e00}-\x{9fa5}]+$/u", $string ) ? true : false;
	}

	/**
	 * 检查某个PHP文件是否有语法错误
	 * @param String $filename 文件的绝对路径
	 * @return Boolean
	 */
	public static function checkSyntax( $filename ){
		if( !$contents = @file_get_contents( $filename ) ){
			return false;
		}
		return @eval( 'return true;?>' . $contents ) ? true : false;
	}

}
?>