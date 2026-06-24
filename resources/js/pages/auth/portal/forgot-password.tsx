import { Form, Head, Link } from '@inertiajs/react';
import AuthFormField from '@/components/auth/AuthFormField';
import AuthPageShell from '@/components/auth/AuthPageShell';
import { login } from '@/routes';
import { email } from '@/routes/password';

type Props = {
    status?: string;
};

export default function Page({ status }: Props) {
    return (
        <>
            <Head title="Forgot Password" />

            <AuthPageShell
                title="Reset your password"
                description="Enter the email linked to your account and we'll send you a reset link."
                footer={
                    <span>
                        Remember your password?{' '}
                        <Link href={login()} className="cp-auth-link">
                            Back to sign in
                        </Link>
                    </span>
                }
            >
                {status && (
                    <div className="alert alert-success cp-auth-alert" role="status">
                        <i className="bi bi-envelope-check me-2" />
                        {status}
                    </div>
                )}

                <div className="cp-auth-info-callout mb-4">
                    <i className="bi bi-info-circle me-2" />
                    Check your inbox and spam folder for the password reset email.
                </div>

                <Form {...email.form()}>
                    {({ errors, processing }) => (
                        <>
                            <AuthFormField
                                label="Email address"
                                name="email"
                                type="email"
                                icon="envelope"
                                placeholder="you@example.com"
                                autoComplete="email"
                                autoFocus
                                required
                                error={errors.email}
                            />

                            <button
                                type="submit"
                                className="btn btn-primary w-100 cp-auth-submit"
                                disabled={processing}
                            >
                                {processing ? (
                                    <>
                                        <span className="spinner-border spinner-border-sm me-2" />
                                        Sending link...
                                    </>
                                ) : (
                                    <>
                                        Email reset link
                                        <i className="bi bi-send ms-2" />
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
