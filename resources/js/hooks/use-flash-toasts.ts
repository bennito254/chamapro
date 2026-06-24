import { useEffect } from 'react';
import { usePage } from '@inertiajs/react';
import { toastError, toastSuccess } from '@/lib/toast';

type Flash = {
    success?: string | null;
    error?: string | null;
};

export function useFlashToasts(): void {
    const { flash } = usePage<{ flash: Flash }>().props;

    useEffect(() => {
        if (flash?.success) {
            toastSuccess(flash.success);
        }
        if (flash?.error) {
            toastError(flash.error);
        }
    }, [flash?.success, flash?.error]);
}
