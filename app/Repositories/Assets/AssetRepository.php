<?php

namespace App\Repositories\Assets;

use App\Asset;
use App\AssetModel;
use App\Division;
use App\Supplier;
use App\Movement;
use App\Manufacturer;
use App\Location;
use App\Status;
use App\WarrantyType;
use App\Invoice;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use App\Traits\RoleBasedAccessTrait;
use Slack;

class AssetRepository implements AssetRepositoryInterface {
  public function index()
  {
    $pageTitle = 'View Assets';
    
  // Use scopes and eager loading for better performance
  /** @var \App\User $user */
  $user = Auth::user();
    if ($user && $user->hasRole('user')) {
      // Users only see assets assigned to them or from their division
      $assets = Asset::withRelations()
                    ->where(function($query) use ($user) {
                      $query->assignedTo($user->id)
                            ->orWhere('division_id', $user->division_id);
                    })
                    ->get();
    } else {
      // Admin/Management see all assets
      $assets = Asset::withRelations()->get();
    }
    
    // Use scopes for statistics
    $totalAssets = Asset::count();
    $deployed = Asset::assigned()->count();
    $readyToDeploy = Asset::inStock()->count();
    $repairs = Asset::inRepair()->count();
    $writtenOff = Asset::disposed()->count();

    return view('assets.index', compact('assets', 'pageTitle', 'totalAssets', 'deployed', 'readyToDeploy', 'repairs', 'writtenOff'));
  }

  public function create()
  {
    $pageTitle = 'Create New Asset';
    $asset_models = AssetModel::all();
    $divisions = Division::all();
    $suppliers = Supplier::all();
    $locations = Location::all()->sortBy('location_name');
    $movements = Movement::all();
    $manufacturers = Manufacturer::all();
    $warranty_types = WarrantyType::all();
    $invoices = Invoice::all()->sortByDesc('invoiced_date');
    $storeroom = Location::where('storeroom', 1)->first();

    if(isset($storeroom))
    {
      return view('assets.create', compact('asset_models', 'divisions', 'suppliers', 'locations', 'movements', 'manufacturers', 'warranty_types', 'invoices', 'pageTitle'));
    }
    else
    {
      $pageTitle = 'Default Storeroom';
      $locations = $this->getAll('App\Location');

      Session::flash('status', 'warning');
      Session::flash('title', 'No Default Storeroom Set');
      Session::flash('message', 'You must select a Default Storeroom before creating Assets.');

      return view('admin.storeroom.index', compact('locations', 'pageTitle'));
    }
  }

  public function store($request)
  {
    $count = DB::table('assets')->count() + 1;
    $asset = new Asset();
    $asset->serial_number = $request->serial_number;
    $asset->model_id = $request->asset_model_id;
    $tag = $asset->model->asset_type->abbreviation;
    $tag = $tag . sprintf('%05d', $count);;
    $asset->asset_tag = $tag;
    $asset->division_id = $request->division_id;
    $asset->supplier_id = $request->supplier_id;
    $asset->purchase_date = $request->purchase_date;
    $asset->warranty_months = $request->warranty_months;
    $asset->warranty_type_id = $request->warranty_type_id;
    $asset->invoice_id = $request->invoice_id;
    $asset->ip = $request->ip;
    $asset->mac = $request->mac;

    $asset->save();

    $user = Auth::user()->id;

    if ($request->location != '') {
      $status = Status::where('name', '=', 'Deployed')->first();

      $movement = new Movement();
      $movement->asset_id = $asset->id;
      $movement->location_id = $request->location;
      $movement->status_id = $status->id;
      $movement->user_id = $user;
      $movement->save();

      $asset->movement_id = $movement->id;

      $asset->update();
    } else {
      $storeroom = Location::where('storeroom', '=', 1)->first();
      $status = Status::where('name', '=', 'Ready to Deploy')->first();

      $movement = new Movement();
      $movement->asset_id = $asset->id;
      $movement->location_id = $storeroom->id;
      $movement->status_id = $status->id;
      $movement->user_id = $user;
      $movement->save();

      $asset->movement_id = $movement->id;

      $asset->update();
    }

    $this->flashSuccessCreate($this->find($asset->id)->asset_tag);

    if (env('SLACK_ENABLED')) {
      $notifier = app(\App\Services\SlackNotifier::class);
      $notifier->notify('New Asset Created - ' . $this->getLatest()->asset_tag, env('SLACK_CHANNEL'), env('SLACK_BOT_NAME'));
    }

    return redirect()->route('assets.index');
  }

  public function edit($asset)
  {
    $pageTitle = 'Edit Asset - ' . $asset->asset_tag;
    $asset_models = AssetModel::all();
    $divisions = Division::all();
    $suppliers = Supplier::all();
    $movements = Movement::all();
    $manufacturers = Manufacturer::all();
    $warranty_types = WarrantyType::all();
    $invoices = Invoice::all()->sortByDesc('invoiced_date');
    return view('assets.edit', compact('asset', 'asset_models', 'divisions', 'suppliers', 'movements', 'manufacturers', 'invoices', 'warranty_types', 'pageTitle'));
  }

  public function update($request, $asset)
  {
    $asset->serial_number = $request->serial_number;
    $asset->model_id = $request->asset_model_id;
    $tag = $asset->model->asset_type->abbreviation;
    $oldTag = $asset->asset_tag;
    $oldTag = substr($oldTag,3);
    $tag = $tag . $oldTag;
    $asset->asset_tag = $tag;
    $asset->division_id = $request->division_id;
    $asset->supplier_id = $request->supplier_id;
    $asset->purchase_date = $request->purchase_date;
    $asset->warranty_months = $request->warranty_months;
    $asset->warranty_type_id = $request->warranty_type_id;
    $asset->invoice_id = $request->invoice_id;
    $asset->ip = $request->ip;
    $asset->mac = $request->mac;

    $asset->update();

    $this->flashSuccessUpdate($this->find($asset->id)->asset_tag);

    if (env('SLACK_ENABLED')) {
      $notifier = app(\App\Services\SlackNotifier::class);
      $notifier->notify('Asset Updated - ' . $this->find($asset->id)->asset_tag, env('SLACK_CHANNEL'), env('SLACK_BOT_NAME'));
    }

    return redirect()->route('assets.index');
  }

  public function getAll($class)
  {
    return $class::all();
  }

  public function getLatest()
  {
    return Asset::get()->last();
  }

  public function find($id)
  {
    return Asset::findOrFail($id);
  }

  public function count()
  {
    return DB::table('assets')->count();
  }

  public function deployedCount()
  {
    return DB::table('assets')
                     ->join('movements', function ($join) {
                       $deployed = DB::table('statuses')->where('name', 'Deployed')->pluck('id');
                       $join->on('assets.movement_id', '=', 'movements.id')
                             ->whereIn('movements.status_id', $deployed);
                     })
                     ->count();
  }

  public function readyToDeployCount()
  {
    return DB::table('assets')
                     ->join('movements', function ($join) {
                       $readyToDeploy = DB::table('statuses')->where('name', 'Ready to Deploy')->pluck('id');
                       $join->on('assets.movement_id', '=', 'movements.id')
                            ->whereIn('movements.status_id', $readyToDeploy);
                     })
                     ->count();
  }

  public function repairsCount()
  {
    return DB::table('assets')
                     ->join('movements', function ($join) {
                       $repairs1 = DB::table('statuses')->where('name', 'Out for Repairs')->pluck('id');
                       $repairs2 = DB::table('statuses')->where('name', 'Waiting for Repairs')->pluck('id');
                       $join->on('assets.movement_id', '=', 'movements.id')
                            ->whereIn('movements.status_id', [$repairs1, $repairs2]);
                     })
                     ->count();
  }

  public function writtenOffCount()
  {
    return DB::table('assets')
                     ->join('movements', function ($join) {
                       $writtenOff1 = DB::table('statuses')->where('name', 'Written Off - Broken')->pluck('id');
                       $writtenOff2 = DB::table('statuses')->where('name', 'Written Off - Age')->pluck('id');
                       $join->on('assets.movement_id', '=', 'movements.id')
                            ->whereIn('movements.status_id', [$writtenOff1, $writtenOff2]);
                     })
                     ->count();
  }

  public function flashSuccessCreate($title)
  {
    Session::flash('status', 'success');
    Session::flash('title', $title);
    Session::flash('message', 'Successfully created');
  }

  public function flashSuccessUpdate($title)
  {
    Session::flash('status', 'success');
    Session::flash('title', $title);
    Session::flash('message', 'Successfully updated');
  }

  // Slack attachments replaced by simple webhook notifier
  /**
   * Notify Slack on create (compat shim).
   *
   * @return void
   */
  public function slackCreate()
  {
    if (env('SLACK_ENABLED')) {
      try {
        $notifier = app(\App\Services\SlackNotifier::class);
        $notifier->notify('Resource created', env('SLACK_CHANNEL'), env('SLACK_BOT_NAME'));
      } catch (\Throwable $e) {
        // Swallow notifier errors during bootstrap/test runs
      }
    }
  }

  /**
   * Notify Slack on update (compat shim).
   *
   * @param mixed $id
   * @return void
   */
  public function slackUpdate($id)
  {
    if (env('SLACK_ENABLED')) {
      try {
        $notifier = app(\App\Services\SlackNotifier::class);
        $notifier->notify('Resource updated: ' . $id, env('SLACK_CHANNEL'), env('SLACK_BOT_NAME'));
      } catch (\Throwable $e) {
        // ignore
      }
    }
  }
}
