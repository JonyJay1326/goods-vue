 //避免函数命名冲突的问题，采用匿名函数来实现,并自调用
  (function(){
      //通过id来获取对象
     var $=function(id){
	   return document.getElementById(id);
	 }
	  //通过name属性的值来获取对象
	  $.getName=function(name){    //在js中一切都是对象，对象的属性同样可以是一个对象，同样可以指向函数的地址
	     return document.getElementsByName(name);
	  }
     //通过标签名称来获取对象
	  $.getTag=function(tag){
	     return document.getElementsByTagName(tag);
	  }
   
    //创建ajax对象的方法
	 $.init=function(){
		 //w3c
	     try{
		    return new XMLHttpRequest();
		 }catch(e){
		  
		 }
		 //IE
          try{
		    return new ActiveXObject('Microsoft.XMLHTTp');
		 }catch(e){
		  
		 } 
	 }   
	 //封装ajax的get方法
        /*
          url : 请求的服务器端的地址
		  data : 客户端需要传递到服务器端的参数
          callback: 客户端对回调的函数值自定义的一个处理函数
		  type:  从服务器端返回的数据类型 
		*/
	  $.get=function(url,data,callback,type=''){   
	      var xhr=$.init(); //获取ajax对象
		   //回调函数
		  xhr.onreadystatechange=function(){
		      if(xhr.readyState==4&&xhr.status==200){
			      //返回服务器端的结果 由用户自定义来进行处理，通过函数来自己处理
				     //对服务器端返回的数据进行处理  text/json
                  if(type==''){
				     msg=xhr.responseText;
				  }

				  if(type=='text'){
				     msg=xhr.responseText;
				  }

				  if(type=='json'){
				     msg=JSON.parse(xhr.responseText);
				  }
                  callback(msg);
			  }
		  }
          //初始化
            //判断是否需要传递参数
			if(data=='null'){
			   url=url;
			}else{
			   url=url+"?"+data;
			}
		  xhr.open('get',url);
		  //发送请求
		  xhr.send(null);  
	  }
	    
    //封装 ajax的post方法
      /*
          url : 请求的服务器端的地址
		  data : 客户端需要传递到服务器端的参数
          callback: 客户端对回调的函数值自定义的一个处理函数
		  type:  从服务器端返回的数据类型 
		*/
 
	  $.post=function(url,data,callback,type=''){
	      var xhr=$.init(); //获取ajax对象
		   //回调函数
		  xhr.onreadystatechange=function(){
		      if(xhr.readyState==4&&xhr.status==200){
			      //返回服务器端的结果 由用户自定义来进行处理，通过函数来自己处理
				     //对服务器端返回的数据进行处理  text/json
                  if(type==''){
				     msg=xhr.responseText;
				  }

				  if(type=='text'){
				     msg=xhr.responseText;
				  }

				  if(type=='json'){
				     msg=JSON.parse(xhr.responseText);
				  }
                  callback(msg);
			  }
		  }
	      //初始化
		   xhr.open('post',url);
		  //设置请求头
		  xhr.setRequestHeader('content-type','application/x-www-form-urlencoded');//设置post提交的响应头
          //发送请求
		  xhr.send(data);
	  
	  } 
  
    //把$变为全局变量
	 window.$=$;
  })();