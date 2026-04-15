<?php

namespace Tests;

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

use App\User;
use App\AssetModel;

class AssetModelTest extends TestCase
{
     use DatabaseTransactions;

     protected function setUp(): void
     {
          parent::setUp();
          try { if (class_exists(\Database\Seeders\AssetModelTestDependenciesSeeder::class)) { (new \Database\Seeders\AssetModelTestDependenciesSeeder())->run(); } } catch (\Throwable $__e) {}
          try { if (class_exists(\Database\Seeders\RolesTableSeeder::class)) { (new \Database\Seeders\RolesTableSeeder())->run(); } } catch (\Throwable $__e) {}
          try { if (class_exists(\Database\Seeders\TestUsersTableSeeder::class)) { (new \Database\Seeders\TestUsersTableSeeder())->run(); } } catch (\Throwable $__e) {}
     }

    public function testUserCannotAccessAssetModelsView()
    {
      $user = User::where('name', 'User User')->get()->first();

      $this->actingAs($user)
           ->visit('/models')
           ->see('Oops! Insufficient Permissions.');
    }

    public function testAssetModelsViewWithLoggedInSuperAdmin()
    {
      $user = User::where('name', 'Super Admin User')->get()->first();

      $this->actingAs($user)
           ->visit('/models')
           ->see('Models');
    }

    public function testCreateNewAssetModelWithoutPartNumberAndPCSpec()
    {
      $user = User::where('name', 'Super Admin User')->get()->first();

      $this->actingAs($user)
           ->visit('/models')
           ->see('Create New Model')
           ->select(1, 'asset_type_id')
           ->select(1, 'manufacturer_id')
           ->type('Fake Model Name', 'asset_model')
           ->press('Add New Model')
           ->seePageIs('/models')
           ->see('Successfully created')
           ->seeInDatabase('asset_models', ['asset_type_id' => 1, 'manufacturer_id' => 1, 'asset_model' => 'Fake Model Name']);
    }

    public function testCreateNewAssetModelWithPartNumberAndPCSpec()
    {
      $user = User::where('name', 'Super Admin User')->get()->first();

      $this->actingAs($user)
           ->visit('/models')
           ->see('Create New Model')
           ->select(1, 'asset_type_id')
           ->select(1, 'manufacturer_id')
           ->type('Fake Model Name', 'asset_model')
           ->type('Fake Part Number', 'part_number')
           ->select(1, 'pcspec_id')
           ->press('Add New Model')
           ->seePageIs('/models')
           ->see('Successfully created')
           ->seeInDatabase('asset_models', ['asset_type_id' => 1, 'manufacturer_id' => 1, 'asset_model' => 'Fake Model Name', 'part_number' => 'Fake Part Number', 'pcspec_id' => 1]);
    }

    public function testEditAssetModel()
    {
      $user = User::where('name', 'Super Admin User')->get()->first();

      $this->actingAs($user)
           ->visit('/models')
           ->see('Create New Model')
           ->select(1, 'asset_type_id')
           ->select(1, 'manufacturer_id')
           ->type('Fake Model Name', 'asset_model')
           ->type('Fake Part Number', 'part_number')
           ->select(1, 'pcspec_id')
           ->press('Add New Model')
           ->seePageIs('/models')
           ->see('Successfully created');
     $this->seeInDatabase('asset_models', [
          'asset_type_id' => 1,
          'manufacturer_id' => 1,
          'asset_model' => 'Fake Model Name',
          'part_number' => 'Fake Part Number',
          'pcspec_id' => 1
      ]);

     $asset_model = AssetModel::where('asset_model', 'Fake Model Name')->first();

      $this->actingAs($user)
           ->visit('/models/' . $asset_model->getKey() . '/edit')
           ->see('Fake Model Name')
           ->select(2, 'asset_type_id')
           ->select(2, 'manufacturer_id')
           ->type('Another Fake Model Name', 'asset_model')
           ->type('Another Fake Part Number', 'part_number')
           ->select(2, 'pcspec_id')
           ->press('Edit Model')
           ->seePageIs('/models')
           ->see('Successfully updated')
           ->seeInDatabase('asset_models', ['asset_type_id' => 2, 'manufacturer_id' => 2, 'asset_model' => 'Another Fake Model Name', 'part_number' => 'Another Fake Part Number', 'pcspec_id' => 2]);
    }
}
