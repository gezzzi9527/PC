<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>Gezzzi</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta name="description" content="Subscription Landing page" />
<link href="/dev/gezzzi/Public/css/reset.css" rel="stylesheet" type="text/css" />
<link href="/dev/gezzzi/Public/css/master.css" rel="stylesheet" type="text/css" />
<link href="/dev/gezzzi/Public/css/fonts.css" rel="stylesheet" type="text/css" />
<!-- jQuery Library + ALL jQuery Tools -->
<script src="/dev/gezzzi/Public/js/jquery.tools.min.js" type="text/javascript" ></script>
<!-- fancy box img viewer -->
<script type="text/javascript" src="/dev/gezzzi/Public/js/fancybox/jquery.fancybox-1.3.1.pack.js"></script>
<script type="text/javascript" src="/dev/gezzzi/Public/js/fancybox/jquery.easing-1.3.pack.js"></script>
<link rel="stylesheet" type="text/css" href="/dev/gezzzi/Public/js/fancybox/jquery.fancybox-1.3.1.css" media="screen" />
<!-- form validation -->
<script src="/dev/gezzzi/Public/js/jquery.validate.js" type="text/javascript"></script>
<script type="text/javascript">
$(document).ready(function() {
	$("#myform").validate();
});
</script>  
</head>

<body>
<!-- WRAPPER -->
<div id="wrapper">
        <!-- HEADER -->
        <div id="header">
                     
        </div>
        <!-- /HEADER -->
        <!-- MAIN GREEN AREA -->
        <div id="main_container">
        <!-- form -->
        <form action="/dev/gezzzi/index.php/Home/Index/insert" method="post" id="myform" name="myform"class="expose" > 
                <fieldset class="col_f_1">	
                        <label>用户名</label><input type="text" name="name"/> 
                        <label>密  码</label><input type="password" name="passwd"/>
                        <label>确认密码</label> <input type="password" name="repasswd" onblur="check_repasswd();"/>
                        <label>验 证 码</label><input name="verify" type="text" maxlength=4 style="height:23px; width:60px; float:left"  class="required" /><img style="padding-left:10px;float:left" src="/dev/gezzzi/index.php/Home/Index/verify/" onclick="this.src='/dev/gezzzi/index.php/Home/Index/verify/'+Math.random();" class="required"/>
                </fieldset>
                 <div class="clr"></div>
                 <hr />
                 <button type="submit">光 速 注 册</button>
        </form>	
        <!--/form -->
         <div id="header_content">
                     <h1>抢先注册<strong>格子世界</strong></h1>
                     <h2>你值得拥有</h2>
         </div>
         <div class="clr"></div>
     </div>
     <!-- MAIN GREEN AREA -->
     <div id="shadow_form"></div>
        
        <!-- FEATURES -->
        <div id="features">
           <div class="clr"></div>
        </div>
        <!-- /FEATURES -->
        <!-- LEFT COLUMN -->
		<div class="col_1">
        
          				
  		 </div>
  		 <!-- /LEFT COLUMN -->
         <!-- RIGHT COLUMN-->
         <div class="col_2">
                   
        </div>
        <!-- / RIGHT COLUMN-->
        <div class="clr"></div>  
</div>
<!-- / WRAPPER -->
            
<!-- FOOTER-->

<!-- /FOOTER-->
</div>
</body>

<script type="text/javascript">

//重复密码验证
function check_repasswd()
{
    //var pwd_cfError=document.getElementById("pwd_cfError");
    var pwd=document.myform.passwd.value;
    var pwd_cf=document.myform.repasswd.value;

    if(pwd!=pwd_cf){
    //pwd_cfError.innerHTML="两次输入的密码不一致！";
    alert("两次输入的密码不一致！");
    }
    else{
    //pwd_cfError.innerHTML="";
    }
}
</script>
</html>