<?php

use App\Features\Contributions\Models\Contribution;
use App\Features\Contributions\Models\ContributionChannel;
use App\Features\Contributions\Models\ContributionType;
use App\Features\Groups\Models\Group;
use App\Features\Groups\Services\GroupProvisioningService;
use App\Features\Members\Models\Member;
use App\Features\Subscriptions\Models\SubscriptionPlan;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Database\Seeders\SubscriptionPlanSeeder;
use Spatie\Permission\PermissionRegistrar;

beforeEach(function () {
    $this->seed([SubscriptionPlanSeeder::class, RolesAndPermissionsSeeder::class]);
});

test('routes resolve models by sqid', function () {
    $plan = SubscriptionPlan::first();
    $group = Group::factory()->create();
    app(GroupProvisioningService::class)->provisionExisting($group, $plan);

    app(PermissionRegistrar::class)->setPermissionsTeamId($group->id);
    $user = User::factory()->create(['group_id' => $group->id, 'email_verified_at' => now()]);
    $user->assignRole('Treasurer');

    $member = Member::factory()->create(['group_id' => $group->id]);
    $type = ContributionType::where('group_id', $group->id)->first();
    $channel = ContributionChannel::where('group_id', $group->id)->first();

    $contribution = Contribution::create([
        'group_id' => $group->id,
        'member_id' => $member->id,
        'contribution_type_id' => $type->id,
        'contribution_channel_id' => $channel->id,
        'amount' => 500,
        'date' => now()->toDateString(),
        'recorded_by' => $user->id,
    ]);

    $this->actingAs($user)
        ->get(route('portal.contributions.show', $contribution))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('portal/contributions/show')
            ->where('contribution.id', $contribution->id)
            ->where('contribution.sqid', $contribution->sqid)
        );
});

test('numeric ids are not accepted in route model binding', function () {
    $plan = SubscriptionPlan::first();
    $group = Group::factory()->create();
    app(GroupProvisioningService::class)->provisionExisting($group, $plan);

    app(PermissionRegistrar::class)->setPermissionsTeamId($group->id);
    $user = User::factory()->create(['group_id' => $group->id, 'email_verified_at' => now()]);
    $user->assignRole('Treasurer');

    $member = Member::factory()->create(['group_id' => $group->id]);
    $type = ContributionType::where('group_id', $group->id)->first();
    $channel = ContributionChannel::where('group_id', $group->id)->first();

    $contribution = Contribution::create([
        'group_id' => $group->id,
        'member_id' => $member->id,
        'contribution_type_id' => $type->id,
        'contribution_channel_id' => $channel->id,
        'amount' => 500,
        'date' => now()->toDateString(),
        'recorded_by' => $user->id,
    ]);

    $this->actingAs($user)
        ->get('/portal/contributions/'.$contribution->id)
        ->assertNotFound();
});

test('serialized models include sqid alongside numeric id', function () {
    $member = Member::factory()->create();

    $array = $member->fresh()->toArray();

    expect($array)->toHaveKeys(['id', 'sqid'])
        ->and($array['id'])->toBeInt()
        ->and($array['sqid'])->toBeString()->not->toBeEmpty();
});
