<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html lang="zh-CN">
    <head>
        <meta http-equiv="content-type" content="text/html;charset=utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=Edge,chrome=1">
        <meta name="viewport" content="width=device-width,initial-scale=1,user-scalable=no"> 
        <meta name="apple-mobile-web-app-capable" content="yes"> 
        <meta name="apple-mobile-web-status-bar-style" content="block"> 
        <meta name="fromat-detecition" content="telephone=no">
        <meta name="keywords" content="">
        <meta name="description" content="">
        <title>参数类型管理</title>
        <link rel="stylesheet" href="/Public/css/bootstrap.min.css">
        <link rel="stylesheet" href="/Application/Admin/View/Public/static/css/sb-admin.css">
        <link rel="stylesheet" href="/Application/Admin/View/Public/static/font-awesome/css/font-awesome.min.css">
        <link rel="icon" href="/favicon.jpg">
        <script src="/Public/js/jquery.min.js"></script>
        <!-- <script src="/Application/Admin/View/Public/static/js/basePermit.js"></script> -->
    <link rel="stylesheet" type="text/css" href="/Application/Admin/View/Public/static/css/base.css" />
</head>
<body>
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
    <div id="wrapper">
        <nav class="navbar navbar-inverse navbar-fixed-top" role="navigation">
            <div>
                <div class="navbar-header">
                    <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-ex1-collapse">
                        <span class="sr-only"></span>
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                    </button>
                    <a class="navbar-brand" href="<?php echo U('index/index');?>">
                        <div class="title-sce">东台长安汽车后台管理系统</div>

                    </a>
                </div>
                <div class="collapse navbar-collapse navbar-ex1-collapse">
                    <ul class="nav navbar-nav side-nav">
    <?php if(is_array($auth["menus"])): $i = 0; $__LIST__ = $auth["menus"];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$item): $mod = ($i % 2 );++$i;?><li class="dropdown">
            <a href="<?php echo ($item["href"]); ?>"><i class="<?php echo ($item["icon"]); ?>"></i> <?php echo ($item["text"]); ?></a>
        <?php if(is_array($item["childs"])): $i = 0; $__LIST__ = $item["childs"];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$child): $mod = ($i % 2 );++$i;?><ul class="dropdown-menu">
                <li class="nav-lvl-3" id="<?php echo ($child["pageid"]); ?>">
                    <a href="<?php echo ($child["href"]); ?>"> <i class="<?php echo ($child["icon"]); ?>"></i><?php echo ($child["text"]); ?></a>
                </li>
            </ul><?php endforeach; endif; else: echo "" ;endif; ?>
        </li><?php endforeach; endif; else: echo "" ;endif; ?>
</ul>

                    <ul class="nav navbar-nav navbar-right navbar-user">
                        <li class="dropdown user-dropdown">
                            <a href="#" class="dropdown-toggle" data-toggle="dropdown"><i class="fa fa-user"></i> 你好,<?php echo ($userinfo["username"]); ?> <b class="caret"></b></a>
                            <ul class="dropdown-menu">
                                <li><a id="editPass" href="javascript:void(0);"><i class="fa fa-gear"></i>修改密码</a></li>
                                <li><a href="<?php echo U('Login/logout');?>"><i class="fa fa-power-off"></i> 退出</a></li>
                            </ul>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>
<div id="pageTitle">
    <div class="page-title">参数类型管理</div>
</div>
<div id="pageWrapper">
    <div id="searchCnr">
        <div class="pull-right">
            <div class="btn btn-laia" @click="showEdit(-1)"  >添加</div>
            <div class="btn btn-default" @click="delBanner()">删除</div>
        </div>
    </div>
    <div class="clearfix"></div>
    <div class="table-responsive">
        <table class="table table-striped table-hover">
            <thead>
                <tr>
                    <th></th>
                    <th>参数类型ID</th>
                    <th>参数类型名称</th>
                    <th>备注</th>
                    <th>排序</th>
                    <th>操作</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="(idx, obj) in tableData">
                    <td>
                        <div class="laia-chk">
                            <input type="checkbox" :value="obj.id" :id="obj.id" v-model="checkedArr">
                            <label :for="obj.id"></label>
                        </div>
                    </td>
                    <td>{{obj.id}}</td>
                    <td>{{obj.name}}</td>
                    <td>
                        {{obj.remark}}
                    </td>
                    <td>
                        {{obj.sort}}
                    </td>
                    <td>
                        <div class="btn btn-link" @click="showEdit(idx)">编辑</div>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
    <div>
        <ul v-show="showPageNav" class="pagination">
            <li :class="curPage < 2 ? 'disabled' : ''" @click="pageNav(curPage-1)">
                <a href="javascript:;">上一页</a>
            </li>
            <li v-for="obj in pageShowData" :class="curPage == obj.num ? 'active' : ''" @click="pageNav(obj.num)">
                <a href="javascript:;">{{obj.num}}</a>
            </li>
            <li :class="curPage < pageAllData.length ? '' : 'disabled'" @click="pageNav(curPage+1)">
                <a href="javascript:;">下一页</a>
            </li>
        </ul>

        <div id="pageCount" class="btn bg-laia">
            <span>第 {{curPage}} 页 | </span>
            <span>共 {{pageCount}} 页</span>
        </div>
    </div>
    <div class="laia-mask" style='display: none' v-show='editVisible'>
        <div class="laia-edit-cnr">
            <div class="edit-icon"></div>
            <div class="edit-cnt">
                <div class="cnt-body">
                    <form id="editform" method="post" enctype="multipart/form-data" class="form-horizontal">
                        <input type="hidden" name="id" v-model="editobj.id">
                        <div class="form-group">
                            <label  class="col-sm-2 control-label">名称</label>
                            <div class="col-sm-10">
                                <input type="text" name="name" class="form-control" v-model="editobj.name" placeholder="名称">
                            </div>
                        </div>

                        <div class="form-group">
                            <label  class="col-sm-2 control-label">备注</label>
                            <div class="col-sm-10">
                                <textarea type="text" name="remark" class="form-control" v-model="editobj.remark" placeholder="备注"></textarea>
                            </div>
                        </div>
                        <div class="form-group">
                            <label  class="col-sm-2 control-label">序号</label>
                            <div class="col-sm-10">
                                <input type="text" name="sort" class="form-control" v-model="editobj.sort" placeholder="只能是整数，数字越小越靠前">
                            </div>
                        </div>
                    </form>
                    <div class="clearfix"></div>
                    <div class="pull-right">
                        <div class="btn btn-laia btn-sm" @click="saveData">保存</div>
                        <div class="btn btn-default  btn-sm" @click="hideEdit">关闭</div>
                    </div>
                    <div class="clearfix"></div>
                </div>
            </div>
        </div>
    </div>
</div>
<div id="editPassDiv" class="laia-mask" style='display: none'>
    <div class="laia-edit-cnr">
        <div class="edit-icon"></div>
        <div class="edit-cnt">
            <div class="cnt-body">
                <form id="passform" method="post" enctype="multipart/form-data" class="form-horizontal">
                    <div class="form-group">
                        <label  class="col-sm-2 control-label">旧密码</label>
                        <div class="col-sm-10">
                            <input type="password" id="oldPass" class="form-control"  placeholder="旧密码">
                        </div>
                    </div>
                    <div class="form-group">
                        <label  class="col-sm-2 control-label">新密码</label>
                        <div class="col-sm-10">
                            <input type="password" id="newPass" class="form-control"  placeholder="新密码">
                        </div>
                    </div>
                    <div class="form-group">
                        <label  class="col-sm-2 control-label">重复密码</label>
                        <div class="col-sm-10">
                            <input type="password" id="rePass"  class="form-control"  placeholder="重复密码">
                        </div>
                    </div>
                </form>
                <div class="clearfix"></div>
                <div class="pull-right">
                    <div class="btn btn-laia btn-sm" id="saveBtn">保存</div>
                    <div class="btn btn-default  btn-sm" id="hideBtn">关闭</div>
                </div>
                <div class="clearfix"></div>
            </div>
        </div>
    </div>
</div>

<div id='loading' style='display: none'>
    <div>
        <img src="/Public/img/loading.gif" align=""><span>&nbsp;&nbsp;loading...</span>
    </div>
</div>
<div id="footer">© 2016 版权归启梦网络所有</div>
</div>
<!-- JavaScript -->
<script src="/Public/js/bootstrap.min.js"></script>
<script src="/Public/js/vue.min.js"></script>
<script src="/Public/js/md5.js"></script>
<script src="/Application/Admin/View/Public/static/js/base.js"></script>
<?php $str = "/Public/js/uploadPreview.js,/Public/js/vue.min.js,/Public/js/jquery.form.js,/Application/Admin/View/Public/static/js/product/param.js"; $arr = explode(",", $str); ?>
<?php if(is_array($arr)): foreach($arr as $key=>$src): ?><script src="<?php echo ($src); ?>"></script><?php endforeach; endif; ?>
<script>
    $(function () {
        $('#editPass').click(function () {
            $('#editPassDiv').show();
        });
        $('#hideBtn').click(function () {
            $('#editPassDiv').hide();
        });
        $('#saveBtn').click(function () {
            var _oldPass = $('#oldPass').val();
            _oldPass = _oldPass.trim();
            var _newPass = $('#newPass').val();
            _newPass = _newPass.trim();
            var _rePass = $('#rePass').val();
            _rePass = _rePass.trim();
            if (_oldPass == '') {
                BASE.showAlert('请输入旧密码');
                return false;
            }
            if (_newPass == '') {
                BASE.showAlert('请输入新密码');
                return false;
            }
            if (_rePass == '') {
                BASE.showAlert('请输入确认密码');
                return false;
            }
            if (_newPass !== _rePass) {
                BASE.showAlert('两次密码输入不一致');
                return false;
            }
            if (_oldPass == _newPass) {
                BASE.showAlert('新密码不能和旧密码一致');
                return false;
            }
            $('#passform').ajaxSubmit({
                url: '/Admin/Index/editPass',
                data: {
                    oldPass: hex_md5(_oldPass),
                    newPass: hex_md5(_newPass),
                },
                beforeSubmit: function () {
                    $('#loading').show();
                },
                success: function (rst) {
                    $('#loading').hide();
                    rst = JSON.parse(rst);
                    if (rst.status != 1) {
                        BASE.showAlert(rst.msg);
                        return false;
                    }
                    BASE.showAlert(rst.msg);
                    $('#editPassDiv').hide();
                }
            });
        });
    });
</script>
</body>
</html>