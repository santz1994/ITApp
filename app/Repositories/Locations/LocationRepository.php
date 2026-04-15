<?php

namespace App\Repositories\Locations;

use App\Location;
use Illuminate\Support\Facades\Session;
use App\Services\SlackNotifier;

class LocationRepository implements LocationRepositoryInterface {
  protected $slack;

  public function __construct(SlackNotifier $slack)
  {
    $this->slack = $slack;
  }

  public function getAll()
  {
    return Location::all();
  }

  public function getLatest()
  {
    return Location::get()->last();
  }

  public function find($id)
  {
    return Location::findOrFail($id);
  }

  public function store($request)
  {
    Location::create($request->all());
  }

  public function update($request, $model)
  {
    $model->update($request->all());
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

  public function slackCreate()
  {
    $this->slack->notify('New Location Created - ' . $this->getLatest()->location_name);
  }

  public function slackUpdate($id)
  {
    $this->slack->notify('Location Updated - ' . $this->find($id)->location_name);
  }
}
