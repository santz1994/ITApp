<?php

namespace App\Http\Controllers;

use App\Supplier;
use Illuminate\Support\Facades\Session;
use App\Http\Requests\Suppliers\StoreSupplierRequest;
use App\Http\Requests\Suppliers\UpdateSupplierRequest;

class SuppliersController extends Controller
{
  public function __construct()
  {
      $this->middleware('auth');
  }

  public function index()
  {
    $pageTitle = 'Suppliers';
    $suppliers = \App\Services\CacheService::getSuppliers();
    return view('suppliers.index', compact('suppliers', 'pageTitle'));
  }

  public function store(StoreSupplierRequest $request)
  {
    $supplier = Supplier::create($request->validated());

    Session::flash('status', 'success');
    Session::flash('title', $supplier->name);
    Session::flash('message', 'Successfully created');

    return redirect()->route('suppliers.index');
  }

  public function show(Supplier $supplier)
  {
    $pageTitle = 'Supplier Details - ' . $supplier->name;
    
    // Get all assets from this supplier
    $assets = $supplier->assets()->with(['assetModel', 'status', 'assignedUser', 'location'])->get();
    
    // Get all invoices from this supplier
    $invoices = $supplier->invoices()->orderBy('created_at', 'desc')->get();
    
    return view('suppliers.show', compact('supplier', 'assets', 'invoices', 'pageTitle'));
  }

  public function edit(Supplier $supplier)
  {
    $pageTitle = 'Edit Supplier - ' . $supplier->name;
    return view('suppliers.edit', compact('supplier', 'pageTitle'));
  }

  public function update(UpdateSupplierRequest $request, Supplier $supplier)
  {
    $supplier->update($request->validated());

    Session::flash('status', 'success');
    Session::flash('title', $supplier->name);
    Session::flash('message', 'Successfully updated');

    return redirect()->route('suppliers.index');
  }

  public function destroy(Supplier $supplier)
  {
    try {
      // Check if supplier has related records
      if ($supplier->assets()->count() > 0) {
        Session::flash('status', 'error');
        Session::flash('title', 'Cannot delete');
        Session::flash('message', 'This supplier is assigned to ' . $supplier->assets()->count() . ' asset(s). Please reassign or remove them first.');
        return redirect()->route('suppliers.index');
      }

      if ($supplier->invoices()->count() > 0) {
        Session::flash('status', 'error');
        Session::flash('title', 'Cannot delete');
        Session::flash('message', 'This supplier has ' . $supplier->invoices()->count() . ' invoice(s). Please remove them first.');
        return redirect()->route('suppliers.index');
      }

      $name = $supplier->name;
      $supplier->delete();

      Session::flash('status', 'success');
      Session::flash('title', $name);
      Session::flash('message', 'Successfully deleted');
    } catch (\Exception $e) {
      Session::flash('status', 'error');
      Session::flash('title', 'Error');
      Session::flash('message', 'Failed to delete supplier: ' . $e->getMessage());
    }

    return redirect()->route('suppliers.index');
  }
}
