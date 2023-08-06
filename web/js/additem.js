/**
 * Created by Elektronik on 4/25/2016.
 */

var myDropzone;
var lastPrice = 0;
var uploadUrl = "/additem.php?action=uploadImage";
$(document).ready(function() {
    //  $("div#imageUploadInput").dropzone({ url: "/oop/web/additem.php" });

    myDropzone = new Dropzone("div#myDrop", {
        url: uploadUrl,
        paramName: "uploadedfile",
        addRemoveLinks: "dictRemoveFile ",
        maxFiles: 5,
        acceptedFiles: "image/*",
        headers: {
            "Accept": "application/json",
            "Cache-Control": "",
            "X-Requested-With": ""
        },
        init: function () {
            this.on("success", function (file, responseText) {
                // Handle the responseText here. For example, add the text to the preview element:

                var responseJSON = JSON.parse(responseText);
                file.removeUrl = responseJSON.removeUrl;

                // file.previewTemplate.attr("targetUrl", responseJSON.removeUrl);
            }),

                this.on("addedfile", function (file) {
                    var removeButton = Dropzone.createElement("<button>Remove file</button>");
                    var _this = this;
                    // Listen to the click event

                    removeButton.addEventListener("click", function (e) {
                        // Make sure the button click doesn't submit the form:
                        e.preventDefault();
                        e.stopPropagation();

                        removeImageRequest(file, callbackRemoveImage);

                    });
                    file.previewElement.addEventListener("click", function() {
                        $(file.previewElement).siblings().removeClass('additem-form-selected-mainpic');
                        $(file.previewElement).addClass('additem-form-selected-mainpic');
                        $('#mainpicInput').val(file.name);
                    });

                }),
                this.on("removedfile", function (file) {
                        removeImageRequest(file, callbackRemoveImage);
                    }
                )
        }

    });


});

function editMode(itemID) {
    uploadUrl = "/edititem.php?action=uploadImage&iid="+itemID;

}
function removeImageRequest(file, callback)
{
    console.log(file);
    $.ajax({
        url: file.removeUrl,
        contentType: "application/json; charset=utf-8",
        dataType: "json",
        success: function(response) {
            console.log(response);
            if(false == response.error){
                callback(file);
            }else{
                alert(response.message);
            }
        },
        error : function(jqXHR, textStatus, errorThrown) {

            console.log('jqXHR:');
            console.log(jqXHR);
            console.log('textStatus:');
            console.log(textStatus);
            console.log('errorThrown:');
            console.log(errorThrown);

        }
    });
}

function updatePriceBox(hasPriceValue, priceText)
{
    if(true == hasPriceValue){

        $('#price').val(lastPrice + '');
        $('#price').attr('disabled', false);
        $('#price').prop("type", "number");

    }else{
        if($.isNumeric($('#price').val())){
            lastPrice = $('#price').val();
        }
        $('#price').prop("type", "text");
        $('#price').val(priceText);
        $('#price').attr('disabled', true);
    }

}
function callbackRemoveImage(file)
{
    myDropzone.removeFile(file);
}
function loadPreviouslyAddedFiles(loadUrl)
{
    $.ajax({
        url: loadUrl,
        contentType: "application/json; charset=utf-8",
        dataType: "json",
        success: function(response) {
            displayAlreadyUploadedImages(response.images);

        },
        error : function(jqXHR, textStatus, errorThrown) {

            console.log('jqXHR:');
            console.log(jqXHR);
            console.log('textStatus:');
            console.log(textStatus);
            console.log('errorThrown:');
            console.log(errorThrown);

        }
    });
}
function displayAlreadyUploadedImages(images){

    var existingFileCount = 0; // The number of files already uploaded
    var date = new Date();

    $.each(images, function(key, image){
        var mockFile = { name: image.name, size: image.size, removeUrl:image.removeUrl };
        myDropzone.emit("addedfile", mockFile);
        myDropzone.createThumbnailFromUrl(mockFile, image.url + "?a="+date.getTime(), null, null);
        myDropzone.emit("complete", mockFile);


        existingFileCount++;
    });

    myDropzone.options.maxFiles = myDropzone.options.maxFiles - existingFileCount;


}
function findCategory(catID, categories)
{
    var i;
    var selectedCat;

    if(null != categories){

        for(i=0; i < categories.length; i++){

            if(null != selectedCat && selectedCat.catid == catID){

                break;
            }
            if(categories[i].catid == catID){

                selectedCat = categories[i];
                break;
            }
            if(categories[i].hasOwnProperty('subCats')){

                selectedCat = findCategory(catID, categories[i].subCats);

            }

        }
    }
    return selectedCat;
}
function categorySelected(selectItem)
{
    var selectedCatID = selectItem.val();
    var x = document.createElement('div');
    x.innerHTML = $('#categories').html();

    var cats_json = JSON.parse(x.innerHTML);


    var cats = findCategory(selectedCatID, cats_json);

    if(null != cats && cats.hasOwnProperty('subCats') && cats.subCats.length > 0){
        /* Add a new category <select> */
        var template = $('#category-selection').html();
        template = $('<textarea />').html(template).text();

        cats.categories = cats.subCats;
        output = Mustache.render(template, cats);
        selectItem.nextAll().remove();
        $('#selectedCatID').val(selectedCatID);
        $('#categorySelectionContainer').append(output);
    }else{

        selectItem.nextAll().remove();
        $('#selectedCatID').val(selectedCatID);
        $('#categorySelectionContainer').append('<span>Kategori Seçildi</span>');
    }
}
function itemAddButtonClicked() {
    $('#addButton').prop('disabled', true);
    addItemForm.submit();

}
/*
 function categorySelected(selectedCatID, categories){

 selectedCat =  getObjects(categories, 'catid', selectedCatID);
 alert(selectedCat);
 },
 */