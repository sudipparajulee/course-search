<?php

use App\Models\Application;
use App\Models\User;
use App\Support\CollegeApplicationForm;

function createTestApplicationFor(User $owner): Application
{
    $schema = CollegeApplicationForm::schema('britts');

    return Application::create([
        'user_id' => $owner->id,
        'course_id' => 'course-1',
        'course_name' => 'Certificate IV in Kitchen Management',
        'form_data' => CollegeApplicationForm::formData([
            'form_template' => 'britts',
            'source_provider_key' => 'britts',
            'source_provider_name' => 'Britts College',
            'source_course_id' => 'course-1',
            'source_course_title' => 'Certificate IV in Kitchen Management',
        ], $schema),
        'status' => 'submitted',
        'submitted_at' => now(),
    ]);
}

test('user can access only own application success and pdf pages', function () {
    $owner = User::factory()->create(['role' => 'user']);
    $otherUser = User::factory()->create(['role' => 'user']);

    $ownersApplication = createTestApplicationFor($owner);
    $otherUsersApplication = createTestApplicationFor($otherUser);

    $this->actingAs($owner)
        ->get(route('application.success', $ownersApplication))
        ->assertOk();

    $this->actingAs($owner)
        ->get(route('application.pdf', $ownersApplication))
        ->assertOk();

    $this->actingAs($owner)
        ->get(route('application.success', $otherUsersApplication))
        ->assertForbidden();

    $this->actingAs($owner)
        ->get(route('application.pdf', $otherUsersApplication))
        ->assertForbidden();
});

test('admin can access any user application success and pdf pages', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $student = User::factory()->create(['role' => 'user']);
    $application = createTestApplicationFor($student);

    $this->actingAs($admin)
        ->get(route('application.success', $application))
        ->assertOk();

    $this->actingAs($admin)
        ->get(route('application.pdf', $application))
        ->assertOk();
});

test('guest is redirected when opening application success and pdf pages', function () {
    $student = User::factory()->create(['role' => 'user']);
    $application = createTestApplicationFor($student);

    $this->get(route('application.success', $application))
        ->assertRedirect(route('login'));

    $this->get(route('application.pdf', $application))
        ->assertRedirect(route('login'));
});
