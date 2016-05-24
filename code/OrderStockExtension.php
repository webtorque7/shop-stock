<?php

/**
 * Created by PhpStorm.
 * User: User
 * Date: 24/05/2016
 * Time: 12:27 PM
 */
class OrderStockExtension extends DataObject
{
    private static $db = array();

    private static $has_one = array(
        'StoreWarehouse' => 'StoreWarehouse'
    );

    public function onBeforeWrite()
    {
        parent::onBeforeWrite();
        if(!$this->owner->StoreWarehouseID){
            $warehousse = StoreWarehouse::current();
            if($warehousse && $warehousse->exists()){
                $this->owner->StoreWarehouseID = $warehousse->ID;
            }
        }
    }
}