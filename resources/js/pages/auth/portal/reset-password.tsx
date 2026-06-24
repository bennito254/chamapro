import { Form, Head, Link } from '@inertiajs/react';
import AuthFormField from '@/components/auth/AuthFormField';
import AuthPageShell from '@/components/auth/AuthPageShell';
import { login } from '@/routes';
import { update } from '@/routes/password';

type Props = {
    email: string;
    token: string;
    status?: string;
    passwordRules?: string;
};

export default function Page({ email, token, status, passwordRules }: Props) {
    return (
        <>
            <Head title="Reset Password" />

            <AuthPageShell
                title="Choose a new password"
                description="Set a strong password to secure your ChamaPro account."
                footer={
                    <span>
                        <Link href={login()} className="cp-auth-link">
                            Return to sign in
                        </Link>
                    </span>
                }
            >
                {status && (
                    <div className="alert alert-success cp-auth-alert" role="status">
                        <i className="bi bi-check-circle me-2" />
                        {status}
                    </div>
                )}

                <Form {...update.form()} resetOnSuccess={['password', 'password_confirmation']}>
                    {({ errors, processing }) => (
                        <>
                            <input type="hidden" name="token" value={token} />

                            <AuthFormField
                                label="Email address"
                                name="email"
                                type="email"
                                icon="envelope"
                                defaultValue={email}
                                autoComplete="email"
                                required
                                error={errors.email}
                            />

                            <AuthFormField
                                label="New password"
                                name="password"
                                type="password"
                                icon="lock"
                                placeholder="Enter a new password"
                                autoComplete="new-password"
                                autoFocus
                                required
                                help={passwordRules}
                                error={errors.password}
                            />

                            <AuthFormField
                                label="Confirm new password"
                                name="password_confirmation"
                                type="password"
                                icon="shield-lock"
                                placeholder="Repeat your new password"
                                autoComplete="new-password"
                                required
                                error={errors.password_confirmation}
                            />

                            <button
                                type="submit"
                                className="btn btn-primary w-100 cp-auth-submit mt-1"
                                disabled={processing}
                            >
                                {processing ? (
                                    <>
                                        <span className="spinner-border spinner-border-sm me-2" />
                                        Updating password...
                                    </>
                                ) : (
                                    <>
                                        Update password
                                        <i className="bi bi-check2 ms-2" />
                                    </>
                                )}
                            </button>
                        </>
                    )}
                </Form>
            </AuthPageShell>
        </>
    );
}
