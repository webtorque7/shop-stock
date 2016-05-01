<div class="StoreStockField">
    <% loop $Warehouses %>
        <div class="store-warehouse">
            <label for="{$Top.Name}_{$WarehouseID}" class="warehouse-title">$WarehouseTitle</label>
            <input id="{$Top.Name}_{$WarehouseID}" type="text" name="$Top.Name[$WarehouseID]" value="$StockAmount">
        </div>
    <% end_loop %>
</div>