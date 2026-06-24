<?php

use App\Features\Admin\Models\SmsProvider;
use App\Features\Groups\Models\Group;
use App\Features\Groups\Services\GroupProvisioningService;
use App\Features\Loans\Models\Loan;
use App\Features\Loans\Models\LoanApplication;
use App\Features\Loans\Models\LoanProduct;
use App\Features\Meetings\Models\Meeting;
use App\Features\Members\Models\Member;
use App\Features\Sms\Models\SmsMessage;
use App\Features\Sms\Models\SmsTemplate;
use App\Features\Subscriptions\Models\SubscriptionPlan;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Database\Seeders\SmsProviderSeeder;
use Database\Seeders\SubscriptionPlanSeeder;
use Illuminate\Support\Facades\File;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

beforeEach(function () {
    $this->seed([
        SubscriptionPlanSeeder::class,
        RolesAndPermissionsSeeder::class,
        SmsProviderSeeder::class,
    ]);
});

function provisionGroupWithSmsTreasurer(): array
{
    $plan = SubscriptionPlan::first();
    $group = Group::factory()->create();
    app(GroupProvisioningService::class)->provisionExisting($group, $plan);

    app(PermissionRegistrar::class)->setPermissionsTeamId($group->id);
    $user = User::factory()->create(['group_id' => $group->id, 'email_verified_at' => now()]);
    $user->assignRole('Treasurer');

    $member = Member::factory()->create([
        'group_id' => $group->id,
        'full_name' => 'Jane Wanjiku',
        'phone_number' => '+254712345678',
    ]);

    $template = SmsTemplate::query()->where('group_id', $group->id)->first();

    return compact('group', 'user', 'member', 'template');
}

test('treasurer can access sms pages', function () {
    ['user' => $user] = provisionGroupWithSmsTreasurer();

    $this->actingAs($user)
        ->get(route('portal.sms-templates.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->component('portal/sms-templates/index'));

    $this->actingAs($user)
        ->get(route('portal.sms-messages.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->component('portal/sms-messages/index'));

    $this->actingAs($user)
        ->get(route('portal.sms-messages.create'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('portal/sms-messages/create')
            ->has('templates')
            ->has('members')
            ->has('placeholders'));
});

test('treasurer can create sms template', function () {
    ['user' => $user, 'group' => $group] = provisionGroupWithSmsTreasurer();

    $this->actingAs($user)
        ->post(route('portal.sms-templates.store'), [
            'name' => 'Custom Reminder',
            'body' => 'Hello {name}, balance KES {loan_balance}.',
            'status' => 'active',
        ])
        ->assertRedirect(route('portal.sms-templates.index'));

    expect(SmsTemplate::query()
        ->where('group_id', $group->id)
        ->where('name', 'Custom Reminder')
        ->exists())->toBeTrue();
});

test('sms placeholders are resolved when sending to members', function () {
    ['user' => $user, 'group' => $group, 'member' => $member] = provisionGroupWithSmsTreasurer();

    $template = SmsTemplate::query()
        ->where('group_id', $group->id)
        ->where('name', 'Loan Balance Notice')
        ->first();

    Meeting::create([
        'group_id' => $group->id,
        'title' => 'June Meeting',
        'date' => now()->toDateString(),
        'status' => 'scheduled',
    ]);

    $product = LoanProduct::create([
        'group_id' => $group->id,
        'name' => 'Emergency Loan',
        'max_amount' => 100000,
        'max_multiplier' => 3,
        'interest_type' => 'percentage',
        'interest_value' => 10,
        'repayment_period' => 6,
        'grace_period' => 0,
        'status' => 'active',
    ]);

    $application = LoanApplication::create([
        'group_id' => $group->id,
        'member_id' => $member->id,
        'loan_product_id' => $product->id,
        'requested_amount' => 5000,
        'status' => 'approved',
    ]);

    Loan::create([
        'group_id' => $group->id,
        'loan_application_id' => $application->id,
        'member_id' => $member->id,
        'loan_product_id' => $product->id,
        'product_name' => 'Emergency Loan',
        'interest_type' => 'percentage',
        'interest_value' => 10,
        'repayment_period' => 6,
        'grace_period' => 0,
        'principal_amount' => 5000,
        'interest_amount' => 500,
        'total_amount' => 5500,
        'outstanding_balance' => 5500,
        'disbursement_date' => now()->subMonth()->toDateString(),
        'due_date' => now()->addMonths(6)->toDateString(),
        'status' => 'active',
        'disbursed_by' => $user->id,
    ]);

    $logPath = storage_path('logs/sms.log');
    File::delete($logPath);

    $this->actingAs($user)
        ->post(route('portal.sms-messages.store'), [
            'sms_template_id' => $template->id,
            'member_ids' => [$member->id],
        ])
        ->assertRedirect(route('portal.sms-messages.index'));

    $message = SmsMessage::query()->where('member_id', $member->id)->first();

    expect($message)->not->toBeNull()
        ->and($message->status)->toBe('sent')
        ->and($message->provider)->toBe('log')
        ->and($message->body)->toContain('Jane Wanjiku')
        ->and($message->body)->toContain('5,500.00');

    expect(File::exists($logPath))->toBeTrue();
    expect(File::get($logPath))->toContain('+254712345678');
    expect(File::get($logPath))->toContain('Jane Wanjiku');
});

test('preview endpoint returns rendered sms body', function () {
    ['user' => $user, 'member' => $member, 'template' => $template] = provisionGroupWithSmsTreasurer();

    $response = $this->actingAs($user)
        ->postJson(route('portal.sms-messages.preview'), [
            'sms_template_id' => $template->id,
            'member_id' => $member->id,
        ]);

    $response->assertOk();
    expect($response->json('body'))->toContain('Jane Wanjiku');
});

test('roles seeder syncs sms permissions to existing groups', function () {
    $this->seed([SubscriptionPlanSeeder::class, RolesAndPermissionsSeeder::class]);

    $plan = SubscriptionPlan::first();
    $group = Group::factory()->create();
    app(GroupProvisioningService::class)->provisionExisting($group, $plan);

    app(PermissionRegistrar::class)->setPermissionsTeamId($group->id);
    $treasurer = Role::query()
        ->where('name', 'Treasurer')
        ->where('group_id', $group->id)
        ->first();

    $treasurer->revokePermissionTo(['sms.view', 'sms.send', 'sms.manage']);

    app(RolesAndPermissionsSeeder::class)->run();

    app(PermissionRegistrar::class)->setPermissionsTeamId($group->id);

    expect($treasurer->fresh()->hasPermissionTo('sms.view'))->toBeTrue()
        ->and($treasurer->fresh()->hasPermissionTo('sms.send'))->toBeTrue()
        ->and($treasurer->fresh()->hasPermissionTo('sms.manage'))->toBeTrue();
});

test('member role does not receive sms permissions', function () {
    $this->seed([SubscriptionPlanSeeder::class, RolesAndPermissionsSeeder::class]);

    $plan = SubscriptionPlan::first();
    $group = Group::factory()->create();
    app(GroupProvisioningService::class)->provisionExisting($group, $plan);

    app(PermissionRegistrar::class)->setPermissionsTeamId($group->id);
    $memberRole = Role::query()
        ->where('name', 'Member')
        ->where('group_id', $group->id)
        ->first();

    expect($memberRole->hasPermissionTo('sms.view'))->toBeFalse()
        ->and($memberRole->hasPermissionTo('sms.send'))->toBeFalse();
});

test('treasurer can access sms template create page', function () {
    ['user' => $user] = provisionGroupWithSmsTreasurer();

    $this->actingAs($user)
        ->get(route('portal.sms-templates.create'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('portal/sms-templates/create')
            ->has('placeholders', 10));
});

test('dummy log provider is available by default', function () {
    $provider = SmsProvider::query()->where('driver', 'log')->first();

    expect($provider)->not->toBeNull()
        ->and($provider->is_default)->toBeTrue()
        ->and($provider->status)->toBe('active');
});
