<?php

/**
 * Created by PhpStorm.
 * User: User
 * Date: 7/05/14
 * Time: 2:54 PM
 */
class StockNotification extends DataObject
{
    private static $db = array(
        'ProductName' => 'Varchar(200)',
        'Email' => 'Varchar(50)',
        'IsVariation' => 'Boolean'
    );

    private static $has_one = array(
        'Product' => 'Product',
        'Variation' => 'ProductVariation'
    );

    private static $summary_fields = array(
        'ProductName' => 'Product Name',
        'Email' => 'Customer Email'
    );

    public function send_notifications()
    {
        $notifications = StockNotification::get();
        foreach ($notifications as $notification) {
            $product = $this->Product();
            if($notification->IsVariation){
                $product = $this->Variation();
            }

            if ($product && $product->exists()){
                $productStock =  $product->checkStock();
                if($productStock > 0){
                    $messageConfig = MessageConfig::current_message_config();
                    $email = new Generic_Email();
                    $email->setTo($notification->Email);
                    $email->setFrom($messageConfig->SiteEmailFrom);
                    $email->setSubject($messageConfig->OutOfStockEmailSubject);
                    $email->setTemplate("StockNotificationEmail");
                    $email->populateTemplate(array(
                        'Product' => $product,
                        'Content' => array(
                            'Subject' => $messageConfig->OutOfStockEmailSubject,
                            'Body' => $messageConfig->dbObject('OutOfStockEmailText')
                        )
                    ));

                    $email->send();
                    $notification->delete();
                }
            }
        }
    }
}

class StockNotificationAdmin extends ModelAdmin
{
    public static $menu_title = 'Stock Notifications';
    public static $url_segment = 'stock-notification';
    public static $managed_models = array('StockNotification');
}