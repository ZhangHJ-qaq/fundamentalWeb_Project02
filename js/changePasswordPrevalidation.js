$(function () {
    let newPasswordInput1 = $("#newPassword");
    let newPasswordInput2 = $("#newPasswordConfirm");
    let errorArea = $("#errorArea");

    $("#changePasswordButton").click(function (e) {
        initialize();
        if (newPasswordInput1.val() === newPasswordInput2.val()) {
            let newPassword = newPasswordInput2.val();
            if (!new RegExp("^.{6,18}$").test(newPassword) || (new RegExp("^[0-9]{1,}$").test(newPassword))) {//如果密码不符合要求
                errorArea.append($("<div>密码必须是6-18位，且不可以是纯数字</div>"));
                newPasswordInput1.css({
                    backgroundColor: "rgba(255,0,0,0.3)"
                });
                newPasswordInput2.css({
                    backgroundColor: "rgba(255,0,0,0.3)"
                })
                e.preventDefault();
            }

        } else {
            errorArea.append($("<div>两次密码输入不一致，请确认后再试</div>"))
            newPasswordInput1.css({
                backgroundColor: "rgba(255,0,0,0.3)"
            });
            newPasswordInput2.css({
                backgroundColor: "rgba(255,0,0,0.3)"
            })

            e.preventDefault();
        }


    })

    function initialize() {
        errorArea.empty();
        $("input").css({
            background: 'white'
        })
    }

})
