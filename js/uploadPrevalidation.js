$(function () {
    var titleInput = $("#titleInput");
    var descInput = $("#descInput");
    var contentSelect = $("#contentSelect");
    var countrySelect = $("#countrySelect");
    var citySelect = $("#citySelect");
    var errorArea = $("#errorArea");
    $("#submit").click(function (e) {
        initialize();
        checkTiTleInput(e);
        checkDescInput(e);
        checkContentSelect(e);
        checkCountrySelect(e);
        checkCitySelect(e);
    });


    function checkTiTleInput(e) {
        if (empty(titleInput.val())) {
            e.preventDefault();
            titleInput.css({
                backgroundColor: "rgba(255,0,0,0.3)"
            });
            errorArea.append($("<p>标题不可以为空！</p>"))
        }
    }

    function checkDescInput(e) {
        if (empty(descInput.val())) {
            e.preventDefault();
            descInput.css({
                backgroundColor: "rgba(255,0,0,0.3)"
            });
            errorArea.append($("<p>描述不可以为空！</p>"))
        }
    }

    function checkContentSelect(e) {
        if (empty(contentSelect.val())) {
            e.preventDefault();
            contentSelect.css({
                backgroundColor: "rgba(255,0,0,0.3)"
            });
            errorArea.append($("<p>请选择图片的内容！</p>"))
        }
    }

    function checkCountrySelect(e) {
        if (empty(countrySelect.val())) {
            e.preventDefault();
            countrySelect.css({
                backgroundColor: "rgba(255,0,0,0.3)"
            });
            errorArea.append($("<p>请选择国家！</p>"))
        }
    }
    function checkCitySelect(e) {
        if (empty(citySelect.val())) {
            e.preventDefault();
            citySelect.css({
                backgroundColor: "rgba(255,0,0,0.3)"
            });
            errorArea.append($("<p>请选择城市！</p>"))
        }
    }



    function initialize() {
        titleInput.css({backgroundColor: "white"});
        descInput.css({backgroundColor: "white"});
        contentSelect.css({backgroundColor: "white"});
        citySelect.css({backgroundColor: "white"});
        countrySelect.css({backgroundColor: "white"});
        errorArea.empty();
    }

    function empty(s) {
        return s === null || s === undefined || s === '';
    }

});
