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
        $warehouse = StoreWarehouse::current();
        if ($warehouse && $warehouse->exists()) {
            $fields->push(StoreStockField::create('StoreProductStocks', 'Warehouse Stocks'));
//            $fields->addFieldToTab('Root.Inventory', StoreStockField::create('StoreProductStocks', 'Warehouse Stocks'));
        } else {
            $fields->push(NumericField::create('StockAmount', 'Stock Amount'));
//            $fields->addFieldToTab('Root.Inventory', NumericField::create('StockAmount', 'Stock Amount'));
        }
    }

    public function checkStock($warehouse = null)
    {
        if(!$warehouse){
            $warehouse = StoreWarehouse::current();
        }
        if ($warehouse && $warehouse->exists()) {
            return StoreProductStock::findOrCreate($warehouse->ID, $this->owner)->StockAmount;
        }

        return $this->owner->StockAmount;
    }

    public function updateStock($warehouse, $quantity, $deductible = false)
    {
        $stock = $this->owner->checkStock();

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