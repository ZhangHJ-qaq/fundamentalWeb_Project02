$(function () {
    var errorArea = $("#errorArea");
    var usernameInput = $("#usernameInput");
    var emailInput = $("#emailInput");
    var password1Input = $("#password1Input");
    var password2Input = $("#password2Input");
    $("#submit").click(function (e) {
        initialize();
        checkUsername(e);
        checkEmail(e);
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

    function checkEmail(e) {
        if (!new RegExp("^[a-z0-9]+([._\\\\-]*[a-z0-9])*@([a-z0-9]+[-a-z0-9]*[a-z0-9]+.){1,63}[a-z0-9]+$").test(emailInput.val())) {
            e.preventDefault();
            errorArea.css({
                visibility: "visible"
            });
            errorArea.append($("<div>邮箱格式不正确</div>"));
            emailInput.css({
                background: "rgba(255,0,0,0.3)"
            })
        }
    }

    function checkPassword(e) {
        if (password1Input.val() === password2Input.val()) {
            var password = password1Input.val();
            if (!new RegExp("^.{6,18}$").test(password) || (new RegExp("^[0-9]{1,}$").test(password))) {
                e.preventDefault();
                errorArea.css({
                    visibility: "visible"
                });
                errorArea.append($("<div>密码必须是6-18位，且不可以是纯数字</div>"));
                password1Input.css({
                    background: "rgba(255,0,0,0.3)"
                });
                password2Input.css({
                    background: "rgba(255,0,0,0.3)"
                })

            }

        } else {
            e.preventDefault();
            errorArea.css({
                visibility: "visible"
            });
            errorArea.append($("<div>两次密码输入不一致</div>"));
            password1Input.css({
                background: "rgba(255,0,0,0.3)"
            });
            password2Input.css({
                background: "rgba(255,0,0,0.3)"
            })
        }
    }
});