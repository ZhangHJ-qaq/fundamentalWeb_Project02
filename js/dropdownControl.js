$(function () {
    var dropdownMenu = $("#headerDropdownMenu");
    var personalCenter = $("#personalCenter");
    personalCenter.on("mouseover", function () {
        dropdownMenu.css({display: "block"})
    })
    personalCenter.on("mouseleave", function () {
        dropdownMenu.css({display: "none"})
    })
})