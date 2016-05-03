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

        if ($this->modelClass === 'StockNotification') {
            if ($gridField) {
                $gridField->getConfig()->getComponentByType('GridFieldDetailForm')->setItemRequestClass(
                    'SendStockNotification_ItemRequestClass'
                );
            }
        }

        return $form;
    }
}

class SendStockNotification_ItemRequestClass extends GridFieldDetailForm_ItemRequest
{
    private static $allowed_actions = array(
        'ItemEditForm',
        'sendnotification'
    );

    public function jsonResponse($array)
    {
        $response = new SS_HTTPResponse(Convert::raw2json($array));
        $response->addHeader('Content-Type', 'application/json');
        return $response;
    }

    public function ItemEditForm()
    {
        $form = parent::ItemEditForm();
        $form->Actions()->push(
            FormAction::create('sendnotification', 'Send Notification')
                ->setAttribute('data-process-url', '/' . $this->Link('sendnotification'))
                ->setUseButtonTag(true)
        );

        return $form;
    }

    public function sendnotification()
    {
        $form = $this->ItemEditForm();
        $controller = Controller::curr();
        if (!$this->record->canEdit()) {
            return $controller->httpError(403);
        }

        $sentCount = StockNotification::send_notifications();
        $message = $sentCount . ' notifications sent.';

        if ($this->request->isAjax()) {
            $form->sessionMessage($message, 'good');
            return $this->jsonResponse(
                array(
                    'Status' => 1,
                    'Message' => $message,
                    'Redirect' => $this->Link('edit')
                )
            );
        }

        return Controller::curr()->redirect($this->Link());
    }
}