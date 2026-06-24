import { Form, Head, Link } from '@inertiajs/react';
import AuthFormField from '@/components/auth/AuthFormField';
import AuthPageShell from '@/components/auth/AuthPageShell';
import { login } from '@/routes';
import { store } from '@/routes/register';

type Props = {
    passwordRules?: string;
};

export default function Page({ passwordRules }: Props) {
    return (
        <>
            <Head title="Register" />

            <AuthPageShell
                title="Create your account"
                description="Register to start managing your chama with contributions, loans, and member records."
                footer={
                    <span>
                        Already have an account?{' '}
                        <Link href={login()} className="cp-auth-link">
                            Sign in
                        </Link>
                    </span>
                }
            >
                <Form {...store.form()} resetOnSuccess={['password', 'password_confirmation']}>
                    {({ errors, processing }) => (
                        <>
                            <AuthFormField
                                label="Full name"
                                name="name"
                                icon="person"
                                placeholder="Jane Wanjiku"
                                autoComplete="name"
                                autoFocus
                                required
                                error={errors.name}
                            />

                            <AuthFormField
                                label="Email address"
                                name="email"
                                type="email"
                                icon="envelope"
                                placeholder="you@example.com"
                                autoComplete="email"
                                required
                                error={errors.email}
                            />

                            <AuthFormField
                                label="Password"
                                name="password"
                                type="password"
                                icon="lock"
                                placeholder="Create a strong password"
                                autoComplete="new-password"
                                required
                                help={passwordRules}
                                error={errors.password}
                            />

                            <AuthFormField
                                label="Confirm password"
                                name="password_confirmation"
                                type="password"
                                icon="shield-lock"
                                placeholder="Repeat your password"
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
                                        Creating account...
                                    </>
                                ) : (
                                    <>
                                        Create account
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
