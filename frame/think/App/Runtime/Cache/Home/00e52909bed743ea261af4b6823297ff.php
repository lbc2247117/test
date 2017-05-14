<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html>
    <head>
        <title><?php echo C('username');?></title>
        <script src="../../../../../js/jquery-1.9.1.min.js" type="text/javascript"></script>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
    </head>
    <body>
        <form method="post" action="<?php echo U('Home/Index/AjaxSend');?>">
        <a href="<?php echo U('Home/Index/login');?>">登录</a>
        <input type="text" id="username" />
        <input type="submit" id="send" value="提交" />
        </form>
        <script type="text/javascript">
            $(function(){
//                $('#send').click(function(){
//                  var obj=new Object;
//                  obj.username=$('#username').val();
//                  $.post("<?php echo U('Home/Index/AjaxSend');?>",JSON.stringify(obj),function(data){
//                      data=JSON.parse(data);
//                      alert(data.username);
//                  });
//                  
//                });
            });
        </script>
    </body>
</html>