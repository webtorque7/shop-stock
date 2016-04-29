<?php
/**
 * Created by PhpStorm.
 * User: User
 * Date: 15/05/14
 * Time: 8:41 AM
 */
class ProductVariationStockExtension extends DataExtension{
	private static $db = array(
		'ShippingUnit' => 'Varchar'
	);

	public function onBeforeWrite(){
		parent::onAfterWrite();
		$product = $this->owner->Product();
		$this->owner->ShippingUnit = $product->ShippingUnit;
	}

	public function updateStock($quantity, $decrement = false){
		$stock = $this->owner->StockAmount;
		if($quantity){
			if($decrement){
				if(($stock - $quantity) < 0){
					$stock = 0;
				}
				else{
					$stock = $stock - $quantity;
				}
			}
			else if(!$decrement){
				$stock = $stock + $quantity;
			}

			$tableName = 'ProductVariation';
			DB::query("UPDATE \"{$tableName}\" SET \"StockAmount\" = {$stock} WHERE ID = {$this->owner->ID}");
			DB::query("UPDATE \"{$tableName}_Live\" SET \"StockAmount\" = {$stock} WHERE ID = {$this->owner->ID}");
		}
	}

	public function onAfterWrite() {
		parent::onAfterWrite();
		StockNotification::send_notifications();
	}
}