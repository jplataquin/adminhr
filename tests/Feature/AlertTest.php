<?php

use App\Models\User;
use App\Models\Employee;
use App\Models\Alert;
use Carbon\Carbon;

test('guest cannot access alerts dashboard', function () {
    $response = $this->get('/alerts');
    $response->assertRedirect('/login');
});

test('authenticated user can access alerts dashboard', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->get('/alerts');

    $response->assertOk();
});

test('user can register an alert document', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->post('/alerts', [
            'title' => 'Visa Renewal',
            'document_type' => 'Visa',
            'expiry_date' => Carbon::tomorrow()->toDateString(),
            'alert_days_before' => 5,
            'description' => 'Need to renew tomorrow.',
        ]);

    $response->assertRedirect('/alerts');
    $response->assertSessionHas('success');

    $this->assertDatabaseHas('alerts', [
        'title' => 'Visa Renewal',
        'document_type' => 'Visa',
        'status' => 'Warning', // Tomorrow is within 5 days threshold, so status should be Warning
        'created_by' => $user->id,
    ]);
});

test('status is correctly calculated on creation', function () {
    $user = User::factory()->create();

    // 1. Far in future should be Active
    $this->actingAs($user)->post('/alerts', [
        'title' => 'Active Contract',
        'document_type' => 'Contract',
        'expiry_date' => Carbon::now()->addDays(50)->toDateString(),
        'alert_days_before' => 10,
    ]);

    $this->assertDatabaseHas('alerts', [
        'title' => 'Active Contract',
        'status' => 'Active',
    ]);

    // 2. Already expired should be Expired
    $this->actingAs($user)->post('/alerts', [
        'title' => 'Expired Contract',
        'document_type' => 'Contract',
        'expiry_date' => Carbon::yesterday()->toDateString(),
        'alert_days_before' => 10,
    ]);

    $this->assertDatabaseHas('alerts', [
        'title' => 'Expired Contract',
        'status' => 'Expired',
    ]);
});

test('user can update an alert', function () {
    $user = User::factory()->create();
    $alert = Alert::create([
        'title' => 'Old Title',
        'document_type' => 'License',
        'expiry_date' => Carbon::now()->addDays(30)->toDateString(),
        'alert_days_before' => 5,
        'created_by' => $user->id,
    ]);

    $response = $this
        ->actingAs($user)
        ->put("/alerts/{$alert->id}", [
            'title' => 'New Title',
            'document_type' => 'License',
            'expiry_date' => Carbon::now()->addDays(30)->toDateString(),
            'alert_days_before' => 5,
            'description' => 'Updated desc',
        ]);

    $response->assertRedirect('/alerts');
    
    $alert->refresh();
    $this->assertSame('New Title', $alert->title);
    $this->assertSame('Updated desc', $alert->description);
    $this->assertSame($user->id, $alert->updated_by);
});

test('user can renew an alert with a new future date', function () {
    $user = User::factory()->create();
    $alert = Alert::create([
        'title' => 'Expired Item',
        'document_type' => 'License',
        'expiry_date' => Carbon::yesterday()->toDateString(),
        'alert_days_before' => 5,
        'status' => 'Expired',
        'created_by' => $user->id,
    ]);

    $response = $this
        ->actingAs($user)
        ->post("/alerts/{$alert->id}/renew", [
            'expiry_date' => Carbon::now()->addDays(15)->toDateString(),
            'alert_days_before' => 5,
        ]);

    $response->assertRedirect('/alerts');
    $response->assertSessionHas('success');

    $alert->refresh();
    $this->assertSame('Active', $alert->status); // Now far enough in future to be active
    $this->assertSame(Carbon::now()->addDays(15)->toDateString(), $alert->expiry_date->toDateString());
});

test('user can delete an alert', function () {
    $user = User::factory()->create();
    $alert = Alert::create([
        'title' => 'To Be Deleted',
        'document_type' => 'License',
        'expiry_date' => Carbon::now()->addDays(30)->toDateString(),
        'alert_days_before' => 5,
        'created_by' => $user->id,
    ]);

    $response = $this
        ->actingAs($user)
        ->delete("/alerts/{$alert->id}");

    $response->assertRedirect('/alerts');
    $this->assertSoftDeleted($alert);
});

test('console command alerts:check transitions alert statuses correctly', function () {
    $user = User::factory()->create();

    // 1. Alert that is active but now moves into warning threshold
    $alertToWarning = Alert::create([
        'title' => 'Nearing Expiry',
        'document_type' => 'License',
        'expiry_date' => Carbon::now()->addDays(5)->toDateString(),
        'alert_days_before' => 10,
        'status' => 'Active',
        'created_by' => $user->id,
    ]);

    // 2. Alert that is warning but now expired
    $alertToExpired = Alert::create([
        'title' => 'Past Expiry',
        'document_type' => 'License',
        'expiry_date' => Carbon::today()->toDateString(), // expiring today -> expired
        'alert_days_before' => 10,
        'status' => 'Warning',
        'created_by' => $user->id,
    ]);

    // Run the artisan check command
    $this->artisan('alerts:check')->assertExitCode(0);

    $alertToWarning->refresh();
    $alertToExpired->refresh();

    $this->assertSame('Warning', $alertToWarning->status);
    $this->assertSame('Expired', $alertToExpired->status);
});
