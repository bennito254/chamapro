import { Form, Head } from '@inertiajs/react';
type Props = { status?: string };
export default function VerifyEmail({ status }: Props) {
    return (
        <>
            <Head title="Verify Email" />
            <p className="small mb-4 text-muted">
                Please verify your email address.
            </p>
            {status && <div className="alert alert-success">{status}</div>}
            <Form
                action="/portal/email/verification-notification"
                method="post"
            >
                {({ processing }) => (
                    <button
                        type="submit"
                        className="btn btn-primary w-100"
                        disabled={processing}
                    >
                        Resend Email
                    </button>
                )}
            </Form>
        </>
    );
}
VerifyEmail.layout = { title: 'Verify Email' };
