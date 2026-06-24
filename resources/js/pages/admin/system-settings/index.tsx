import { Form, Head } from '@inertiajs/react';
import FormField from '@/components/shared/FormField';
import PageHeader from '@/components/shared/PageHeader';
import { update } from '@/routes/admin/system-settings';

type Props = { settings: Record<string, { key: string; value: string }> };

export default function SystemSettings({ settings }: Props) {
    const entries = Object.values(settings);
    return (<><Head title="System Settings" /><PageHeader title="System Settings" />
    <div className="card border-0 shadow-sm"><div className="card-body">
        <Form {...update.form()}>{({ errors, processing }) => (<>
            {entries.map((s) => <FormField key={s.key} label={s.key} name={`settings[${s.key}]`} defaultValue={s.value} error={errors[`settings.${s.key}`]} />)}
            <button type="submit" className="btn btn-primary" disabled={processing}>Save Settings</button>
        </>)}</Form>
    </div></div></>);
}
