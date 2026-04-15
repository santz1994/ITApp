<?php

namespace App\Http\Controllers;

use App\Invoice;
use App\Supplier;
use App\Division;
use Illuminate\Support\Facades\Session;
use App\Services\SlackNotifier;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use App\Http\Requests\Invoices\StoreInvoiceRequest;
use App\Http\Requests\Invoices\UpdateInvoiceRequest;

class InvoicesController extends Controller
{
  protected $slack;

  public function __construct(SlackNotifier $slack)
  {
    $this->middleware('auth');
    $this->slack = $slack;
  }

  public function index()
  {
    $pageTitle = 'Invoices';
    $invoices = Invoice::orderBy('invoiced_date', 'desc')->paginate(50);
    $suppliers = \App\Services\CacheService::getSuppliers();
    $divisions = \App\Services\CacheService::getDivisions();
    return view('invoices.index', compact('invoices', 'suppliers', 'divisions', 'pageTitle'));
  }

  /**
   * Display the invoice details page
   */
  public function show(Invoice $invoice)
  {
    $pageTitle = 'Invoice Details - ' . $invoice->invoice_number;
    return view('invoices.show', compact('invoice', 'pageTitle'));
  }

  /**
   * Download the invoice PDF file
   */
  public function downloadPdf(Invoice $invoice)
  {
    $filepath = storage_path() . "/app/invoices/" . $invoice->invoice_number . ".pdf";
    
    if (!Storage::exists('invoices/' . $invoice->invoice_number . '.pdf')) {
      return redirect('/invoices')->with('error', 'PDF file not found.');
    }
    
    return response()->file($filepath);
  }

  public function store(StoreInvoiceRequest $request)
  {
    $invoice = new Invoice();
    $invoice->invoice_number = $request->invoice_number;
    $invoice->order_number = $request->order_number;
    $invoice->division_id = $request->division_id;
    $invoice->supplier_id = $request->supplier_id;
    $invoice->invoiced_date = $request->invoiced_date;
    $invoice->total = $request->total;
    $filename = "invoices/" . $invoice->invoice_number . '.pdf';
    $file = $request->file('file');
    if ($file) {
      Storage::disk('local')->put($filename, File::get($file));
    }

    $invoice->save();

    Session::flash('status', 'success');
    Session::flash('title', 'Invoice ' . $invoice->invoice_number);
    Session::flash('message', 'Successfully created');

    if (getenv('SLACK_ENABLED')) {
      $supplierName = optional($invoice->supplier)->name ?? 'N/A';
      $divisionName = optional($invoice->division)->name ?? 'N/A';
      $message = "New Invoice Created: #{$invoice->invoice_number} - Order: {$invoice->order_number} - Total: {$invoice->total} - Supplier: {$supplierName} - Division: {$divisionName}";
      $this->slack->notify($message);
    }

    return redirect()->route('invoices.index');
  }

  public function edit(Invoice $invoice)
  {
    $pageTitle = 'Edit Invoice - ' . $invoice->invoice_number;
    $suppliers = \App\Services\CacheService::getSuppliers();
    $divisions = \App\Services\CacheService::getDivisions();
    return view('invoices.edit', compact('invoice', 'suppliers', 'divisions', 'pageTitle'));
  }

  public function update(UpdateInvoiceRequest $request, Invoice $invoice)
  {
    if ($request->invoice_number != $invoice->invoice_number) {
      Storage::move('invoices/' . $invoice->invoice_number . '.pdf', 'invoices/' . $request->invoice_number  . '.pdf');
    }

    $filename = "invoices/" . $request->invoice_number . '.pdf';
    $file = $request->file('file');
    if ($file) {
      Storage::disk('local')->put($filename, File::get($file));
    }

    $invoice->update($request->validated());

    Session::flash('status', 'success');
    Session::flash('title', 'Invoice ' . $invoice->invoice_number);
    Session::flash('message', 'Successfully updated');

    return redirect()->route('invoices.index');
  }
}
