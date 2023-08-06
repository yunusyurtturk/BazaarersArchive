<?php

/**
 * Created by PhpStorm.
 * User: Elektronik
 * Date: 6/5/2016
 * Time: 18:57
 */

require_once ('CViewBaseTemplateValues.php');
require_once ('CIndexPageBaseTemplateValues.php');


class SItemMessages
{
    public $iid;
    public $owner;
    public $desirer;
    public $ownerAgreed;
    public $desirerAgreed;
    public $isRead;
    public $sender;
    public $itemName;

    public $time;
    public $senderPic;
    public $itemPic;

    public $message;
}

class SItemMessagesBox
{
    public $displayMessage;
    public $messages;
}

class SItemMessageFormTexts
{
    public $inboxText;
    public $outboxText;
    public $selectMessageText;
    public $conversationDisplayUrl;
    public $headerSelectMessageText;
    public $headerText;

    public $textareaLabel;
    public $textAreaName;

    public function __construct()
    {
        $this->inboxText                = _('Inbox');
        $this->outboxText               = _('Sent');
        $this->selectMessageText        = _('Select a message to see conservation');
        $this->headerSelectMessageText  = _('Select a conversation');
        $this->headerText               = _('Conversation Archive');
        $this->conversationDisplayUrl   = '/oop/web/itemmessages.php?action=read';
    }
}

class SItemMessageFormInputUnit
{
    public $name;
    public $text;
    public $value;
}




class SItemMessageFormInputs
{
    /** @var   SItemMessageFormInputUnit */
    public $desirerAgreement;
    /** @var   SItemMessageFormInputUnit */
    public $ownerAgreement;

    /** @var   SItemMessageFormInputUnit */
    public $desirerGot;
    /** @var   SItemMessageFormInputUnit */
    public $ownerGave;

    /** @var   SItemMessageFormInputUnit */
    public $messageBox;

    /** @var   SItemMessageFormInputUnit */
    public $itemID;

    /** @var   SItemMessageFormInputUnit */
    public $conversation;

    /** @var   SItemMessageFormInputUnit */
    public $reportReason;

    /** @var   SItemMessageFormInputUnit */
    public $reportConversationID;

    /** @var   SItemMessageFormInputUnit */
    public $submitButton;



    public $sendMessageAction;
    public $reportConversationAction;



    public function __construct()
    {
        $this->desirerAgreement      = new SItemMessageFormInputUnit();
        $this->ownerAgreement        = new SItemMessageFormInputUnit();
        $this->desirerGot            = new SItemMessageFormInputUnit();
        $this->ownerGave             = new SItemMessageFormInputUnit();

        $this->messageBox            = new SItemMessageFormInputUnit();
        $this->itemID                = new SItemMessageFormInputUnit();
        $this->conversation          = new SItemMessageFormInputUnit();
        $this->reportReason          = new SItemMessageFormInputUnit();
        $this->reportConversationID  = new SItemMessageFormInputUnit();

        $this->submitButton  = new SItemMessageFormInputUnit();


        $this->desirerAgreement->name = 'desireragreed';
        $this->desirerAgreement->text = _('Deal');

        $this->ownerAgreement->name = 'owneragreed';
        $this->ownerAgreement->text = _('Deal');

        $this->desirerGot->name = '';
        $this->desirerGot->text = _('I got the item');

        $this->ownerGave->name = '';
        $this->ownerGave->text = _('I gave the item');




        $this->messageBox->name = 'message';
        $this->messageBox->text = _('Message:');

        $this->itemID->name = 'iid';
        $this->itemID->value = '0';

        $this->conversation->name = 'imsgrsid';
        $this->conversation->value = '0';

        $this->reportReason->name = 'reason';

        $this->reportConversationID->name = 'imsgrsid';

        $this->submitButton->value = '/oop/web/itemmessages.php?action=send';
        $this->submitButton->text = _('Send');


        $this->sendMessageAction        = '/oop/web/itemmessages.php?action=send';
        $this->reportConversationAction = '/oop/web/itemmessages.php?action=reportConversation';

    }
}

class SItemMessagesPageTemplateInputs
{
    /** @var  $baseInputs SViewBaseTemplateInputs */
    public  $baseInputs;


    public $action;


    /** @var   SItemMessagesBox */
    public $inbox;
    /** @var   SItemMessagesBox */
    public $outbox;

    /** @var   SItemMessageFormTexts */
    public $formTexts;

    /** @var   SItemMessageFormInputs */
    public $formInputs;

    public $itemPath;




    function __construct(SViewBaseTemplateInputs &$baseInputs)
    {
        $this->baseInputs = $baseInputs;

        $this->inbox = new SItemMessagesBox();
        $this->outbox = new SItemMessagesBox();

        $this->formInputs = new SItemMessageFormInputs();
        $this->formTexts = new SItemMessageFormTexts();


        $this->itemPath = '/item';



    }
}

class CItemMessagesTemplateValues extends CViewBaseTemplateValues
{
    private $itemMessagesParams;

    function __construct(SItemMessagesPageTemplateInputs $params)
    {
        $this->itemMessagesParams = $params;
        parent::__construct($params->baseInputs);
        parent::GetValues();
    }

    function &GetValues()
    {
        $trueDef = true;
        $zero = 0;
        if(true === $this->params->loggedIn){

            $this->templateParams->Add('logged-in', $trueDef);

            if((is_array($this->itemMessagesParams->inbox->messages) && count($this->itemMessagesParams->inbox->messages) > 0)
            ||(is_array($this->itemMessagesParams->outbox->messages) && count($this->itemMessagesParams->outbox->messages) > 0)
            ){


                $this->templateParams->Add('has-messages', $trueDef);



                $this->templateParams->Add('inbox',  $this->itemMessagesParams->inbox->messages);
                if(!is_array($this->itemMessagesParams->inbox->messages) || count($this->itemMessagesParams->inbox->messages) < 1){

                    $this->templateParams->Add('inbox-empty-message',  _('Empty input box'));
                }

                $this->templateParams->Add('outbox', $this->itemMessagesParams->outbox->messages);
                if(!is_array($this->itemMessagesParams->outbox->messages) || count($this->itemMessagesParams->outbox->messages) < 1){

                    $this->templateParams->Add('outbox-empty-message',  _('Empty sent box'));
                }


                $this->templateParams->Add('itempath', $this->itemMessagesParams->itemPath);
                $this->templateParams->Add('item-messages-inbox-name', $this->itemMessagesParams->formTexts->inboxText);
                $this->templateParams->Add('item-messages-outbox-name', $this->itemMessagesParams->formTexts->outboxText);
                $this->templateParams->Add('item-messages-select-message-message', $this->itemMessagesParams->formTexts->selectMessageText);
                $this->templateParams->Add('conversation-display-url', $this->itemMessagesParams->formTexts->conversationDisplayUrl);
                $this->templateParams->Add('itemmessages-header-select-message-text', $this->itemMessagesParams->formTexts->headerSelectMessageText);
                $this->templateParams->Add('itemmessages-header-text', $this->itemMessagesParams->formTexts->headerText);

                $this->templateParams->Add('desirer-agreement-input-name', $this->itemMessagesParams->formInputs->desirerAgreement->name);
                $this->templateParams->Add('checkbox-desirer-agreed-text', $this->itemMessagesParams->formInputs->desirerGot->text);
                $this->templateParams->Add('owner-agreement-input-name', $this->itemMessagesParams->formInputs->ownerAgreement->name);
                $this->templateParams->Add('checkbox-desirer-agreed-text', $this->itemMessagesParams->formInputs->ownerAgreement->text);

                $this->templateParams->Add('desirer-got-input-name', $this->itemMessagesParams->formInputs->desirerGot->name);
                $this->templateParams->Add('desirer-got-text', $this->itemMessagesParams->formInputs->desirerGot->text);
                $this->templateParams->Add('owner-gave-input-name', $this->itemMessagesParams->formInputs->ownerGave->name);
                $this->templateParams->Add('owner-gave-text', $this->itemMessagesParams->formInputs->ownerGave->text);

                $this->templateParams->Add('itemmessage-form-textarea-label', $this->itemMessagesParams->formInputs->messageBox->text);
                $this->templateParams->Add('itemmessage-form-textarea-name', $this->itemMessagesParams->formInputs->messageBox->name);
                $this->templateParams->Add('itemmessage-form-iid-value', $this->itemMessagesParams->formInputs->itemID->value);
                $this->templateParams->Add('itemmessage-form-iid-name', $this->itemMessagesParams->formInputs->itemID->name);
                $this->templateParams->Add('itemmessage-form-conversation-value', $this->itemMessagesParams->formInputs->conversation->value);
                $this->templateParams->Add('itemmessage-form-conversation-name', $this->itemMessagesParams->formInputs->conversation->name);

                $this->templateParams->Add('itemmessage-send-form-button-text', $this->itemMessagesParams->formInputs->submitButton->text);
                $this->templateParams->Add('itemmessage-send-form-action', $this->itemMessagesParams->formInputs->submitButton->value);

                $this->templateParams->Add('report-conversation-action', $this->itemMessagesParams->formInputs->reportConversationAction);

                $this->templateParams->Add('report-conversation-conversation-reason-input-name', $this->itemMessagesParams->formInputs->reportReason->name);
                $this->templateParams->Add('report-conversation-conversation-id-input-name', $this->itemMessagesParams->formInputs->reportConversationID->name);


                /* Additional Templates */


                $this->templateParams->AddTemplate()->ItemMessages();
                $this->templateParams->AddTemplate()->ItemMessageSendForm();
                $this->templateParams->AddTemplate()->ItemMessageAgreementForm();
            }else{

                $noMessageFoundMessage= _('You don\'t have any messages');
                $this->templateParams->Add('no-entry-message', $noMessageFoundMessage);
            }


        }else{

            $this->templateParams->Add('login-text', $this->itemMessagesParams->notLoggedInInputs->loginText);
            $this->templateParams->Add('login-addr', $this->itemMessagesParams->notLoggedInInputs->loginAddr);
            $this->templateParams->Add('not-logged-in-message', $this->itemMessagesParams->notLoggedInInputs->notLoggedInMessage);


        }



        return $this->templateParams->GetParams();

    }
}