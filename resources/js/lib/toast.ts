import toastr from 'toastr';
import 'toastr/build/toastr.min.css';

toastr.options = {
    closeButton: true,
    progressBar: true,
    positionClass: 'toast-top-right',
    timeOut: 4000,
    extendedTimeOut: 1000,
};

export function toastSuccess(message: string): void {
    toastr.success(message);
}

export function toastError(message: string): void {
    toastr.error(message);
}

export function toastInfo(message: string): void {
    toastr.info(message);
}

export function toastWarning(message: string): void {
    toastr.warning(message);
}

export default toastr;
