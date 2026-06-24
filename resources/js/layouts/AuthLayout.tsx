import { Link, usePage } from '@inertiajs/react';
import type { ReactNode } from 'react';
import { useFlashToasts } from '@/hooks/use-flash-toasts';

type Props = {
    children: ReactNode;
};

const highlights = [
    { icon: 'cash-stack', text: 'Track contributions, loans, and repayments' },
    { icon: 'shield-check', text: 'Role-based access for every officer' },
    { icon: 'graph-up-arrow', text: 'Reports and statements ready to export' },
] as const;

export default function AuthLayout({ children }: Props) {
    const { name } = usePage<{ name: string }>().props;
    useFlashToasts();

    return (
        <div className="cp-auth">
            <aside className="cp-auth-brand">
                <div className="cp-auth-brand__content">
                    <Link href="/" className="cp-auth-brand__logo text-decoration-none">
                        <span className="cp-auth-brand__logo-icon">
                            <i className="bi bi-building-fill" />
                        </span>
                        <span>{name}</span>
                    </Link>

                    <div className="cp-auth-brand__hero">
                        <h2 className="cp-auth-brand__headline">
                            Modern chama management, built for treasurers and members.
                        </h2>
                        <p className="cp-auth-brand__lead">
                            Securely manage your group finances, meetings, and member communication from one
                            portal.
                        </p>
                    </div>

                    <ul className="cp-auth-brand__list list-unstyled mb-0">
                        {highlights.map((item) => (
                            <li key={item.text} className="cp-auth-brand__list-item">
                                <span className="cp-auth-brand__list-icon">
                                    <i className={`bi bi-${item.icon}`} />
                                </span>
                                <span>{item.text}</span>
                            </li>
                        ))}
                    </ul>

                    <Link href="/" className="cp-auth-brand__home-link">
                        <i className="bi bi-arrow-left me-1" />
                        Back to homepage
                    </Link>
                </div>
            </aside>

            <main className="cp-auth-main">
                <div className="cp-auth-main__mobile-brand d-lg-none">
                    <Link href="/" className="cp-auth-main__mobile-logo text-decoration-none">
                        <span className="cp-auth-brand__logo-icon">
                            <i className="bi bi-building-fill" />
                        </span>
                        <span className="fw-bold">{name}</span>
                    </Link>
                </div>

                <div className="cp-auth-main__inner">{children}</div>
            </main>
        </div>
    );
}
