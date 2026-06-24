type Props = {
    title: string;
    count?: number;
    children: React.ReactNode;
};

export default function ActivitySection({ title, count, children }: Props) {
    return (
        <div className="card border-0 shadow-sm mb-4">
            <div className="card-header bg-white border-bottom py-3 d-flex align-items-center gap-2">
                <h2 className="h6 mb-0">{title}</h2>
                {count !== undefined && (
                    <span className="badge bg-light text-dark border">{count}</span>
                )}
            </div>
            <div className="card-body p-0">{children}</div>
        </div>
    );
}
