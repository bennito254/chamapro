import { Form, Head } from '@inertiajs/react';
import FormField from '@/components/shared/FormField';

export default function Page() {
    return (
        <>
            <Head title="Two-Factor Challenge" />

            <Form
                action="/portal/two-factor-challenge"
                method="post"
                resetOnSuccess={['password']}
            >
                {({ errors, processing }) => (
                    <>
                        <FormField
                            label="Code"
                            name="code"
                            error={errors.code}
                        />
                        <FormField
                            label="Recovery Code"
                            name="recovery_code"
                            error={errors.recovery_code}
                        />
                        <button
                            type="submit"
                            className="btn btn-primary mt-2 w-100"
                            disabled={processing}
                        >
                            {processing
                                ? 'Please wait...'
                                : 'Two-Factor Challenge'}
                        </button>
                    </>
                )}
            </Form>
        </>
    );
}
Page.layout = {
    title: 'Two-Factor Challenge',
    description: 'Secure access to ChamaPro',
};
