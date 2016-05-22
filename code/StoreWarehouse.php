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
        'Title' => 'Varchar(200)',
        'Country' => 'Varchar',
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
        $fields->removeByName(array('ShopStores', 'StoreProductStocks'));
        $fields->addFieldsToTab('Root.Main', array(
            TextField::create('Title', 'Warehouse Name'),
            DropdownField::create('Country', 'Which country is the warehouse in?',
                array_combine(array_keys(ShopStore::config()->country_locale_mapping),
                    array_keys(ShopStore::config()->country_locale_mapping)))
                ->setDescription('This determines where products will be shipped from and whether a member will charged domestic or international shipping')
        ));
        return $fields;
    }

    public static function current($country = null)
    {
        $store = ShopStore::current();
        if($country){
            $store = singleton('ShopStore')->StoreForCountry($country);
        }
        if ($store && $store->StoreWarehouseID) {
            return $store->StoreWarehouse();
        }
    }
}