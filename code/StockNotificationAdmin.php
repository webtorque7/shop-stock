<?php

/**
 * Created by PhpStorm.
 * User: User
 * Date: 04/05/2016
 * Time: 11:51 AM
 */
class StockNotificationAdmin extends ModelAdmin
{
    public static $menu_title = 'Stock Notifications';
    public static $url_segment = 'stock-notification';
    public static $managed_models = array('StockNotification');

    public function getEditForm($id = null, $fields = null)
    {
        $form = parent::getEditForm($id, $fields);

        $gridFieldName = $this->modelClass;
        $gridField = $form->Fields()->fieldByName($gridFieldName);

        if ($gridField && $this->modelClass === 'StockNotification') {
            $gridField->getConfig()->addComponent(new SendStockNotificationButton());
        }

        return $form;
    }
}

//class SendStockNotification_ItemRequestClass extends GridFieldDetailForm_ItemRequest
//{
//    private static $allowed_actions = array(
//        'ItemEditForm',
//        'sendnotification'
//    );
//
//    public function jsonResponse($array)
//    {
//        $response = new SS_HTTPResponse(Convert::raw2json($array));
//        $response->addHeader('Content-Type', 'application/json');
//        return $response;
//    }
//
//    public function ItemEditForm()
//    {
//        $form = parent::ItemEditForm();
//        $form->Actions()->push(
//            FormAction::create('sendnotification', 'Send Notification')
//                ->setAttribute('data-process-url', '/' . $this->Link('sendnotification'))
//                ->setUseButtonTag(true)
//        );
//
//        return $form;
//    }
//
//    public function sendnotification()
//    {
//        $form = $this->ItemEditForm();
//        $controller = Controller::curr();
//        if (!$this->record->canEdit()) {
//            return $controller->httpError(403);
//        }
//
//        $sentCount = StockNotification::send_notifications();
//        $message = $sentCount . ' notifications sent.';
//
//        if ($this->request->isAjax()) {
//            $form->sessionMessage($message, 'good');
//            return $this->jsonResponse(
//                array(
//                    'Status' => 1,
//                    'Message' => $message,
//                    'Redirect' => $this->Link('edit')
//                )
//            );
//        }
//
//        return Controller::curr()->redirect($this->Link());
//    }
//}

class SendStockNotificationButton implements GridField_HTMLProvider, GridField_ActionProvider, GridField_URLHandler {

    protected $targetFragment;

    public function __construct($targetFragment = "before") {
        $this->targetFragment = $targetFragment;
    }

    public function getHTMLFragments($gridField) {
        $button = new GridField_FormAction(
            $gridField,
            'stockNotification',
            'Send Stock Notifications',
            'stockNotification',
            null
        );

        return array(
            $this->targetFragment => '<p class="grid-stock-notification-button">' . $button->Field() . '</p>',
        );
    }

    public function getActions($gridField) {
        return array('stockNotification');
    }

    public function handleAction(GridField $gridField, $actionName, $arguments, $data) {
        if(strtolower($actionName) == 'stocknotification') {
            $message = $this->sendNotification($gridField);

            Controller::curr()->getResponse()->setStatusCode(200, $message);
            return Controller::curr()->redirectBack();
        }
    }

    public function getURLHandlers($gridField) {
        return array(
            'stockNotification' => 'sendNotification',
        );
    }

    public function sendNotification($gridField, $request = null) {
        $sentCount = StockNotification::send_notifications();
        return $sentCount . ' stock notifications sent.';
    }
}