var selectedId = '0-0-0';
var A = {test: function(id){alert(id)},  adds: function(a, b){alert(a+b)}, var subs = function(a, b){alert(a-b)};};
//$.extend({test: function(id){alert(id)}});
//var f = function(id){alert(id);}
$(document).ready(function(){
  $(".ff, .origin").click(function(ev){
    selectedId = $(this).attr("id");

    if (selectedId == "0-0-0"){
    	var divcss = {
  		  left: ev.pageX+1,
  		  top: ev.pageY+1, 
  		};
		  $("#tipss").css(divcss).css('display', "block");
    } else {
    	$("#tipss").css('display', "none"); 
    } 

  });

  $(".ff").mouseover(function(){
  	$(this).css('background-image', 'url(images/22.jpg)');
  });

  $(".ff").mouseout(function(){
        $(this).css('background-image', 'url(images/21.jpg)');
  });

  $(".origin").mouseover(function(){
  	$(this).css('background-image', 'url(images/12.jpg)');
  });

  $(".origin").mouseout(function(){
  	$(this).css('background-image', 'url(images/11.jpg)');
  	$("#tipss").css('display', "none");
  });

  $("#getin").click(function() {
  	if (selectedId == "0-0-0"){
      location.href = "chat.html";
    }
  		
  });
});

