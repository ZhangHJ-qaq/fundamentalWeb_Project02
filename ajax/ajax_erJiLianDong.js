$(function () {
    var citySelect = $("#citySelect");
    var countrySelect = $("#countrySelect");
    countrySelect.change(function () {
        citySelect.empty();
        var ISO = countrySelect.val();
        var queryString = "ISO=" + ISO;
        $.get("ajax/ajax_getCityOptions.php", queryString)
            .done(function (data) {
                var cityList = JSON.parse(data);
                citySelect.append($("<option value=''>选择城市</option>"));
                for (var i = 0; i <= cityList.length - 1; i++) {
                    var option=$("<option></option>").attr({value:cityList[i]['GeoNameID']}).html(cityList[i]['AsciiName']);
                    citySelect.append(option);
                }
                citySelect.append($("<option value='-1'>其他城市</option>"))

            })

    })
});