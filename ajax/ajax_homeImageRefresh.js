//主页页面的刷新
$(function () {
    $("#refresh").click(function () {
        $.get("ajax/ajax_homeImageRefresh.php")
            .done(function (data) {
                var imageArray = JSON.parse(data);
                var box = $("#box");
                box.empty();
                for (var i = 0; i <= imageArray.length - 1; i++) {
                    var anchor = $("<a></a>").attr({
                        href: "imageDetail.php?imageID=" + imageArray[i]['imageID']
                    });
                    var image = $("<img class='thumbnail pure-u-1-2'>").attr({
                        src: "img/small/" + imageArray[i]['path'],
                        alt: imageArray[i]['title']
                    });
                    anchor.append(image);
                    var title = $("<h1></h1>").html(imageArray[i]['title']);
                    var description = $("<div></div>").html(imageArray[i]['description']);
                    var card = $("<div class='card pure-u-1-3'></div>");
                    card.append(anchor, title, description);
                    box.append(card);

                }

            })
            .fail(function (jqXHR) {
                alert("刷新失败!错误代码:" + jqXHR.status);
            })
    })
});