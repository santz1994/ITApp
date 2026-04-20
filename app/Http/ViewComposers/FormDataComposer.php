<?php

namespace App\Http\ViewComposers;

use Illuminate\View\View;
use App\Division;
use App\Location;
use App\Status;
use App\User;
use App\Role;
use Illuminate\Support\Facades\Cache;

class FormDataComposer
{
    /**
     * Bind data to the view.
     *
     * @param  View  $view
     * @return void
     */
    public function compose(View $view)
    {
        // Only bind basic user management form data
        // Use caching to improve performance
        $view->with([
            // Return model collections so views can access ->id and ->name properties
            'divisions' => Cache::remember('divisions_dropdown', 3600, function () {
                return Division::select('id', 'name')->orderBy('name')->get();
            }),
            'locations' => Cache::remember('locations_dropdown', 3600, function () {
                return Location::select('id', 'location_name as name')->orderBy('location_name')->get();
            }),
            'statuses' => Cache::remember('statuses_dropdown', 3600, function () {
                return Status::select('id', 'name')->orderBy('name')->get();
            }),
            'users' => Cache::remember('users_dropdown', 1800, function () {
                return User::select('id', 'name')->orderBy('name')->get();
            }),
            'roles' => Cache::remember('roles_dropdown', 7200, function () {
                return Role::query()
                    ->assignable()
                    ->select('id', 'display_name')
                    ->orderBy('display_name')
                    ->get();
            }),
        ]);
    }
}