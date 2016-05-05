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
        'Variation' => 'ProductVariation',
        'Warehouse' => 'StoreWarehouse'
    );

    private static $summary_fields = array(
        'ProductName' => 'Product Name',
        'Email' => 'Customer Email'
    );

    public static function send_notifications()
    {
        $notifications = StockNotification::get();
        $count = 0;
        foreach ($notifications as $notification) {
            $product = $notification->Product();
            if($notification->IsVariation){
                $product = $notification->Variation();
            }

            if ($product && $product->exists() && $notification->WarehouseID > 0){
                $productStock = $product->checkStock($notification->Warehouse());
                if($productStock > 0){
                    $messageConfig = MessageConfig::current_message_config();
                    $email = new Generic_Email();
                    $email->setTo($notification->Email);
                    $email->setFrom($messageConfig->SiteEmailFrom);
                    $email->setSubject($messageConfig->OutOfStockEmailSubject);
                    $email->setTemplate("StockNotificationEmail");
                    $email->populateTemplate(array(
                        'Product' => $product,
                        'Subject' => $messageConfig->OutOfStockEmailSubject,
                        'Body' => $messageConfig->dbObject('OutOfStockEmailText')
                    ));

                    $email->send();
                    $notification->delete();
                    $count ++;
                }
            }
        }

        return $count;
    }
}