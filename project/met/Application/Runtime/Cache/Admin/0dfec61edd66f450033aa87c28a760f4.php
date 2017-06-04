<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8"/>
        <title>东台长安汽车后台管理系统</title>
        <meta name="renderer" content="webkit" />
        <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
        <link href="/Public/css/bootstrap.min.css" rel="stylesheet" type="text/css"/>
        <link href="/Application/Admin/View/Public/static/css/sb-admin.css" rel="stylesheet" type="text/css"/>
        <link href="/Application/Admin/View/Public/static/css/base.css" rel="stylesheet" type="text/css"/>
        <link href="/Application/Admin/View/Public/static/css/login.css" rel="stylesheet" type="text/css"/>
    </head>
    <body class="login">
        <!-- NOTICE BEGIN -->
        <div id="noticeCnr">
            <div v-html="consoleMsg"></div>
            <div v-show="confirm.show" class="confirm-cnr" style="display:none;">
                <div class="confirm-cnt">
                    <span v-html="confirm.msg"></span>
                    <div class="confirm-btn-cnr">
                        <div class="confirm-btn" @click="confirm.confirm()">确定</div>
                        <div class="confirm-btn" @click="confirm.cancel()">取消</div>
                        <div class="clear"></div>
                    </div>
                </div>
            </div>
            <div v-show="alert.show" class="alert-cnr" :class="alert.type" style="display:none;">
                <span v-html="alert.msg"></span>
            </div>
        </div>
        <!-- NOTICE END -->
        <div class="content">
            <form class="login-form">
                <h3 class="form-title">登录后台管理系统</h3>

                <div class="form-group">
                    <label class="control-label visible-ie8 visible-ie9">用户名</label>
                    <div class="input-icon">
                        <i class="fa fa-user"></i>
                        <input class="form-control placeholder-no-fix" type="text" autocomplete="off" placeholder="用户名" name="username" id="username"/>
                    </div>
                </div>
                <div class="form-group">
                    <label class="control-label visible-ie8 visible-ie9">密码</label>
                    <div class="input-icon">
                        <i class="fa fa-lock"></i>
                        <input class="form-control placeholder-no-fix" type="password" autocomplete="off" placeholder="密码" name="password" id="password"/>
                    </div>
                </div>
                <div class="form-actions">
                    <div id="subBtn" class="btn btn-dt pull-right">
                        登录 
                    </div>
                </div>                
                <div class="create-account">
                    <p>
                        2016 &copy; QiMeng Group Co,.Ltd.
                    </p>
                </div>
            </form>
        </div>

        <script src="/Public/js/jquery.min.js" type="text/javascript"></script>
        <script src="/Public/js/bootstrap.min.js" type="text/javascript"></script>
        <script src="/Public/js/jquery.backstretch.min.js" type="text/javascript"></script>
        <script src="/Public/js/md5.js" type="text/javascript"></script>
        <script src="/Public/js/vue.min.js" type="text/javascript"></script>
        <script src="/Application/Admin/View/Public/static/js/base.js" type="text/javascript"></script>
        <script src="/Application/Admin/View/Public/static/js/login.js" type="text/javascript"></script>
        <script>
            jQuery(document).ready(function () {
                $.backstretch([
                    "/Public/img/2_1.jpg",
                    "/Public/img/3_1.jpg",
                    "/Public/img/4_1.jpg"
                ], {
                    fade: 1000,
                    duration: 8000
                });
            });
        </script>
    </body>
</html>