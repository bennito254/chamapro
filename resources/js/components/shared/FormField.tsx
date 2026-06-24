type Props = {
    label: string;
    name: string;
    type?: string;
    value?: string | number;
    defaultValue?: string | number;
    error?: string;
    required?: boolean;
    placeholder?: string;
    options?: Array<{ value: string; label: string }>;
    rows?: number;
    help?: string;
};

export default function FormField({
    label,
    name,
    type = 'text',
    value,
    defaultValue,
    error,
    required,
    placeholder,
    options,
    rows,
    help,
}: Props) {
    const inputId = `field-${name}`;

    return (
        <div className="mb-3">
            <label htmlFor={inputId} className="form-label">
                {label}
                {required && <span className="text-danger ms-1">*</span>}
            </label>
            {options ? (
                <select
                    id={inputId}
                    name={name}
                    className={`form-select ${error ? 'is-invalid' : ''}`}
                    defaultValue={defaultValue ?? value ?? ''}
                    required={required}
                >
                    <option value="">Select...</option>
                    {options.map((opt) => (
                        <option key={opt.value} value={opt.value}>
                            {opt.label}
                        </option>
                    ))}
                </select>
            ) : type === 'textarea' ? (
                <textarea
                    id={inputId}
                    name={name}
                    className={`form-control ${error ? 'is-invalid' : ''}`}
                    rows={rows ?? 3}
                    defaultValue={defaultValue ?? value}
                    placeholder={placeholder}
                    required={required}
                />
            ) : (
                <input
                    id={inputId}
                    name={name}
                    type={type}
                    className={`form-control ${error ? 'is-invalid' : ''}`}
                    defaultValue={defaultValue ?? value}
                    placeholder={placeholder}
                    required={required}
                />
            )}
            {help && <div className="form-text">{help}</div>}
            {error && <div className="invalid-feedback d-block">{error}</div>}
        </div>
    );
}
