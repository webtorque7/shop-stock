<?php

/**
 * Created by PhpStorm.
 * User: User
 * Date: 24/05/2016
 * Time: 12:27 PM
 */
class OrderStockExtension extends DataExtension
{
    private static $has_one = array(
        'StoreWarehouse' => 'StoreWarehouse'
    );
}