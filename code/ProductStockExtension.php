<?php

/**
 * Created by PhpStorm.
 * User: User
 * Date: 15/05/14
 * Time: 8:41 AM
 */
class ProductStockExtension extends DataExtension
{
    private static $db = array(
        'StockAmount' => 'Int'
    );

    private static $has_many = array(
        'StoreProductStocks' => 'StoreProductStock'
    );

    public function updateCMSFields(FieldList $fields)
    {
        if ($this->owner->Variations()->exists()) {
            $fields->addFieldToTab('Root.Inventory', new LabelField('VariationStock',
                'Warehouse Stocks - Because you have one or more variations, the stocks can be set in the "Variations" tab.'));
        } else {
            $warehouse = $this->owner->findWarehouse();
            if ($warehouse && $warehouse->exists()) {
                $fields->addFieldToTab('Root.Inventory',
                    StoreStockField::create('StoreProductStocks', 'Warehouse Stocks'));
            } else {
                $fields->addFieldToTab('Root.Inventory', NumericField::create('StockAmount', 'Stock Amount'));
            }
        }
    }

    public function findWarehouse()
    {
        $store = ShopStore::current();
        if ($store && $store->StoreWarehouseID) {
            return $store->StoreWarehouse();
        }
    }

    public function checkStock()
    {
        $variations = $this->owner->Variations();
        if ($variations->count()) {
            $stock = 0;
            foreach ($variations as $variation) {
                $stock += (int)$variation->checkStock();
            }

            return $stock;
        }

        $warehouse = $this->owner->findWarehouse();
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

    public function onAfterWrite()
    {
        parent::onAfterWrite();
        StockNotification::send_notifications();
    }
}