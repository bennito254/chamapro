import { Form, Head, Link } from '@inertiajs/react';
import AuthFormField from '@/components/auth/AuthFormField';
import AuthPageShell from '@/components/auth/AuthPageShell';
import { login, register } from '@/routes';
import { store } from '@/routes/login';
import { request as forgotPassword } from '@/routes/password';

type Props = {
    status?: string;
    canResetPassword?: boolean;
};

export default function Page({ status, canResetPassword }: Props) {
    return (
        <>
            <Head title="Log In" />

            <AuthPageShell
                title="Welcome back"
                description="Sign in to your chama portal to manage members, finances, and reports."
                footer={
                    <span>
                        Don&apos;t have an account?{' '}
                        <Link href={register()} className="cp-auth-link">
                            Create one
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

                <Form {...store.form()} resetOnSuccess={['password']}>
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

                            <AuthFormField
                                label="Password"
                                name="password"
                                type="password"
                                icon="lock"
                                placeholder="Enter your password"
                                autoComplete="current-password"
                                required
                                error={errors.password}
                            />

                            <div className="d-flex align-items-center justify-content-between mb-4">
                                <div className="form-check mb-0">
                                    <input
                                        className="form-check-input"
                                        type="checkbox"
                                        name="remember"
                                        id="remember"
                                    />
                                    <label className="form-check-label" htmlFor="remember">
                                        Remember me
                                    </label>
                                </div>
                                {canResetPassword && (
                                    <Link href={forgotPassword()} className="cp-auth-link small">
                                        Forgot password?
                                    </Link>
                                )}
                            </div>

                            <button
                                type="submit"
                                className="btn btn-primary w-100 cp-auth-submit"
                                disabled={processing}
                            >
                                {processing ? (
                                    <>
                                        <span className="spinner-border spinner-border-sm me-2" />
                                        Signing in...
                                    </>
                                ) : (
                                    <>
                                        Sign in
                                        <i className="bi bi-arrow-right ms-2" />
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
