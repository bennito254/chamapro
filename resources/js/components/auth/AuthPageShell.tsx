import type { ReactNode } from 'react';

type Props = {
    title: string;
    description: string;
    children: ReactNode;
    footer?: ReactNode;
};

export default function AuthPageShell({ title, description, children, footer }: Props) {
    return (
        <div className="cp-auth-panel">
            <div className="cp-auth-panel__header">
                <h1 className="cp-auth-panel__title">{title}</h1>
                <p className="cp-auth-panel__description">{description}</p>
            </div>

            <div className="cp-auth-panel__body">{children}</div>

            {footer && <div className="cp-auth-panel__footer">{footer}</div>}
        </div>
    );
}
