import '@/lib/toast';
import { createInertiaApp } from '@inertiajs/react';
import type { ComponentType } from 'react';
import { createRoot } from 'react-dom/client';
import 'bootstrap/dist/js/bootstrap.bundle.min.js';
import '../scss/app.scss';
import 'toastr/build/toastr.min.css';
import 'sweetalert2/dist/sweetalert2.min.css';

import AdminLayout from '@/layouts/AdminLayout';
import AuthLayout from '@/layouts/AuthLayout';
import PortalLayout from '@/layouts/PortalLayout';
import { initializeTheme } from '@/lib/theme';

const appName = import.meta.env.VITE_APP_NAME || 'ChamaPro';

createInertiaApp({
    title: (title) => (title ? `${title} - ${appName}` : appName),
    resolve: async (name) => {
        const pages = import.meta.glob('./pages/**/*.tsx');
        const importPage = pages[`./pages/${name}.tsx`];

        if (!importPage) {
            throw new Error(`Page not found: ${name}`);
        }

        const module = (await importPage()) as { default: ComponentType };

        return module;
    },
    setup({ el, App, props }) {
        const root = createRoot(el);
        root.render(<App {...props} />);
    },
    layout: (name) => {
        if (name.startsWith('admin/')) {
            return AdminLayout;
        }

        if (name.startsWith('portal/')) {
            return PortalLayout;
        }

        if (name.startsWith('auth/admin/') || name.startsWith('auth/portal/')) {
            return AuthLayout;
        }

        return undefined;
    },
    progress: {
        color: '#1e40af',
        showSpinner: true,
    },
});

initializeTheme();
