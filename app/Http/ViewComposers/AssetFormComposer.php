<?php

namespace App\Http\ViewComposers;

use Illuminate\View\View;
use App\Division;
use App\Location;
use App\Status;
use App\AssetModel;
use App\Manufacturer;
use App\Supplier;
use App\AssetType;
use App\WarrantyType;
use App\Invoice;
use Illuminate\Support\Facades\Cache;

class AssetFormComposer
{
    /**
     * Bind asset-specific data to the view.
     *
     * @param  View  $view
     * @return void
     */
    public function compose(View $view)
    {
        $view->with([
            'divisions' => Cache::remember('divisions_objects', 3600, function () {
                return Division::select('id', 'name')->orderBy('name')->get();
            }),
            'locations' => Cache::remember('locations_objects', 3600, function () {
                return Location::select('id', 'location_name')->orderBy('location_name')->get();
            }),
            'statuses' => Cache::remember('asset_statuses_objects', 3600, function () {
                return Status::select('id', 'name')->orderBy('name')->get();
            }),
            'asset_models' => Cache::remember('asset_models_objects', 3600, function () {
                return AssetModel::select('id', 'asset_model', 'manufacturer_id', 'asset_type_id')->with(['manufacturer:id,name', 'asset_type:id,type_name'])->get();
            }),
            'manufacturers' => Cache::remember('manufacturers_objects', 3600, function () {
                return Manufacturer::select('id', 'name')->orderBy('name')->get();
            }),
            'suppliers' => Cache::remember('suppliers_objects', 3600, function () {
                return Supplier::select('id', 'name')->orderBy('name')->get();
            }),
            'asset_types' => Cache::remember('asset_types_objects', 3600, function () {
                return AssetType::select('id', 'type_name')->orderBy('type_name')->get();
            }),
            'warranty_types' => Cache::remember('warranty_types_objects', 7200, function () {
                return WarrantyType::select('id', 'name')->orderBy('name')->get();
            }),
            'invoices' => Cache::remember('invoices_objects', 3600, function () {
                return Invoice::select('id', 'invoice_number', 'invoiced_date', 'total', 'supplier_id')
                              ->with('supplier:id,name')
                              ->orderBy('invoiced_date', 'desc')
                              ->get();
            }),
            'purchaseOrders' => Cache::remember('purchase_orders_objects', 3600, function () {
                return \App\PurchaseOrder::select('id', 'po_number', 'order_date')
                              ->orderBy('order_date', 'desc')
                              ->get();
            }),
        ]);
    }
}