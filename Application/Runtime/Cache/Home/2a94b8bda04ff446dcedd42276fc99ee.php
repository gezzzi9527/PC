<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>格子世界</title>
<link href="/dev/gezzzi/Public/css/index.css" rel="stylesheet" type="text/css" />
<script type="text/javascript" src="/dev/gezzzi/Public/js/jquery-1.11.1.min.js"></script>
<script type="text/javascript" src="/dev/gezzzi/Public/js/index.js"></script>
</head>

<body>
<a href= "/dev/gezzzi/index.php/Home/Index/<?php echo ($regUrl); ?>" ><?php echo ($regName); ?></a>
<a href= "/dev/gezzzi/index.php/Home/Index/<?php echo ($loginUrl); ?>" ><?php echo ($loginName); ?></a>
<div class="top" id="top" style="display:none">
    <div class="nick pos" id="nick">用户昵称</div>
    <div class="scroll pos" id="scroll">参照谷歌地图缩放控件</div>
    <div class="gps pos" id="pgs">格子坐标:X-Y-Z</div>
</div>
<div class="gezzzi" id="gezzzi" style="display:none">
	<div class="f1">
		<div class="ff" id="3-2-2"></div>
        <div class="ff" id="2-2-2"></div>
        <div class="ff" id="1-2-2"></div>
        <div class="ff" id="0-2-6"></div>
        <div class="ff" id="1-2-1"></div>
        <div class="ff" id="2-2-1"></div>
        <div class="ff" id="3-2-1"></div>
    </div>
    <div class="f2">
		<div class="ff" id="3-1-2"></div>
        <div class="ff" id="2-1-2"></div>
        <div class="ff" id="1-1-2"></div>
        <div class="ff" id="0-1-6"></div>
        <div class="ff" id="1-1-1"></div>
        <div class="ff" id="2-1-1"></div>
        <div class="ff" id="3-1-1"></div>
    </div>
    <div class="f3">
		<div class="ff" id="3-0-7"></div>
        <div class="ff" id="2-0-7"></div>
        <div class="ff" id="1-0-7"></div>
        <div class="origin" id="0-0-0"></div>
        <div class="ff" id="1-0-5"></div>
        <div class="ff" id="2-0-5"></div>
        <div class="ff" id="3-0-5"></div>
    </div>
    <div class="f4">
		<div class="ff" id="3-1-3"></div>
        <div class="ff" id="2-1-3"></div>
        <div class="ff" id="1-1-3"></div>
        <div class="ff" id="0-1-8"></div>
        <div class="ff" id="1-1-4"></div>
        <div class="ff" id="2-1-4"></div>
        <div class="ff" id="3-1-4"></div>
    </div>
    <div class="f5">
		<div class="ff" id="3-2-3"></div>
        <div class="ff" id="2-2-3"></div>
        <div class="ff" id="1-2-3"></div>
        <div class="ff" id="0-2-8"></div>
        <div class="ff" id="1-2-4"></div>
        <div class="ff" id="2-2-4"></div>
        <div class="ff" id="3-2-4"></div>
    </div>
</div>
<div class="bottom" id="bottom" style="display:none">
    <div class="friend pos" id="friend">好友</div>
    <div class="me pos" id="me">我</div>
    <div class="getin pos" id="getin">进入格子</div>
</div>
<div style="display:none" id="tipss" class="tipss">
    <p>这是格子世界的原点</p>
</div>
</body>
</html>