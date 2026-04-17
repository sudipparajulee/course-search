<?php

use App\Models\Application;
use App\Models\User;
use App\Support\CollegeApplicationForm;

test('aliased provider templates resolve to the correct local pdf asset', function () {
    expect(CollegeApplicationForm::originalPdfUrl('menzies'))->toContain('/menzies.pdf');
    expect(CollegeApplicationForm::originalPdfUrl('bc'))->toContain('/bc.pdf');
});

test('apply page opens the bentley mirrored form for laneway college', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->get('/apply?provider=laneway&provider_name=Laneway%20College&course=demo-course&course_name=Diploma%20of%20Business');

    $response
        ->assertOk()
        ->assertSee('Laneway College Application Form')
        ->assertSee('Selected course: Diploma of Business')
        ->assertSee('images/forms/bc/page-1.png', false);
});

test('application form submission is stored with the college specific template', function () {
    $user = User::factory()->create();

    $payload = [
        'form_template' => 'britts',
        'source_provider_key' => 'britts',
        'source_provider_name' => 'Britts College',
        'source_course_id' => 'course-1',
        'source_course_title' => 'Certificate IV in Kitchen Management',
        'text_field_2' => 'Aman Sharma',
        'radio_button_payment' => 'radio_button_payment_2',
    ];

    $response = $this
        ->actingAs($user)
        ->post(route('application.store'), $payload);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect();

    $application = Application::first();

    expect($application)->not->toBeNull();
    expect($application->user_id)->toBe($user->id);
    expect($application->status)->toBe('submitted');
    expect($application->course_id)->toBe('course-1');
    expect($application->course_name)->toBe('Certificate IV in Kitchen Management');
    expect($application->form_data['form_template'])->toBe('britts');
    expect($application->form_data['source_provider_key'])->toBe('britts');
    expect($application->form_data['source_provider_name'])->toBe('Britts College');
    expect($application->form_data['text_field_2'])->toBe('Aman Sharma');
    expect($application->form_data['radio_button_payment'])->toBe('radio_button_payment_2');
});

test('admin can update status and office-use fields for mirrored forms', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $student = User::factory()->create();
    $schema = CollegeApplicationForm::schema('bc');

    $application = Application::create([
        'user_id' => $student->id,
        'course_id' => 'course-1',
        'course_name' => 'Diploma of Business',
        'form_data' => CollegeApplicationForm::formData([
            'form_template' => 'bc',
            'source_provider_key' => 'laneway',
            'source_provider_name' => 'Laneway College',
            'source_course_id' => 'course-1',
            'source_course_title' => 'Diploma of Business',
            'start_dateddmmyy' => '17/04/2026',
        ], $schema),
        'status' => 'submitted',
        'submitted_at' => now(),
    ]);

    $response = $this
        ->actingAs($admin)
        ->put(route('admin.applications.update-status', $application), [
            'status' => 'approved',
            'notes' => 'Ready for offer letter.',
            'office_received_date' => '17/04/2026',
            'office_approved_date' => '18/04/2026',
            'approved_by' => 'Admissions Team',
            'office_signature' => 'Admin Signature',
        ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect();

    $application->refresh();

    expect($application->status)->toBe('approved');
    expect($application->notes)->toBe('Ready for offer letter.');
    expect($application->reviewed_at)->not->toBeNull();
    expect($application->form_data['form_template'])->toBe('bc');
    expect($application->form_data['source_provider_key'])->toBe('laneway');
    expect($application->form_data['office_received_date'])->toBe('17/04/2026');
    expect($application->form_data['office_approved_date'])->toBe('18/04/2026');
    expect($application->form_data['approved_by'])->toBe('Admissions Team');
    expect($application->form_data['office_signature'])->toBe('Admin Signature');
});
