import type { Auth } from '@/types/auth';
import type { Group, Subscription } from '@/types/models';

declare module 'react' {
    // eslint-disable-next-line @typescript-eslint/no-unused-vars
    interface InputHTMLAttributes<T> {
        passwordrules?: string;
    }
}

declare module '@inertiajs/core' {
    export interface InertiaConfig {
        sharedPageProps: {
            name: string;
            auth: Auth;
            group?: Group | null;
            subscription?: Subscription | null;
            permissions: string[];
            flash: {
                success?: string | null;
                error?: string | null;
            };
            sidebarOpen: boolean;
        };
    }
}
