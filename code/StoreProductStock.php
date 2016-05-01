<?php

/**
 * Created by PhpStorm.
 * User: User
 * Date: 29/04/2016
 * Time: 11:20 AM
 */
class StoreProductStock extends DataObject
{
    private static $db = array(
        'StockAmount' => 'Int'
    );

    private static $has_one = array(
        'Product' => 'Product',
        'ProductVariation' => 'ProductVariation',
        'StoreWarehouse' => 'StoreWarehouse'
    );

    public static function findOrCreate($warehouseID, $product){
        $idField = 'ProductID';
        if($product->ClassName == 'ProductVariation'){
            $idField = 'ProductVariationID';
        }

        $productStock = StoreProductStock::get()->filter(array(
            'StoreWarehouseID' => $warehouseID,
            $idField => $product->ID
        ))->first();

        if(empty($productStock)){
            $productStock = StoreProductStock::create();
            $productStock->StoreWarehouseID = $warehouseID;
            $productStock->$idField = $product->ID;
            $productStock->StockAmount = 0;
            $productStock->write();
        }

        return $productStock;
    }

    public function updateStock($quantity, $deductible = false){
        $stock = $this->StockAmount;
        if($deductible){
            $stock -= (int)$quantity;
        }
        else{
            $stock += (int)$quantity;
        }

        if($stock < 0){
            $stock = 0;
        }

        $this->StockAmount = $stock;
        $this->write();
    }
}