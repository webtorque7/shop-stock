<?php

/**
 * Created by PhpStorm.
 * User: User
 * Date: 15/05/14
 * Time: 8:41 AM
 */
class ProductStockExtension extends DataExtension
{
    private static $db = array();

    public function checkStock()
    {
        $variations = $this->owner->Variations();
        return $variations->count() ? $variations->sum('StockAmount') : $this->owner->StockAmount;
    }

    public function updateStock($quantity, $decrement = false)
    {
        $stock = $this->owner->StockAmount;
        if ($quantity) {
            if ($decrement) {
                if (($stock - $quantity) < 0) {
                    $stock = 0;
                } else {
                    $stock = $stock - $quantity;
                }
            } elseif (!$decrement) {
                $stock = $stock + $quantity;
            }

            $tableName = 'Product';
            DB::query("UPDATE \"{$tableName}\" SET \"StockAmount\" = {$stock} WHERE ID = {$this->owner->ID}");
            DB::query("UPDATE \"{$tableName}_Live\" SET \"StockAmount\" = {$stock} WHERE ID = {$this->owner->ID}");
        }
    }

    public function onAfterWrite()
    {
        parent::onAfterWrite();
        StockNotification::send_notifications();
    }
}