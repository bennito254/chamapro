<?php

use App\Features\Banking\Models\BankAccount;
use App\Features\Contributions\Models\Contribution;
use App\Features\Contributions\Models\ContributionChannel;
use App\Features\Contributions\Models\ContributionType;
use App\Features\Groups\Models\Group;
use App\Features\Groups\Services\GroupProvisioningService;
use App\Features\Meetings\Models\Meeting;
use App\Features\Members\Models\Member;
use App\Features\Subscriptions\Models\SubscriptionPlan;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Database\Seeders\SubscriptionPlanSeeder;
use Spatie\Permission\PermissionRegistrar;

beforeEach(function () {
    $this->seed([SubscriptionPlanSeeder::class, RolesAndPermissionsSeeder::class]);
});

function provisionGroupWithTreasurer(): array
{
    $plan = SubscriptionPlan::first();
    $group = Group::factory()->create();
    app(GroupProvisioningService::class)->provisionExisting($group, $plan);

    app(PermissionRegistrar::class)->setPermissionsTeamId($group->id);
    $user = User::factory()->create(['group_id' => $group->id, 'email_verified_at' => now()]);
    $user->assignRole('Treasurer');

    $member = Member::factory()->create(['group_id' => $group->id]);
    $type = ContributionType::where('group_id', $group->id)->first();
    $channel = ContributionChannel::where('group_id', $group->id)->first();

    return compact('group', 'user', 'member', 'type', 'channel');
}

test('treasurer can access contribution pages', function () {
    ['user' => $user, 'group' => $group] = provisionGroupWithTreasurer();

    Meeting::create([
        'group_id' => $group->id,
        'title' => 'Older Meeting',
        'date' => '2026-01-10',
        'status' => 'completed',
    ]);

    $latestMeeting = Meeting::create([
        'group_id' => $group->id,
        'title' => 'Latest Meeting',
        'date' => '2026-03-15',
        'status' => 'scheduled',
    ]);

    $this->actingAs($user)
        ->get(route('portal.contributions.index'))
        ->assertOk();

    $this->actingAs($user)
        ->get(route('portal.contributions.create'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->has('meetings', 2)
            ->where('defaultMeetingId', $latestMeeting->id)
        );

    $this->actingAs($user)
        ->get(route('portal.contributions.bulk'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->has('meetings', 2)
            ->where('defaultMeetingId', $latestMeeting->id)
            ->has('memberContributionTotals')
        );
});

test('member cannot contribute again when required meeting amount is already met', function () {
    ['user' => $user, 'member' => $member, 'type' => $type, 'channel' => $channel] = provisionGroupWithTreasurer();
    $date = now()->toDateString();

    $this->actingAs($user)
        ->post(route('portal.contributions.bulk.store'), [
            'contribution_type_id' => $type->id,
            'contribution_channel_id' => $channel->id,
            'date' => $date,
            'entries' => [
                ['member_id' => $member->id, 'amount' => (float) $type->default_amount],
            ],
        ])
        ->assertRedirect(route('portal.contributions.by-date', $date));

    $this->actingAs($user)
        ->post(route('portal.contributions.bulk.store'), [
            'contribution_type_id' => $type->id,
            'contribution_channel_id' => $channel->id,
            'date' => $date,
            'entries' => [
                ['member_id' => $member->id, 'amount' => 100],
            ],
        ])
        ->assertSessionHasErrors('entries.0.member_id');

    expect(Contribution::where('member_id', $member->id)->count())->toBe(1);
});

test('treasurer can record bulk contributions', function () {
    ['user' => $user, 'member' => $member, 'type' => $type, 'channel' => $channel] = provisionGroupWithTreasurer();
    $date = now()->toDateString();

    $this->actingAs($user)
        ->post(route('portal.contributions.bulk.store'), [
            'contribution_type_id' => $type->id,
            'contribution_channel_id' => $channel->id,
            'date' => $date,
            'entries' => [
                ['member_id' => $member->id, 'amount' => 500],
            ],
        ])
        ->assertRedirect(route('portal.contributions.by-date', $date));

    expect(Contribution::where('member_id', $member->id)->count())->toBe(1);
});

test('contributions index groups records by meeting date', function () {
    ['user' => $user, 'member' => $member, 'type' => $type, 'channel' => $channel] = provisionGroupWithTreasurer();
    $date = '2026-01-15';

    $this->actingAs($user)->post(route('portal.contributions.bulk.store'), [
        'contribution_type_id' => $type->id,
        'contribution_channel_id' => $channel->id,
        'date' => $date,
        'entries' => [
            ['member_id' => $member->id, 'amount' => 500],
            ['member_id' => $member->id, 'amount' => 300],
        ],
    ]);

    $this->actingAs($user)
        ->get(route('portal.contributions.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('portal/contributions/index')
            ->has('dateGroups.data', 1)
            ->where('dateGroups.data.0.date', $date)
            ->where('dateGroups.data.0.contributions_count', 2)
            ->where('dateGroups.data.0.total_amount', 800)
        );
});

test('treasurer can view all contributions for a meeting date', function () {
    ['user' => $user, 'member' => $member, 'type' => $type, 'channel' => $channel, 'group' => $group] = provisionGroupWithTreasurer();
    $date = '2026-02-20';

    $secondType = ContributionType::create([
        'group_id' => $group->id,
        'name' => 'Welfare Fund',
        'amount_type' => 'fixed',
        'frequency' => 'monthly',
        'status' => 'active',
    ]);

    $this->actingAs($user)->post(route('portal.contributions.bulk.store'), [
        'contribution_type_id' => $type->id,
        'contribution_channel_id' => $channel->id,
        'date' => $date,
        'entries' => [
            ['member_id' => $member->id, 'amount' => 1000],
        ],
    ]);

    $this->actingAs($user)->post(route('portal.contributions.bulk.store'), [
        'contribution_type_id' => $secondType->id,
        'contribution_channel_id' => $channel->id,
        'date' => $date,
        'entries' => [
            ['member_id' => $member->id, 'amount' => 250],
        ],
    ]);

    $this->actingAs($user)
        ->get(route('portal.contributions.by-date', $date))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('portal/contributions/by-date')
            ->where('date', $date)
            ->has('contributionGroups', 2)
            ->where('summary.contributions_count', 2)
            ->where('summary.total_amount', 1250)
            ->where('summary.types_count', 2)
        );
});

test('treasurer can create contribution type', function () {
    ['user' => $user, 'group' => $group] = provisionGroupWithTreasurer();

    $this->actingAs($user)
        ->get(route('portal.contribution-types.create'))
        ->assertOk();

    $this->actingAs($user)
        ->post(route('portal.contribution-types.store'), [
            'name' => 'Building Fund',
            'description' => 'Monthly building contributions',
            'default_amount' => 1000,
            'amount_type' => 'fixed',
            'frequency' => 'monthly',
            'status' => 'active',
            'save_to_bank' => true,
        ])
        ->assertRedirect(route('portal.contribution-types.index'));

    $type = ContributionType::where('group_id', $group->id)->where('name', 'Building Fund')->first();

    expect($type)->not->toBeNull()
        ->and($type->save_to_bank)->toBeTrue();
});

test('treasurer can view and edit payment channel', function () {
    ['user' => $user, 'channel' => $channel] = provisionGroupWithTreasurer();

    $this->actingAs($user)
        ->get(route('portal.contribution-channels.index'))
        ->assertOk();

    $this->actingAs($user)
        ->get(route('portal.contribution-channels.edit', $channel))
        ->assertOk();

    $this->actingAs($user)
        ->post(route('portal.contribution-channels.store'), [
            'name' => 'Bank Transfer',
            'status' => 'active',
        ])
        ->assertRedirect(route('portal.contribution-channels.index'));
});

test('treasurer can create bank account', function () {
    ['user' => $user, 'group' => $group] = provisionGroupWithTreasurer();

    $this->actingAs($user)
        ->get(route('portal.bank-accounts.create'))
        ->assertOk();

    $this->actingAs($user)
        ->post(route('portal.bank-accounts.store'), [
            'account_name' => 'Main Operations',
            'bank_name' => 'Equity Bank',
            'account_number' => '1234567890',
            'branch' => 'Nairobi CBD',
            'opening_balance' => 50000,
            'status' => 'active',
        ])
        ->assertRedirect(route('portal.bank-accounts.index'));

    $account = BankAccount::where('group_id', $group->id)
        ->where('account_number', '1234567890')
        ->first();

    expect($account)->not->toBeNull()
        ->and($account->account_name)->toBe('Main Operations')
        ->and((float) $account->current_balance)->toBe(50000.0);
});
