import Swal from 'sweetalert2';

export async function confirmDelete(
    title = 'Are you sure?',
    text = 'This action cannot be undone.',
): Promise<boolean> {
    const result = await Swal.fire({
        title,
        text,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, delete it',
    });

    return result.isConfirmed;
}

export async function confirmAction(
    title: string,
    text?: string,
): Promise<boolean> {
    const result = await Swal.fire({
        title,
        text,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#1e40af',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Confirm',
    });

    return result.isConfirmed;
}

export function showSuccess(title: string, text?: string): void {
    void Swal.fire({
        title,
        text,
        icon: 'success',
        timer: 2000,
        showConfirmButton: false,
    });
}

export function showError(title: string, text?: string): void {
    void Swal.fire({ title, text, icon: 'error' });
}

export default Swal;
