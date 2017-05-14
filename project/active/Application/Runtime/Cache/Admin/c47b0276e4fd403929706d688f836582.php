<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html lang="zh-CN">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>奖品信息</title>
        <link rel="stylesheet" href="/active/Public/css/bootstrap.min.css">
        <link rel="stylesheet" href="/active/Application/Admin/View//Public/static/css/sb-admin.css">
        <link rel="stylesheet" href="/active/Application/Admin/View//Public/static/font-awesome/css/font-awesome.min.css">
        <script src="/active/Public/js/jquery.min.js"></script>
    <link rel="stylesheet" type="text/css" href="/active/Application/Admin/View//Public/static/css/index.css" />
</head>
<body>
    <div id="wrapper">
        <nav class="navbar navbar-inverse navbar-fixed-top" role="navigation">
            <div class="navbar-header">
                <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-ex1-collapse">
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                </button>
                <a class="navbar-brand" href="<?php echo U('index/index');?>">运营活动后台</a>
            </div>
            <div class="collapse navbar-collapse navbar-ex1-collapse">
                <ul class="nav navbar-nav side-nav">
                    <li class="dropdown">
                        <a href="index">16年教师节</a>
                    </li>
                    <li class="dropdown">
                        <a href="sendsms">成都GT赛车</a>
                    </li>
                    <li class="dropdown">
                        <a href="midautumn">16年中秋节</a>
                    </li>
                </ul>


                <ul class="nav navbar-nav navbar-right navbar-user">

                    <li class="dropdown user-dropdown">
                        <a href="#" class="dropdown-toggle" data-toggle="dropdown"><i class="fa fa-user"></i> 你好,<?php echo session('username');?> <b class="caret"></b></a>
                        <ul class="dropdown-menu">
                            <li><a href="#"><i class="fa fa-gear"></i> 设置</a></li>
                            <li class="divider"></li>
                            <li><a href="<?php echo U('login/logout');?>"><i class="fa fa-power-off"></i> 退出</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </nav>
<div id="page-wrapper">
    <div id="alertCnr" v-show="alert.show" class="alert" :class="alert.type" style="display:none;">
        <div class="close" @click="hideAlert">&times;</div>
        <span v-html="alert.msg"></span>
    </div>
    <div v-show="notice.show" class="alert" :class="notice.type" style="display:none;">
        <span>{{notice.msg}}</span>
        <div class="close" @click="hideNotice">&times;</div>
    </div>
    <div id="searchCnr">
        <div class="pull-right">
            <div class="btn bg-laia" @click="showPrize(-1)">添加</div>
            <div class="btn btn-danger" @click="delPrize">删除</div>
        </div>
        <div class="clearfix"></div>
    </div>
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th></th>
                    <th>ID</th>
                    <th>奖品</th>
                    <th>总数</th>
                    <th>已中奖</th>
                    <th>操作</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="(idx, obj) in tableData">
                    <td><input type="checkbox" value="{{obj.id}}" v-model="checkedArr"></td>
                    <td>{{obj.id}}</td>
                    <td>{{obj.text}}</td>
                    <td>{{obj.total}}</td>
                    <td>{{obj.count}}</td>
                    <td>
                        <div class='btn btn-link' @click="showPrize(idx)">编辑</div>
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
    <div id="editCnr" v-show="editCnrVisible" style="display:none;">
        <div id="editCnt">
            <div id="header">
                <div class="pull-right" id="editClose" @click="hideEdit">&times;</div>
            </div>
            <div>
                <table>
                    <tbody>
                        <tr>
                            <td>奖品名</td>
                            <td>
                                <input type="text" v-model="editObj.prizeName" class="form-control">
                            </td>
                        </tr>
                        <tr>
                            <td>奖品总数</td>
                            <td>
                                <input type="text" v-model="editObj.prizeTotal" class="form-control">
                            </td>
                        </tr>
                    </tbody>
                </table>
                <div id="actionCnr">
                    <div class="col-md-6">
                        <div id="saveEdit" class="pull-left btn bg-laia btn-sm" @click="savePrize">保存</div>
                    </div>
                    <div class="col-md-6">
                        <div class="pull-right btn btn-default btn-sm" @click="hideEdit">取消</div>
                    </div>
                    <div class="clearfix"></div>
                </div>
            </div>
        </div>
    </div>
</div>
</div>
<div id="footer">© 2016 版权归世纪云道所有</div>
<!-- JavaScript -->
<script src="/active/Public/js/bootstrap.min.js"></script>
<script src="/active/Application/Admin/View//Public/static/js/app.js"></script>
<?php $str = "/active/Public/js/uploadPreview.js,/active/Public/js/jquery.form.js,/active/Public/js/vue.min.js,/active/Application/Admin/View//Public/static/js/index/huoprize.js"; $arr = explode(",", $str); ?>
<?php if(is_array($arr)): foreach($arr as $key=>$src): ?><script src="<?php echo ($src); ?>"></script><?php endforeach; endif; ?>
</body>
</html>