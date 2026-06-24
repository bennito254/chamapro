import { Form, Head } from '@inertiajs/react';
import FormField from '@/components/shared/FormField';
import PageHeader from '@/components/shared/PageHeader';
import { formatCurrency } from '@/lib/format';
import { store } from '@/routes/portal/subscription/renew';
import type { Group, SubscriptionPlan } from '@/types/models';

type MpesaConfig = {
    stk_enabled: boolean;
    stub_mode: boolean;
    default_phone?: string | null;
};

type Props = {
    group?: Group | null;
    plans: SubscriptionPlan[];
    mpesa: MpesaConfig;
};

export default function RenewSubscription({ group, plans, mpesa }: Props) {
    return (
        <>
            <Head title="Renew Subscription" />
            <PageHeader title="Renew Subscription" description={group?.name} />

            <div className="row g-4">
                <div className="col-lg-7">
                    <div className="card border-0 shadow-sm">
                        <div className="card-body">
                            <h5 className="fw-semibold mb-3">Pay with M-Pesa Express</h5>
                            <p className="text-muted small mb-4">
                                Select your plan and enter the M-Pesa number to receive the STK push prompt on your phone.
                            </p>

                            <Form {...store.form()}>
                                {({ errors, processing }) => (
                                    <>
                                        <FormField
                                            label="Plan"
                                            name="subscription_plan_id"
                                            required
                                            options={plans.map((plan) => ({
                                                value: String(plan.id),
                                                label: `${plan.name} — ${formatCurrency(plan.amount)}`,
                                            }))}
                                            error={errors.subscription_plan_id}
                                        />
                                        <FormField
                                            label="M-Pesa phone number"
                                            name="phone_number"
                                            type="tel"
                                            required
                                            defaultValue={mpesa.default_phone ?? ''}
                                            placeholder="2547XXXXXXXX"
                                            error={errors.phone_number}
                                        />
                                        <button type="submit" className="btn btn-primary" disabled={processing || !mpesa.stk_enabled}>
                                            {processing ? 'Sending request…' : 'Pay with M-Pesa'}
                                        </button>
                                    </>
                                )}
                            </Form>
                        </div>
                    </div>
                </div>

                <div className="col-lg-5">
                    <div className="card border-0 shadow-sm h-100">
                        <div className="card-body">
                            <h6 className="fw-semibold mb-3">How it works</h6>
                            <ol className="text-muted small ps-3 mb-4">
                                <li className="mb-2">Choose your subscription plan.</li>
                                <li className="mb-2">Enter the phone number registered with M-Pesa.</li>
                                <li className="mb-2">Approve the payment on your phone when prompted.</li>
                                <li>Your subscription activates immediately after payment.</li>
                            </ol>

                            {!mpesa.stk_enabled && (
                                <div className="alert alert-warning small mb-0">
                                    M-Pesa checkout is currently disabled by the platform administrator.
                                </div>
                            )}

                            {mpesa.stub_mode && mpesa.stk_enabled && (
                                <div className="alert alert-info small mb-0">
                                    Sandbox/stub mode is active — payments complete instantly without a real STK push.
                                </div>
                            )}
                        </div>
                    </div>
                </div>
            </div>
        </>
    );
}
