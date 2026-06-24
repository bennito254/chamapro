<?php

use App\Features\Contributions\Models\Contribution;
use App\Features\Contributions\Models\ContributionChannel;
use App\Features\Contributions\Models\ContributionType;
use App\Features\Groups\Models\Group;
use App\Features\Groups\Services\GroupProvisioningService;
use App\Features\Members\Models\Member;
use App\Features\Reports\Services\ReportService;
use App\Features\Subscriptions\Models\SubscriptionPlan;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Database\Seeders\SubscriptionPlanSeeder;
use Spatie\Permission\PermissionRegistrar;

beforeEach(function () {
    $this->seed([SubscriptionPlanSeeder::class, RolesAndPermissionsSeeder::class]);
});

function createTreasurer(): User
{
    $plan = SubscriptionPlan::first();
    $group = Group::factory()->create();
    app(GroupProvisioningService::class)->provisionExisting($group, $plan);

    app(PermissionRegistrar::class)->setPermissionsTeamId($group->id);
    $user = User::factory()->create(['group_id' => $group->id, 'email_verified_at' => now()]);
    $user->assignRole('Treasurer');

    return $user;
}

function createMemberRoleUser(): User
{
    $plan = SubscriptionPlan::first();
    $group = Group::factory()->create();
    app(GroupProvisioningService::class)->provisionExisting($group, $plan);

    app(PermissionRegistrar::class)->setPermissionsTeamId($group->id);
    $user = User::factory()->create(['group_id' => $group->id, 'email_verified_at' => now()]);
    $user->assignRole('Member');

    return $user;
}

test('annual report groups contributions by month on sqlite', function () {
    $user = createTreasurer();
    $group = $user->group;

    $member = Member::factory()->create(['group_id' => $group->id]);
    $type = ContributionType::where('group_id', $group->id)->first();
    $channel = ContributionChannel::where('group_id', $group->id)->first();

    Contribution::create([
        'group_id' => $group->id,
        'member_id' => $member->id,
        'contribution_type_id' => $type->id,
        'contribution_channel_id' => $channel->id,
        'amount' => 500,
        'date' => '2026-03-15',
        'recorded_by' => $user->id,
    ]);

    $report = app(ReportService::class)->annualReport(2026);

    expect($report['contribution_trends'])->toHaveCount(1)
        ->and((int) $report['contribution_trends']->first()->month)->toBe(3)
        ->and((float) $report['contribution_trends']->first()->total)->toBe(500.0);
});

test('treasurer can view reports index and annual report page', function () {
    $user = createTreasurer();

    $this->actingAs($user)
        ->get(route('portal.reports.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->component('portal/reports/index'));

    $this->actingAs($user)
        ->get(route('portal.reports.show', ['type' => 'annual', 'year' => now()->year]))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('portal/reports/show')
            ->where('type', 'annual')
            ->has('data.summary')
            ->has('data.monthly_totals', 12));
});

test('member without export permission cannot export reports', function () {
    $user = createMemberRoleUser();

    $this->actingAs($user)
        ->get(route('portal.reports.export', ['type' => 'contributions', 'format' => 'csv']))
        ->assertForbidden();
});

test('treasurer can export contributions report as csv', function () {
    $user = createTreasurer();

    $response = $this->actingAs($user)
        ->get(route('portal.reports.export', ['type' => 'contributions', 'format' => 'csv']));

    $response->assertOk();
    expect($response->headers->get('content-type'))->toContain('text/csv');
});

test('contributions report accepts date filters as strings', function () {
    $user = createTreasurer();
    $group = $user->group;

    $member = Member::factory()->create(['group_id' => $group->id]);
    $type = ContributionType::where('group_id', $group->id)->first();
    $channel = ContributionChannel::where('group_id', $group->id)->first();

    Contribution::create([
        'group_id' => $group->id,
        'member_id' => $member->id,
        'contribution_type_id' => $type->id,
        'contribution_channel_id' => $channel->id,
        'amount' => 300,
        'date' => '2026-02-10',
        'recorded_by' => $user->id,
    ]);

    Contribution::create([
        'group_id' => $group->id,
        'member_id' => $member->id,
        'contribution_type_id' => $type->id,
        'contribution_channel_id' => $channel->id,
        'amount' => 700,
        'date' => '2026-04-10',
        'recorded_by' => $user->id,
    ]);

    $items = app(ReportService::class)->contributionsReport([
        'from' => '2026-03-01',
        'to' => '2026-03-31',
    ]);

    expect($items)->toHaveCount(0);
});
