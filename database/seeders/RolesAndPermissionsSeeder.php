<?php

namespace Database\Seeders;

use App\Features\Groups\Models\Group;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    /** @var array<string, array<string>> */
    private array $matrix = [
        'Chairperson' => [
            'members.view', 'members.manage', 'members.import',
            'contributions.record', 'contributions.view',
            'loans.apply', 'loans.review', 'loans.approve', 'loans.disburse',
            'repayments.record', 'fines.manage', 'expenses.manage', 'bank.manage',
            'reports.view', 'reports.export', 'meetings.manage', 'welfare.manage',
            'shares.manage', 'dividends.run', 'settings.manage', 'users.manage',
            'sms.view', 'sms.send', 'sms.manage',
        ],
        'Treasurer' => [
            'members.view', 'members.manage', 'members.import',
            'contributions.record', 'contributions.view',
            'loans.apply', 'loans.disburse', 'repayments.record',
            'fines.manage', 'expenses.manage', 'bank.manage',
            'reports.view', 'reports.export', 'welfare.manage',
            'shares.manage', 'dividends.run',
            'sms.view', 'sms.send', 'sms.manage',
        ],
        'Secretary' => [
            'members.view', 'members.manage', 'contributions.view',
            'loans.apply', 'fines.manage', 'reports.view',
            'meetings.manage',
            'sms.view', 'sms.send', 'sms.manage',
        ],
        'Credit Committee' => [
            'members.view', 'contributions.view', 'loans.apply',
            'loans.review', 'loans.approve', 'reports.view',
        ],
        'Member' => [
            'members.view', 'contributions.view', 'loans.apply', 'reports.view',
        ],
    ];

    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = collect($this->matrix)->flatten()->unique()->values();

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        Group::query()->orderBy('id')->each(fn (Group $group) => $this->seedForGroup($group));
    }

    public function seedForGroup(Group $group): void
    {
        app()[PermissionRegistrar::class]->setPermissionsTeamId($group->id);

        foreach ($this->matrix as $roleName => $rolePermissions) {
            $role = Role::firstOrCreate([
                'name' => $roleName,
                'guard_name' => 'web',
                'group_id' => $group->id,
            ]);

            $role->syncPermissions($rolePermissions);
        }
    }
}
