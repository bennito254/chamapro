type Props = {
    title: string;
    count?: number;
    children: React.ReactNode;
};

export default function ActivitySection({ title, count, children }: Props) {
    return (
        <div className="card mb-4 border-0 shadow-sm">
            <div className="card-header border-bottom d-flex align-items-center gap-2 bg-white py-3">
                <h2 className="h6 mb-0">{title}</h2>
                {count !== undefined && (
                    <span className="badge bg-light text-dark border">
                        {count}
                    </span>
                )}
            </div>
            <div className="card-body p-0">{children}</div>
        </div>
    );
}
