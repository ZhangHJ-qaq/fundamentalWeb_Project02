//二级联动
$(function () {
    var citySelect = $("#citySelect");
    var countrySelect = $("#countrySelect");
    countrySelect.change(function () {
        citySelect.empty();
        var ISO = countrySelect.val();
        var queryString = "ISO=" + ISO;
        $.get("ajax/ajax_getCityOptions.php", queryString)//通过ajax从服务器上获得城市列表
            .done(function (data) {
                var cityList = JSON.parse(data);
                citySelect.append($("<option value=''>选择城市</option>"));
                var innerHTML = "";
                for (var i = 0; i <= cityList.length - 1; i++) {
                    innerHTML += `<option value=${cityList[i]['GeoNameID']}>${cityList[i]['AsciiName']}</option>`;
                }
                citySelect.append(innerHTML);
                citySelect.append($("<option value='-1'>其他城市</option>"))

            })

    })
});