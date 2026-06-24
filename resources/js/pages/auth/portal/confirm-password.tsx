import { Form, Head } from '@inertiajs/react';
import FormField from '@/components/shared/FormField';

export default function Page() {
    return (
        <>
            <Head title="Confirm Password" />

            <Form
                action="/portal/user/confirm-password"
                method="post"
                resetOnSuccess={['password']}
            >
                {({ errors, processing }) => (
                    <>
                        <FormField
                            label="Password"
                            name="password"
                            type="password"
                            required
                            error={errors.password}
                        />
                        <button
                            type="submit"
                            className="btn btn-primary mt-2 w-100"
                            disabled={processing}
                        >
                            {processing ? 'Please wait...' : 'Confirm Password'}
                        </button>
                    </>
                )}
            </Form>
        </>
    );
}
Page.layout = {
    title: 'Confirm Password',
    description: 'Secure access to ChamaPro',
};
