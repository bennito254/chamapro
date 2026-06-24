type Props = {
    label: string;
    name: string;
    type?: string;
    value?: string | number;
    defaultValue?: string | number;
    error?: string;
    required?: boolean;
    placeholder?: string;
    icon?: string;
    help?: string;
    autoComplete?: string;
    autoFocus?: boolean;
};

export default function AuthFormField({
    label,
    name,
    type = 'text',
    value,
    defaultValue,
    error,
    required,
    placeholder,
    icon,
    help,
    autoComplete,
    autoFocus,
}: Props) {
    const inputId = `auth-field-${name}`;

    return (
        <div className="mb-3">
            <label htmlFor={inputId} className="form-label fw-medium">
                {label}
                {required && <span className="text-danger ms-1">*</span>}
            </label>
            <div className="input-group cp-auth-input-group">
                {icon && (
                    <span className="input-group-text">
                        <i className={`bi bi-${icon}`} />
                    </span>
                )}
                <input
                    id={inputId}
                    name={name}
                    type={type}
                    className={`form-control ${error ? 'is-invalid' : ''}`}
                    defaultValue={defaultValue ?? value}
                    placeholder={placeholder}
                    required={required}
                    autoComplete={autoComplete}
                    autoFocus={autoFocus}
                />
            </div>
            {help && <div className="form-text">{help}</div>}
            {error && <div className="invalid-feedback d-block">{error}</div>}
        </div>
    );
}
