<?php

namespace Tests;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use App\User;

class StoreroomDebugTest extends TestCase
{
    use DatabaseTransactions;

    public function testPatchSetsStoreroom()
    {
        $user = User::where('name', 'Super Admin User')->first();
        $this->actingAs($user);

        // Send PATCH to the storeroom update route
        $response = $this->patch(route('admin.storeroom.update'), ['store' => 1]);

        // Assert we redirected back to index
        $response->assertRedirect(route('admin.storeroom.index'));

        // Assert the DB has storeroom set to 1 for id 1
        $this->assertDatabaseHas('locations', ['id' => 1, 'storeroom' => 1]);
    }
}
