<?php

/**
 * Created by PhpStorm.
 * User: User
 * Date: 24/05/2016
 * Time: 12:27 PM
 */
class OrderStockExtension extends DataExtension
{
    private static $db = array(
        'Locale' => 'Varchar'
    );

    private static $has_one = array(
        'StoreWarehouse' => 'StoreWarehouse'
    );

    public function onBeforeWrite()
    {
        parent::onBeforeWrite();
        if(!$this->owner->StoreWarehouseID){
            $storeID = filter_var(ShoppingCart::$cartid_session_name, FILTER_SANITIZE_NUMBER_INT);
            if($storeID){
                $warehouse = ShopStore::get()->byID($storeID)->StoreWarehouse();
                if($warehouse && $warehouse->exists()){
                    $this->owner->StoreWarehouseID = $warehouse->ID;
                }
            }
        }

        if(!$this->owner->Locale){
            $locale = substr(ShoppingCart::$cartid_session_name, strpos(ShoppingCart::$cartid_session_name, '-') + 1);
            $this->owner->Locale = $locale;
        }
    }
}