<?php

use App\Models\User;
use App\Models\Employee;
use App\Models\Alert;
use Carbon\Carbon;
use Illuminate\Http\UploadedFile;

test('guest cannot access bulk upload page', function () {
    $this->get('/alerts/upload')->assertRedirect('/login');
});

test('authenticated user can access bulk upload page', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
         ->get('/alerts/upload')
         ->assertOk();
});

test('user can download sample upload template', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)
         ->get('/alerts/sample');

    $response->assertOk();
    $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
    $response->assertHeader('Content-Disposition', 'attachment; filename="alerts_bulk_upload_sample.csv"');
    
    $content = $response->streamedContent();
    expect($content)->toContain('Title')
                    ->toContain('Document Type')
                    ->toContain('Employee ID')
                    ->toContain('Expiry Date')
                    ->toContain('Alert Days Before')
                    ->toContain('Description');
});

test('user can upload file and see tabular preview', function () {
    $user = User::factory()->create();

    $header = 'Title,Document Type,Employee ID,Expiry Date,Alert Days Before,Description';
    $row1 = 'Uploaded Visa Alert,Visa,,2026-12-31,30,Visa details here';
    $content = "{$header}\n{$row1}";

    $file = UploadedFile::fake()->createWithContent('alerts.csv', $content);

    $response = $this->actingAs($user)
         ->post('/alerts/preview', [
             'file' => $file,
         ]);

    $response->assertOk();
    $response->assertViewIs('alerts.preview');
    $response->assertViewHas('parsedAlerts');
    
    $parsed = $response->viewData('parsedAlerts');
    expect($parsed)->toHaveCount(1);
    expect($parsed[0]['title'])->toBe('Uploaded Visa Alert');
    expect($parsed[0]['document_type'])->toBe('Visa');
    expect($parsed[0]['expiry_date'])->toBe('2026-12-31');
});

test('user can store bulk uploaded alerts', function () {
    $user = User::factory()->create();

    $alertsData = [
        [
            'title' => 'Bulk Alert One',
            'document_type' => 'Visa',
            'employee_id' => null,
            'expiry_date' => Carbon::now()->addDays(40)->toDateString(),
            'alert_days_before' => 10,
            'description' => 'First bulk desc',
        ],
        [
            'title' => 'Bulk Alert Two',
            'document_type' => 'License',
            'employee_id' => null,
            'expiry_date' => Carbon::now()->addDays(5)->toDateString(),
            'alert_days_before' => 10,
            'description' => 'Second bulk desc',
        ],
    ];

    $response = $this->actingAs($user)
         ->post('/alerts/bulk-store', [
             'alerts' => json_encode($alertsData),
         ]);

    $response->assertRedirect('/alerts');
    $response->assertSessionHas('success');

    $this->assertDatabaseHas('alerts', [
        'title' => 'Bulk Alert One',
        'document_type' => 'Visa',
        'status' => 'Active',
    ]);

    $this->assertDatabaseHas('alerts', [
        'title' => 'Bulk Alert Two',
        'document_type' => 'License',
        'status' => 'Warning', // 5 days is within 10 days warning threshold
    ]);
});

test('bulk store enforces unique title/item name across upload', function () {
    $user = User::factory()->create();

    // Duplicate titles in the upload payload
    $alertsData = [
        [
            'title' => 'Duplicate Alert',
            'document_type' => 'Visa',
            'employee_id' => null,
            'expiry_date' => '2026-12-31',
            'alert_days_before' => 30,
            'description' => '',
        ],
        [
            'title' => 'Duplicate Alert',
            'document_type' => 'License',
            'employee_id' => null,
            'expiry_date' => '2026-10-15',
            'alert_days_before' => 15,
            'description' => '',
        ],
    ];

    $response = $this->actingAs($user)
         ->from('/alerts/preview')
         ->post('/alerts/bulk-store', [
             'alerts' => json_encode($alertsData),
         ]);

    $response->assertRedirect('/alerts/preview');
    $response->assertSessionHasErrors();
    
    $this->assertDatabaseMissing('alerts', [
        'title' => 'Duplicate Alert',
    ]);
});

test('bulk store enforces unique title/item name against existing database alerts', function () {
    $user = User::factory()->create();

    // Create an alert in DB
    Alert::create([
        'title' => 'Existing DB Alert',
        'document_type' => 'Visa',
        'expiry_date' => '2026-12-31',
        'alert_days_before' => 30,
        'created_by' => $user->id,
    ]);

    // Upload payload with the same title
    $alertsData = [
        [
            'title' => 'Existing DB Alert',
            'document_type' => 'License',
            'employee_id' => null,
            'expiry_date' => '2026-10-15',
            'alert_days_before' => 15,
            'description' => '',
        ],
    ];

    $response = $this->actingAs($user)
         ->from('/alerts/preview')
         ->post('/alerts/bulk-store', [
             'alerts' => json_encode($alertsData),
         ]);

    $response->assertRedirect('/alerts/preview');
    $response->assertSessionHasErrors();
});
