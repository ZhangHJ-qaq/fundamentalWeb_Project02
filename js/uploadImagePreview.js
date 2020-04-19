
$(function () {
    var imageInput = $("#imageInput");
    imageInput.change(function () {//上传照片并在本地预览的逻辑
        var imageFile = document.getElementById("imageInput").files[0];
        var fileReader = new FileReader();
        fileReader.readAsDataURL(imageFile);
        $(fileReader).on("load", function () {
            var url = fileReader.result;
            var imagePreview = $("#imagePreview");
            imagePreview.empty();
            var img = $("<img>");
            img.attr({
                src: url,
                alt: ""
            });
            img.css({
                maxWidth: "100%"
            });
            imagePreview.append(img);
        });
    });




});