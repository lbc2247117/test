<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html>
<head lang="en">
    <meta charset="UTF-8">
    <title>管理员登陆</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Bootstrap core CSS -->
    <link href="/active/Application/Admin/View//Public/static/css/bootstrap.css" rel="stylesheet">

    <!-- Add custom CSS here -->
    <link href="/active/Application/Admin/View//Public/static/css/sb-admin.css" rel="stylesheet">
    <link rel="stylesheet" href="/active/Application/Admin/View//Public/static/font-awesome/css/font-awesome.min.css">
</head>
<body>
<div class="container">
    <div class="row">
        <div class="col-md-4 col-md-offset-4">
            <h1>管理员登陆</h1>

            <form action="<?php echo U('login/login');?>" method="post">
                <div class="form-group">
                    <label for="exampleInputUser">用户名</label>
                    <input type="text" name="username" class="form-control" id="exampleInputUser" placeholder="用户名">
                </div>
                <div class="form-group">
                    <label for="exampleInputPassword">密码</label>
                    <input type="password" name="password" class="form-control" id="exampleInputPassword" placeholder="密码">
                </div>
                <div class="form-group">
                    <label for="exampleInputCode">验证码</label>
                    <div class="row">
                        <div class="col-md-8">
                            <input type="text"  name="verify" class="form-control" id="exampleInputCode" placeholder="验证码">
                        </div>
                        <div class="col-md-4">
                            <a href="javascript:void(0)"><img class="verify" src="<?php echo U('login/verify');?>" alt="点击刷新"/></a>
                        </div>
                    </div>
                </div>
                <button type="submit" class="btn btn-default">登陆</button>
            </form>
        </div>
    </div>
</div>
<script src="/active/Public/js/jquery.min.js"></script>
<script>
    $(function(){
        $(".verify").click(function(){
            var src = "<?php echo U('login/verify');?>";
            var random = Math.floor(Math.random()*(1000+1));
            $(this).attr("src",src+"?random="+random);

        });
    })
</script>
</body>
</html>