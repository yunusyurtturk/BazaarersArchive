/**
 * Created by Elektronik on 3/27/2016.
 */


$(document).ready(function() {
    $('.masonry-container').imagesLoaded( function() {


        $('.masonry-container').masonry({
            itemSelector: '.ms-item', // use a separate class for itemSelector, other than .col-
            columnWidth: '.ms-item',
            containerStyle: { padding: '0px' }
        });
    });


    $('#reportConversationModal').on('show.bs.modal', function (event) {
        var button = $(event.relatedTarget) ;
        var action = button.data('action') ;
        var modal = $(this);
        modal.find('#reportConversationFormAction').val(action);
    });



});

function applyMasonry(container){

    container.masonry({
        itemSelector: '.ms-item', // use a separate class for itemSelector, other than .col-
        columnWidth: '.ms-item',
        containerStyle: { padding: '0px' }
    });
}

function isBreakpoint( alias ) {
    return $('.device-' + alias).is(':visible');
}

function filterItems(catClassID) {


    //$('#main-list-container .cat'+catClassID).hide().removeClass('ms-item');
    $('#main-list-container .ms-item').not('.cat'+catClassID).hide().removeClass('ms-item');
   // $('#main-list-container  :not(.cat'+catClassID+')').hide().removeClass('ms-item');
    applyMasonry($('#main-list-container'));
        //$('#main-list-container').masonry('reload');



}
function passwordUpdatedCallback(container, response)
{
    var responseJSON = JSON.parse(response);
    container.html(responseJSON.message);

}
function agreementCallback(container, response)
{
    $('#item-message-sending-indicator').css('display', 'none');


}

function updatePassword(to, form)
{
    ajaxPostFormTo(to, form, $('#passwordUpdateFormInputs'),  passwordUpdatedCallback);
}

function toggleAgreement(to, input)
{
    var data = {'checked' : input.is(':checked')};

    $('#item-message-send-result').hide();
    $('#item-message-sending-indicator').css('display', 'inline');
    $('#item-message-sending-text').hide();

    ajaxPostTo(to, data, null, agreementCallback);
}
function disableAndSubmitButton(button)
{
    $(button).prop('disabled', true);
    $(button).closest('form').submit();
   // form.submit();

}
function clearUnreadNewsBadge(container, response, data)
{
    $('#unreadNewsCountBadge').html('');
}
function readNews(to)
{
    if(to){
        ajaxPostTo(to, null, null, clearUnreadNewsBadge);
    }
}
function ajaxPostTo(to, data, container, callback)
{
    $.ajax({
        url: to,
        type: 'POST',
        data: data,
        dataType: "json",
        success: function(response) {

            if(null != callback) {

                callback(container, response, data);
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
function ajaxPostFormTo(to, form, container, callback)
{
    ajaxPostTo(to, form.serialize(), container, callback);


}

function requestLocation()
{
    if(navigator.geolocation){

        navigator.geolocation.getCurrentPosition(positionCallback, positionErrorCallback,  {maximumAge:0});
    }else{

    }
}

function listItems()
{
    $('#listUsersCmd').show();
    $('#listItemsCmd').hide();

    ajaxRequest('index.php?action=items', $('#main-list-container'), callbackListItems);
}
function listUsers()
{

    ajaxRequest('index.php?action=users&lat=39&lng=32', $('#main-list-container'), callbackUserFollowers);

}

function removeItem(url)
{
    ajaxPostTo(url, null, null, removeItemCallback);

}
function removeItemCallback(container, response, data)
{
    $('#removeItemModalResult').html(response.message);

    if(false == response.error){
        $('#removeItemModal').modal();

        
    }else{

    }
}

function positionErrorCallback(error)
{
    var position = {coords:{latitude: 39.8, longitude:32.75}};
    //positionCallback(position);
}

function positionCallback(position)
{
    var container = $('#main-list-container');
    ajaxRequest('index.php?action=userLocation&lat='+position.coords.latitude + '&lng=' + position.coords.longitude , container, callbackListItems);

}

function renderHeaderMessage(response)
{
    /* Header Message'i ekle */
    var template = $('#list-header-message-template').html();


    template = $('<div/>').html(template).text();
    var output = '';

    if(response.hasOwnProperty('header-message')){

        output = Mustache.render(template, response);
        $('#mainListHeader').html(output);
    }
}
function callbackItemList(container, response, colCount)
{
    renderHeaderMessage(response);

    /* Urunleri ekle */
    var template = $('#item-in-list-template').html();


    template = $('<div/>').html(template).text();
    var output = '';

    if(response.hasOwnProperty('items') && response.items.length > 0) {

        $.each(response.items, function(key, item){

            output = Mustache.render(template, item);
            container.append(output);

            applyMasonry(container);
        });


    }else{

        var noUserMessage = '<p class="user-has-no-item-message">Kullanıcının hiçbir ürünü bulunmamakta</p>';

        container.append(noUserMessage);
    }
}

function callbackListItems(container, response)
{
    callbackItemList(container, response, 3);


    applyMasonry(container);
    container.masonry('reloadItems');
    container.masonry('layout');
}
function callbackUserItems(container, response)
{
    callbackItemList(container, response, 3);

}
function callbackSendItemMessage(container, response, data)
{
    if(false == response.error){
       
        var itemmessage = $('#item-message-template').html();
        var template = $('<div/>').html(itemmessage).text();

        var message = {itemMessageWay : 1, message: $('#new-item-message').val()};

        output = Mustache.render(template, message);
        container.prepend(output);
        $('#new-item-message').val('');


        $('#item-message-send-result').html(response.message);
        $('#item-message-send-result').show();

        $('#item-message-sending-text').css('display', 'none');
        $('#item-message-sending-indicator').css('display', 'none');

        fadeOutMessage( $('#item-message-send-result'));

    }else{
        alert('Mesaj gönderilemedi');
    }
    $('.btn-send-message').prop('disabled', false);

}
function fadeOutMessage(container)
{
    setTimeout(function() {
        container.fadeOut('slow');
    }, 4000 );


}
function sendItemMessage(sender, to)
{
    if(!$('#new-item-message').val().trim() ){

        alert('Type something and send again');
    }else{

        $('.btn-send-message').prop('disabled', true);

        $('#item-message-send-result').hide();


        $('#item-message-sending-text').css('display', 'inline');
        $('#item-message-sending-indicator').css('display', 'inline');


        ajaxPostFormTo(to, $('#sendMessageForm'), $('#conversationContainer'), callbackSendItemMessage);
    }


}

function showAllLastItemmessages()
{

        $('#itemMessagesInboxContainer').children().fadeIn('slow');
        $('#itemMessagesOutboxContainer').children().fadeIn('slow');


}
function  selectLastItemMessage(messageContainer)
{
    messageContainer.siblings().removeClass("selected-last-message");
    messageContainer.addClass("selected-last-message").removeClass("not-read-item-message");

    if(isBreakpoint('xs')) {

        messageContainer.siblings().fadeOut('slow');
    }
}
function callbackDisplayConversation(container, response, data)
{
    var output = '';
    container.html('');

    loadingEventsQuickShowItemMessages();


    if(data.hasOwnProperty('imsgrsid') &&  data.hasOwnProperty('iid')){

        $('#sendMessageFormItemID').val(data.iid);
        $('#sendMessageFormConversationID').val(data.imsgrsid);
        $('#reportConversationID').val(data.imsgrsid);

        if(response.hasOwnProperty('previouslyReported')){

            $('#previouslyReportedIndicator').show();
            $('#previouslyReportedIndicator').html(response.previouslyReportedText);
        }else{
            $('#previouslyReportedIndicator').hide();
        }

    }else{

        $('#sendMessageFormItemID').val(0);
        $('#sendMessageFormConversationID').val(0);
    }
    if(response.messages.length > 0) {
        $('#specificItemMessagesContainer').show();
        var itemmessage = $('#item-message-template').html();
        var template = $('<div/>').html(itemmessage).text();

        $.each(response.messages, function (key, message) {

            /* If not sender */
            if(message.itemMessageWay != "1"){
                delete message.itemMessageWay;
            }
            output = Mustache.render(template, message);
            container.append(output);
        });


    }

    if(response.agreementStatus){
        var itemmessage = $('#item-message-agreement-boxes-template').html();
        var template = $('<div/>').html(itemmessage).text();

        if(response.agreementStatus["0"] == true){

            response["owner-agreed"] = true;
        }
        if(response.agreementStatus["1"] == true){

            response["desirer-agreed"] = true;
        }
        if(response.agreementStatus["2"] == true){

            response["owner-gave"] = true;
        }
        if(response.agreementStatus["3"] == true){

            response["desirer-got"] = true;
        }


        if(response.checkboxStatus[0] == false){
            response["owner-agreed-disabled"] = true;
        }
        if(response.checkboxStatus[1] == false){

            response["desirer-agreed-disabled"] = true;
        }
        if(response.checkboxStatus[2] == false){

            response["owner-gave-disabled"] = true;
        }
        if(response.checkboxStatus[3] == false){

            response["desirer-got-disabled"] = true;
        }



        output = Mustache.render(template, response);
        $('#agreementBoxesContainer').html(output);

    }

}
function loadingEventsQuickShowItemMessages()
{
    $('#conversationContainer').toggle();
    $('#specificItemMessagesContainer').toggle();
    $('#itemMessagesLoadingIndicator').toggle();

}
function quickShowItemMessages(url, iid, conversationID, container)
{

    var str =  {imsgrsid: conversationID, iid: iid};

    loadingEventsQuickShowItemMessages();
    ajaxPostTo(url, str, container,  callbackDisplayConversation);

}
function callbackGenericUserFollows(container, response, no_result_message)
{
    renderHeaderMessage(response);

    if(response.users.length > 0) {

        var user_in_list = $('#user-in-list-template').html();
        var template = $('<div/>').html(user_in_list).text();

        var output = '';

        $.each(response.users, function (key, user) {

            if(user.is_following == false){
                delete user.is_following;
            }
            output = Mustache.render(template, user);
            container.append(output);
        });

        applyMasonry(container);
    }else{

        var noUserMessage = '<p class="user-has-no-item-message">'+no_result_message+'</p>';

        container.append(noUserMessage);
    }



}

function callbackUserFollowings(container, response)
{
    callbackGenericUserFollows(container, response, "Takip edilen kimse bulunmamakta")
}
function callbackUserFollowers(container, response)
{
    $('#listUsersCmd').hide();
    $('#listItemsCmd').show();
    callbackGenericUserFollows(container, response, "Takip ettiği kimse bulunmamakta")
}

function callbackQuickFollow(container, response)
{
    if(response.followed == true){

        container.addClass('user-remove-button').children('span:first').removeClass('glyphicon-plus').addClass('glyphicon-minus')
    }else{

        container.removeClass('user-remove-button').children('span:first').removeClass('glyphicon-minus').addClass('glyphicon-plus')
    }
    container.find('.glyphicon').toggle();
    container.find('.spinner').toggle();
}
function callbackFollow(container, response)
{
    container.find('.spinner').toggle();
    if(response.followed == true){

        container.addClass('btn-default').removeClass('btn-success');
    }else{

        container.addClass('btn-success').removeClass('btn-default');
    }
    
    container.find('#buttonText').html(response.message);


}
function getUserItems(userid, container)
{
    ajaxRequest('user.php?userid='+userid+'&action=getItems', container, callbackUserItems);
}
function getUserFollowers(userid, container)
{
    ajaxRequest('user.php?userid='+userid+'&action=getFollowers', container, callbackUserFollowers);
}
function getUserFollowings(userid, container)
{
    ajaxRequest('user.php?userid='+userid+'&action=getFollowings', container, callbackUserFollowings);
}
function quickFollowUser(url, container)
{

    container.find('.spinner').toggle();
    container.find('.glyphicon').toggle();
    //container.addClass('user-remove-button').children('span:first').removeClass('glyphicon-plus').addClass('glyphicon-minus')
    ajaxPostTo(url,{}, container, callbackQuickFollow);

}
function followUser(url, container)
{
    container.find('.spinner').toggle();
    ajaxPostFormTo(url, $('#quickFollowForm'), container, callbackFollow);
}

function ajaxRequest(url, container, callback){

    $.ajax({
        url: '/oop/web/' + url,
        contentType: "application/json; charset=utf-8",
        dataType: "json",
        success: function(response) {

            container.html('');
            callback(container, response);

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


function showUserInfoUpdateForm()
{
    $('#userInfoUpdateFormContainer').slideToggle(400);

}

function reportConversationCallback(container, response, data)
{
    if(false == response.error){

        alert('Raporlama basarili');
    }else{

        alert('Bir hata olustu');
    }
}
function reportConversation(to)
{
    ajaxPostFormTo(to, $('#reportConversationForm'), null, reportConversationCallback)

}
