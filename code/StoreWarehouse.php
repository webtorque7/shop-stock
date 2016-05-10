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

    private static $summary_fields = array(
        'Title' => 'Title'
    );

    public function getCMSFields()
    {
        $fields = parent::getCMSFields();
        $fields->removeByName('ShopStores', 'StoreProductStocks');
        $fields->addFieldToTab('Root.Main', TextField::create('Title', 'Warehouse Name'));
        return $fields;
    }
}