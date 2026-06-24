import { Link, router } from '@inertiajs/react';
import { confirmDelete } from '@/lib/alerts';

type Props = {
    editHref?: string;
    deleteHref?: string;
};

export default function AdminRowActions({ editHref, deleteHref }: Props) {
    const handleDelete = async () => {
        if (!deleteHref) {
            return;
        }

        const confirmed = await confirmDelete();

        if (confirmed) {
            router.delete(deleteHref);
        }
    };

    return (
        <div className="d-flex gap-1 justify-content-end">
            {editHref && (
                <Link href={editHref} className="btn btn-sm btn-outline-primary">
                    Edit
                </Link>
            )}
            {deleteHref && (
                <button type="button" className="btn btn-sm btn-outline-danger" onClick={handleDelete}>
                    Delete
                </button>
            )}
        </div>
    );
}
