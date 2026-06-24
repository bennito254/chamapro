import { Head, Link, usePage } from '@inertiajs/react';
import { dashboard } from '@/routes/portal';
import { login, register } from '@/routes';
import { login as adminLogin } from '@/routes/admin';

const features = [
    {
        icon: 'bi-people-fill',
        title: 'Member management',
        description:
            'Onboard members, track statuses, guarantors, and activity — all scoped to your chama group.',
    },
    {
        icon: 'bi-cash-stack',
        title: 'Contributions & shares',
        description:
            'Record individual or bulk contributions, configure types and frequencies, and manage share purchases.',
    },
    {
        icon: 'bi-bank',
        title: 'Loans & repayments',
        description:
            'Loan products, applications, disbursements, guarantors, and principal or interest repayments with full audit trails.',
    },
    {
        icon: 'bi-journal-text',
        title: 'Ledger & banking',
        description:
            'Double-entry journal, chart of accounts, bank and cash accounts — finances you can trust and reconcile.',
    },
    {
        icon: 'bi-calendar-event',
        title: 'Meetings & fines',
        description:
            'Schedule meetings, capture attendance, track expenses, and apply fines with clear meeting summaries.',
    },
    {
        icon: 'bi-chat-dots-fill',
        title: 'SMS messaging',
        description:
            'Template-based member SMS with smart placeholders for balances, dues, and reminders.',
    },
] as const;

const highlights = [
    { value: 'Multi-tenant', label: 'Groups with isolated data' },
    { value: 'Role-based', label: 'Granular permissions' },
    { value: 'M-Pesa ready', label: 'STK push integration' },
    { value: 'Reports', label: 'PDF & Excel exports' },
] as const;

export default function Welcome() {
    const { auth } = usePage().props;

    return (
        <>
            <Head title="Chama management, simplified" />

            <div className="cp-landing">
                <nav className="cp-landing-nav navbar navbar-expand-lg">
                    <div className="container">
                        <Link href="/" className="navbar-brand cp-landing-brand">
                            <span className="cp-landing-brand__icon">
                                <i className="bi bi-building-fill" />
                            </span>
                            ChamaPro
                        </Link>

                        <button
                            className="navbar-toggler border-0 text-white"
                            type="button"
                            data-bs-toggle="collapse"
                            data-bs-target="#landingNav"
                            aria-controls="landingNav"
                            aria-expanded="false"
                            aria-label="Toggle navigation"
                        >
                            <i className="bi bi-list fs-3" />
                        </button>

                        <div className="collapse navbar-collapse" id="landingNav">
                            <ul className="navbar-nav ms-auto align-items-lg-center gap-lg-1">
                                <li className="nav-item">
                                    <a className="nav-link cp-landing-nav-link" href="#features">
                                        Features
                                    </a>
                                </li>
                                <li className="nav-item">
                                    <a className="nav-link cp-landing-nav-link" href="#highlights">
                                        Why ChamaPro
                                    </a>
                                </li>
                                {auth.user ? (
                                    <li className="nav-item ms-lg-2">
                                        <Link href={dashboard()} className="btn btn-light btn-sm px-3">
                                            Go to dashboard
                                        </Link>
                                    </li>
                                ) : (
                                    <>
                                        <li className="nav-item ms-lg-2">
                                            <Link href={login()} className="btn btn-outline-light btn-sm px-3">
                                                Sign in
                                            </Link>
                                        </li>
                                        <li className="nav-item ms-lg-2">
                                            <Link href={register()} className="btn btn-light btn-sm px-3">
                                                Get started
                                            </Link>
                                        </li>
                                    </>
                                )}
                            </ul>
                        </div>
                    </div>
                </nav>

                <header className="cp-landing-hero">
                    <div className="container">
                        <div className="row align-items-center g-5">
                            <div className="col-lg-6">
                                <span className="cp-landing-eyebrow">
                                    <i className="bi bi-stars me-1" />
                                    Built for Kenyan chamas
                                </span>
                                <h1 className="cp-landing-hero__title">
                                    Run your chama with clarity, confidence, and control.
                                </h1>
                                <p className="cp-landing-hero__lead">
                                    ChamaPro brings contributions, loans, meetings, ledger accounting, and member
                                    communication into one secure portal — so treasurers spend less time on spreadsheets
                                    and more time growing the group.
                                </p>
                                <div className="d-flex flex-wrap gap-2 mt-4">
                                    {auth.user ? (
                                        <Link href={dashboard()} className="btn btn-light btn-lg px-4">
                                            Open dashboard
                                            <i className="bi bi-arrow-right ms-2" />
                                        </Link>
                                    ) : (
                                        <>
                                            <Link href={register()} className="btn btn-light btn-lg px-4">
                                                Start free trial
                                                <i className="bi bi-arrow-right ms-2" />
                                            </Link>
                                            <Link href={login()} className="btn btn-outline-light btn-lg px-4">
                                                Sign in
                                            </Link>
                                        </>
                                    )}
                                </div>
                            </div>

                            <div className="col-lg-6">
                                <div className="cp-landing-hero-card">
                                    <div className="cp-landing-hero-card__header">
                                        <span className="cp-landing-hero-card__dot" />
                                        <span className="cp-landing-hero-card__dot" />
                                        <span className="cp-landing-hero-card__dot" />
                                    </div>
                                    <div className="cp-landing-hero-card__body">
                                        <div className="cp-landing-stat-row">
                                            <div>
                                                <div className="cp-landing-stat-label">Net cash in</div>
                                                <div className="cp-landing-stat-value text-success">KES 124,500</div>
                                            </div>
                                            <i className="bi bi-graph-up-arrow fs-3 text-success opacity-75" />
                                        </div>
                                        <div className="cp-landing-stat-row">
                                            <div>
                                                <div className="cp-landing-stat-label">Active loans</div>
                                                <div className="cp-landing-stat-value">12 members</div>
                                            </div>
                                            <i className="bi bi-bank fs-3 text-primary opacity-75" />
                                        </div>
                                        <div className="cp-landing-stat-row mb-0">
                                            <div>
                                                <div className="cp-landing-stat-label">SMS reminders sent</div>
                                                <div className="cp-landing-stat-value">48 this month</div>
                                            </div>
                                            <i className="bi bi-chat-dots fs-3 text-info opacity-75" />
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </header>

                <section id="highlights" className="cp-landing-section cp-landing-section--muted">
                    <div className="container">
                        <div className="row g-4">
                            {highlights.map((item) => (
                                <div key={item.label} className="col-6 col-lg-3">
                                    <div className="cp-landing-highlight text-center">
                                        <div className="cp-landing-highlight__value">{item.value}</div>
                                        <div className="cp-landing-highlight__label">{item.label}</div>
                                    </div>
                                </div>
                            ))}
                        </div>
                    </div>
                </section>

                <section id="features" className="cp-landing-section">
                    <div className="container">
                        <div className="text-center mb-5 cp-landing-section__intro">
                            <h2 className="cp-landing-section__title">Everything your chama needs</h2>
                            <p className="cp-landing-section__subtitle mx-auto">
                                From the first member contribution to year-end dividends — ChamaPro keeps every
                                transaction tied to your chart of accounts and member records.
                            </p>
                        </div>

                        <div className="row g-4">
                            {features.map((feature) => (
                                <div key={feature.title} className="col-md-6 col-lg-4">
                                    <article className="cp-landing-feature h-100">
                                        <div className="cp-landing-feature__icon">
                                            <i className={`bi ${feature.icon}`} />
                                        </div>
                                        <h3 className="cp-landing-feature__title">{feature.title}</h3>
                                        <p className="cp-landing-feature__text mb-0">{feature.description}</p>
                                    </article>
                                </div>
                            ))}
                        </div>
                    </div>
                </section>

                <section className="cp-landing-cta">
                    <div className="container text-center">
                        <h2 className="cp-landing-cta__title">Ready to modernize your chama?</h2>
                        <p className="cp-landing-cta__text mx-auto">
                            Create your group, invite officers, and start recording contributions in minutes.
                        </p>
                        <div className="d-flex flex-wrap justify-content-center gap-2">
                            {auth.user ? (
                                <Link href={dashboard()} className="btn btn-light btn-lg px-4">
                                    Go to dashboard
                                </Link>
                            ) : (
                                <>
                                    <Link href={register()} className="btn btn-light btn-lg px-4">
                                        Create your account
                                    </Link>
                                    <Link href={login()} className="btn btn-outline-light btn-lg px-4">
                                        Sign in to portal
                                    </Link>
                                </>
                            )}
                        </div>
                    </div>
                </section>

                <footer className="cp-landing-footer">
                    <div className="container d-flex flex-column flex-md-row align-items-center justify-content-between gap-3">
                        <div className="d-flex align-items-center gap-2 text-white-50 small">
                            <i className="bi bi-building-fill" />
                            <span>&copy; {new Date().getFullYear()} ChamaPro</span>
                        </div>
                        <div className="d-flex gap-3 small">
                            <Link href={login()} className="cp-landing-footer-link">
                                Member portal
                            </Link>
                            <Link href={adminLogin()} className="cp-landing-footer-link">
                                Admin
                            </Link>
                        </div>
                    </div>
                </footer>
            </div>
        </>
    );
}
