type Props = {
    rows?: number;
    columns?: number;
    type?: 'table' | 'card' | 'text';
};

export default function SkeletonLoader({ rows = 5, columns = 4, type = 'table' }: Props) {
    if (type === 'card') {
        return (
            <div className="row g-3">
                {Array.from({ length: columns }).map((_, i) => (
                    <div key={i} className="col-md-3">
                        <div className="card border-0 shadow-sm">
                            <div className="card-body placeholder-glow">
                                <span className="placeholder col-6 mb-2" />
                                <span className="placeholder col-8" />
                            </div>
                        </div>
                    </div>
                ))}
            </div>
        );
    }

    if (type === 'text') {
        return (
            <div className="placeholder-glow">
                {Array.from({ length: rows }).map((_, i) => (
                    <span key={i} className="placeholder col-12 mb-2 d-block" />
                ))}
            </div>
        );
    }

    return (
        <div className="card border-0 shadow-sm">
            <div className="table-responsive placeholder-glow p-3">
                <table className="table">
                    <thead>
                        <tr>
                            {Array.from({ length: columns }).map((_, i) => (
                                <th key={i}>
                                    <span className="placeholder col-8" />
                                </th>
                            ))}
                        </tr>
                    </thead>
                    <tbody>
                        {Array.from({ length: rows }).map((_, ri) => (
                            <tr key={ri}>
                                {Array.from({ length: columns }).map((_, ci) => (
                                    <td key={ci}>
                                        <span className="placeholder col-10" />
                                    </td>
                                ))}
                            </tr>
                        ))}
                    </tbody>
                </table>
            </div>
        </div>
    );
}
