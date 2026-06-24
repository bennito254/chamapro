import { Form, Head } from '@inertiajs/react';
import Chart from 'react-apexcharts';
import DataTable from '@/components/shared/DataTable';
import FormField from '@/components/shared/FormField';
import PageHeader from '@/components/shared/PageHeader';
import QuickLinks from '@/components/shared/QuickLinks';
import StatCard from '@/components/shared/StatCard';
import { formatDateTime } from '@/lib/format';
import { adminQuickLinks } from '@/lib/navigation';
import { update as updateMpesaSettings } from '@/routes/admin/mpesa-settings';
import type { Group } from '@/types/models';

type Stats = {
    groups_total: number;
    groups_active: number;
    groups_suspended: number;
    subscriptions_active: number;
    subscriptions_trial: number;
    subscriptions_expired: number;
    open_tickets: number;
    payments_completed: number;
    payments_pending: number;
};

type MpesaSettings = {
    mpesa_consumer_key: string;
    mpesa_consumer_secret: string;
    mpesa_shortcode: string;
    mpesa_passkey: string;
    mpesa_callback_url: string;
    mpesa_environment: string;
    mpesa_stk_enabled: boolean;
    configured: boolean;
    stub_mode: boolean;
};

type Props = {
    stats: Stats;
    recentGroups: Group[];
    mpesaSettings: MpesaSettings;
};

export default function AdminDashboard({
    stats,
    recentGroups,
    mpesaSettings,
}: Props) {
    const today = formatDateTime(new Date());

    return (
        <>
            <Head title="Admin Dashboard" />
            <PageHeader title="Platform Dashboard" description={today} />

            <div className="row g-3 mb-4">
                <div className="col-sm-6 col-xl-3">
                    <StatCard
                        title="Total Groups"
                        value={stats.groups_total}
                        icon="buildings"
                    />
                </div>
                <div className="col-sm-6 col-xl-3">
                    <StatCard
                        title="Active Groups"
                        value={stats.groups_active}
                        icon="check-circle-fill"
                        color="success"
                    />
                </div>
                <div className="col-sm-6 col-xl-3">
                    <StatCard
                        title="Active Subscriptions"
                        value={stats.subscriptions_active}
                        subtitle={`${stats.subscriptions_trial} on trial`}
                        icon="credit-card-2-front"
                        color="info"
                    />
                </div>
                <div className="col-sm-6 col-xl-3">
                    <StatCard
                        title="M-Pesa Payments"
                        value={stats.payments_completed}
                        subtitle={`${stats.payments_pending} pending`}
                        icon="phone"
                        color="primary"
                    />
                </div>
            </div>

            <div className="mb-4">
                <QuickLinks links={adminQuickLinks} />
            </div>

            <div className="row g-4 mb-4">
                <div className="col-lg-4">
                    <div className="card cp-panel h-100 border-0">
                        <div className="card-header border-0 bg-transparent px-4 pt-4">
                            <h6 className="fw-semibold mb-0">
                                Subscription Mix
                            </h6>
                        </div>
                        <div className="card-body">
                            <Chart
                                type="donut"
                                height={280}
                                series={[
                                    stats.subscriptions_active,
                                    stats.subscriptions_trial,
                                    stats.subscriptions_expired,
                                ]}
                                options={{
                                    labels: ['Active', 'Trial', 'Expired'],
                                    colors: ['#059669', '#f59e0b', '#ef4444'],
                                    legend: { position: 'bottom' },
                                    chart: { fontFamily: 'inherit' },
                                }}
                            />
                        </div>
                    </div>
                </div>
                <div className="col-lg-8">
                    <div className="card cp-panel h-100 border-0">
                        <div className="card-header d-flex justify-content-between align-items-center border-0 bg-transparent px-4 pt-4">
                            <h6 className="fw-semibold mb-0">Recent Groups</h6>
                            <span className="badge bg-primary-subtle text-primary">
                                {stats.groups_suspended} suspended
                            </span>
                        </div>
                        <div className="card-body pt-0">
                            <DataTable
                                searchable={false}
                                data={recentGroups}
                                columns={[
                                    { key: 'name', label: 'Name' },
                                    {
                                        key: 'status',
                                        label: 'Status',
                                        render: (r) => (
                                            <span
                                                className={`badge cp-badge-status cp-badge-status--${r.status}`}
                                            >
                                                {r.status}
                                            </span>
                                        ),
                                    },
                                    { key: 'county', label: 'County' },
                                ]}
                            />
                        </div>
                    </div>
                </div>
            </div>

            <div className="card border-0 shadow-sm">
                <div className="card-header d-flex justify-content-between align-items-center bg-white">
                    <div>
                        <h5 className="fw-semibold mb-1">
                            M-Pesa Express (Subscription Billing)
                        </h5>
                        <p className="small mb-0 text-muted">
                            Platform Daraja credentials for subscription
                            checkout. Group-level M-Pesa settings remain
                            separate.
                        </p>
                    </div>
                    <span
                        className={`badge ${mpesaSettings.stub_mode ? 'bg-warning-subtle text-warning' : 'bg-success-subtle text-success'}`}
                    >
                        {mpesaSettings.stub_mode
                            ? 'Stub / sandbox'
                            : 'Live configured'}
                    </span>
                </div>
                <div className="card-body">
                    <Form
                        {...updateMpesaSettings.form()}
                        options={{ preserveScroll: true }}
                    >
                        {({ errors, processing }) => (
                            <div className="row g-3">
                                <div className="col-md-6">
                                    <FormField
                                        label="Consumer key"
                                        name="mpesa_consumer_key"
                                        defaultValue={
                                            mpesaSettings.mpesa_consumer_key
                                        }
                                        error={errors.mpesa_consumer_key}
                                    />
                                </div>
                                <div className="col-md-6">
                                    <FormField
                                        label="Consumer secret"
                                        name="mpesa_consumer_secret"
                                        type="password"
                                        defaultValue={
                                            mpesaSettings.mpesa_consumer_secret
                                        }
                                        error={errors.mpesa_consumer_secret}
                                    />
                                </div>
                                <div className="col-md-4">
                                    <FormField
                                        label="Shortcode"
                                        name="mpesa_shortcode"
                                        defaultValue={
                                            mpesaSettings.mpesa_shortcode
                                        }
                                        error={errors.mpesa_shortcode}
                                    />
                                </div>
                                <div className="col-md-4">
                                    <FormField
                                        label="Passkey"
                                        name="mpesa_passkey"
                                        type="password"
                                        defaultValue={
                                            mpesaSettings.mpesa_passkey
                                        }
                                        error={errors.mpesa_passkey}
                                    />
                                </div>
                                <div className="col-md-4">
                                    <FormField
                                        label="Environment"
                                        name="mpesa_environment"
                                        options={[
                                            {
                                                value: 'sandbox',
                                                label: 'Sandbox',
                                            },
                                            {
                                                value: 'production',
                                                label: 'Production',
                                            },
                                        ]}
                                        defaultValue={
                                            mpesaSettings.mpesa_environment
                                        }
                                        error={errors.mpesa_environment}
                                    />
                                </div>
                                <div className="col-12">
                                    <FormField
                                        label="Callback URL"
                                        name="mpesa_callback_url"
                                        defaultValue={
                                            mpesaSettings.mpesa_callback_url
                                        }
                                        placeholder="https://your-domain.com/api/mpesa/callback"
                                        error={errors.mpesa_callback_url}
                                    />
                                </div>
                                <div className="col-12">
                                    <div className="form-check">
                                        <input
                                            type="hidden"
                                            name="mpesa_stk_enabled"
                                            value="0"
                                        />
                                        <input
                                            className="form-check-input"
                                            type="checkbox"
                                            name="mpesa_stk_enabled"
                                            id="mpesa_stk_enabled"
                                            value="1"
                                            defaultChecked={
                                                mpesaSettings.mpesa_stk_enabled
                                            }
                                        />
                                        <label
                                            className="form-check-label"
                                            htmlFor="mpesa_stk_enabled"
                                        >
                                            Enable M-Pesa Express checkout for
                                            subscription renewals
                                        </label>
                                    </div>
                                </div>
                                <div className="col-12">
                                    <button
                                        type="submit"
                                        className="btn btn-primary"
                                        disabled={processing}
                                    >
                                        Save M-Pesa settings
                                    </button>
                                </div>
                            </div>
                        )}
                    </Form>
                </div>
            </div>
        </>
    );
}
