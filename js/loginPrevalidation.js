//验证的在前端检查
$(function () {
    var usernameInput = $("#usernameInput");
    var passwordInput = $("#passwordInput");
    var errorArea = $("#errorArea");
    $("#submit").click(function (e) {
        initialize();
        checkUsername(e);
        checkPassword(e);
    });

    function initialize() {
        errorArea.empty();
        $("input").css({
            background: 'white'
        })
    }
    function checkUsername(e) {
        if (!new RegExp("^[0-9a-zA-Z]{6,18}$").test(usernameInput.val())) {
            e.preventDefault();
            errorArea.css({
                visibility: "visible"
            });
            errorArea.append($("<div>用户名必须为6-18位，且只能由字母，数字组成</div>"));
            usernameInput.css({
                background: "rgba(255,0,0,0.3)"
            })
        }
    }
    function checkPassword(e) {
        var password=passwordInput.val();
        if (!new RegExp("^.{6,18}$").test(password) || (new RegExp("^[0-9]{1,}$").test(password))) {
            e.preventDefault();
            errorArea.css({
                visibility: "visible"
            });
            errorArea.append($("<div>密码必须是6-18位，且不可以是纯数字</div>"));
            passwordInput.css({
                background: "rgba(255,0,0,0.3)"
            });
        }

    }

});