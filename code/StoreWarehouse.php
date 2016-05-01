<?php

/**
 * Created by PhpStorm.
 * User: User
 * Date: 29/04/2016
 * Time: 10:49 AM
 */
class StoreWarehouse extends DataObject
{
    private static $db = array(
        'Title' => 'Varchar(200)'
    );

    private static $has_many = array(
        'ShopStores' => 'ShopStore',
        'StoreProductStocks' => 'StoreProductStock'
    );
}