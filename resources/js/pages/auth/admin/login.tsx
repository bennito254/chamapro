import { Form, Head } from '@inertiajs/react';
import AuthFormField from '@/components/auth/AuthFormField';
import AuthPageShell from '@/components/auth/AuthPageShell';
import { login as adminLogin } from '@/routes/admin';

export default function Page() {
    return (
        <>
            <Head title="Admin Sign In" />

            <AuthPageShell
                title="Admin sign in"
                description="Super-admin access to manage groups, subscriptions, and platform settings."
            >
                <Form action={adminLogin.url()} method="post" resetOnSuccess={['password']}>
                    {({ errors, processing }) => (
                        <>
                            <AuthFormField
                                label="Email address"
                                name="email"
                                type="email"
                                icon="envelope"
                                placeholder="admin@example.com"
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

                            <div className="form-check mb-4">
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
                                        Sign in to admin
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
