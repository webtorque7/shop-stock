<?php

/**
 * Created by PhpStorm.
 * User: User
 * Date: 15/05/14
 * Time: 8:41 AM
 */
class ProductVariationStockExtension extends DataExtension
{
    private static $db = array(
        'StockAmount' => 'Int',
        'ShippingUnit' => 'Varchar'
    );

    private static $has_many = array(
        'StoreProductStocks' => 'StoreProductStock'
    );

    public function updateCMSFields(FieldList $fields)
    {
        $warehouse = $this->owner->findWarehouse();
        if ($warehouse && $warehouse->exists()) {
            $fields->addFieldToTab('Root.Inventory', StoreStockField::create('StoreProductStocks', 'Warehouse Stocks'));
        } else {
            $fields->addFieldToTab('Root.Inventory', NumericField::create('StockAmount', 'Stock Amount'));
        }
    }

    public function findWarehouse()
    {
        $store = ShopStore::current();
        if ($store && $store->StoreWarehouseID) {
            return $store->StoreWarehouse();
        }
    }

    public function checkStock($warehouse = null)
    {
        if(!$warehouse){
            $warehouse = $this->owner->findWarehouse();
        }
        if ($warehouse && $warehouse->exists()) {
            return StoreProductStock::findOrCreate($warehouse->ID, $this->owner)->StockAmount;
        }

        return $this->owner->StockAmount;
    }

    public function updateStock($quantity, $deductible = false)
    {
        $stock = $this->owner->checkStock();
        $warehouse = $this->owner->findWarehouse();
        if ($warehouse && $warehouse->exists()) {
            $stock = StoreProductStock::findOrCreate($warehouse->ID, $this->owner);
            $stock->updateStock($quantity, $deductible);
        }
        else{
            if($deductible){
                $stock -= (int)$quantity;
            }
            else{
                $stock += (int)$quantity;
            }

            if($stock < 0){
                $stock = 0;
            }

            $this->owner->StockAmount = $stock;
            $this->owner->write();
        }
    }

    public function onBeforeWrite()
    {
        parent::onAfterWrite();
        $product = $this->owner->Product();
        $this->owner->ShippingUnit = $product->ShippingUnit;
    }
}