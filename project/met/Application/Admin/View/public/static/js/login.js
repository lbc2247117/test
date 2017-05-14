var LOGIN_API = 'userLogin';
function userLogin() {
    var _user = $('#username').val();
    var _pass = $('#password').val();
    if (!_user.trim() || !_pass.trim())
    {
        BASE.showAlert('请输入用户名和密码');
        return false;
    }
    var _dt = {
        username: _user,
        password: hex_md5(_pass),
    }
    $.post(LOGIN_API, _dt, function (rst) {
        rst = JSON.parse(rst);
        if (rst.status != 1) {
            BASE.showAlert(rst.msg);
            return false;
        }
        window.location.href = '../Index/index';
    });
}
$(function () {
    $('#subBtn').click(function () {
        userLogin();
    });
    $('#password').keypress(function (e) {
        if (e.keyCode == 13)
            userLogin();
    });

});