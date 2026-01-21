import React, { useState, useEffect } from 'react';
import ErrorDetails from './ErrorDetails';

function FormModal({ 
    item, 
    onClose, 
    onSave, 
    title,
    fields,
    validate: customValidate
}) {
    const [formData, setFormData] = useState({});
    const [errors, setErrors] = useState({});
    const [apiError, setApiError] = useState(null);
    const [saving, setSaving] = useState(false);

    useEffect(() => {
        if (item) {
            const initialData = {};
            fields.forEach(field => {
                let value = item[field.name] || field.defaultValue || '';
                
                // Parse JSON fields
                if (field.type === 'json' && typeof value === 'string') {
                    try {
                        value = JSON.parse(value || '{}');
                    } catch {
                        value = {};
                    }
                }
                
                initialData[field.name] = value;
            });
            setFormData(initialData);
        } else {
            // Initialize with default values for create mode
            const initialData = {};
            fields.forEach(field => {
                initialData[field.name] = field.defaultValue !== undefined ? field.defaultValue : '';
            });
            setFormData(initialData);
        }
    }, [item, fields]);

    const handleChange = (e) => {
        const { name, value, type, checked } = e.target;
        const newValue = type === 'checkbox' ? checked : value;
        
        setFormData(prev => ({
            ...prev,
            [name]: newValue
        }));
        
        // Clear errors when user starts typing
        if (errors[name]) {
            setErrors(prev => ({ ...prev, [name]: '' }));
        }
        if (apiError) {
            setApiError(null);
        }
    };

    const validate = () => {
        const newErrors = {};
        
        fields.forEach(field => {
            if (field.required) {
                const value = formData[field.name];
                if (!value || (typeof value === 'string' && !value.trim())) {
                    newErrors[field.name] = `${field.label} is required`;
                }
            }
            
            // Custom field validation
            if (field.validate && formData[field.name]) {
                const error = field.validate(formData[field.name], formData);
                if (error) {
                    newErrors[field.name] = error;
                }
            }
        });
        
        // Custom form-level validation
        if (customValidate) {
            const customErrors = customValidate(formData);
            Object.assign(newErrors, customErrors);
        }
        
        setErrors(newErrors);
        return Object.keys(newErrors).length === 0;
    };

    const handleSubmit = async (e) => {
        e.preventDefault();
        
        if (!validate()) {
            return;
        }

        setSaving(true);
        setApiError(null);
        try {
            await onSave(formData);
            onClose();
        } catch (error) {
            setApiError(error);
        } finally {
            setSaving(false);
        }
    };

    const renderField = (field) => {
        const hasError = errors[field.name];
        const value = formData[field.name] || '';

        switch (field.type) {
            case 'textarea':
                return (
                    <textarea
                        name={field.name}
                        value={value}
                        onChange={handleChange}
                        required={field.required}
                        placeholder={field.placeholder}
                        rows={field.rows || 4}
                        style={{
                            width: '100%',
                            padding: '8px',
                            border: hasError ? '1px solid #dc3545' : '1px solid #ddd',
                            borderRadius: '4px',
                            boxSizing: 'border-box',
                            fontFamily: 'inherit',
                            resize: 'vertical'
                        }}
                    />
                );
            
            case 'checkbox':
                return (
                    <input
                        type="checkbox"
                        name={field.name}
                        checked={!!value}
                        onChange={handleChange}
                        style={{
                            width: 'auto',
                            marginRight: '8px'
                        }}
                    />
                );
            
            case 'select':
                return (
                    <select
                        name={field.name}
                        value={value}
                        onChange={handleChange}
                        required={field.required}
                        style={{
                            width: '100%',
                            padding: '8px',
                            border: hasError ? '1px solid #dc3545' : '1px solid #ddd',
                            borderRadius: '4px',
                            boxSizing: 'border-box'
                        }}
                    >
                        {field.options?.map(option => (
                            <option key={option.value} value={option.value}>
                                {option.label}
                            </option>
                        ))}
                    </select>
                );
            
            default:
                return (
                    <input
                        type={field.type || 'text'}
                        name={field.name}
                        value={value}
                        onChange={handleChange}
                        required={field.required}
                        placeholder={field.placeholder}
                        style={{
                            width: '100%',
                            padding: '8px',
                            border: hasError ? '1px solid #dc3545' : '1px solid #ddd',
                            borderRadius: '4px',
                            boxSizing: 'border-box'
                        }}
                    />
                );
        }
    };

    return (
        <div style={{
            position: 'fixed',
            top: 0,
            left: 0,
            right: 0,
            bottom: 0,
            backgroundColor: 'rgba(0,0,0,0.5)',
            display: 'flex',
            alignItems: 'center',
            justifyContent: 'center',
            zIndex: 10000
        }}>
            <div style={{
                backgroundColor: 'white',
                padding: '20px',
                borderRadius: '4px',
                maxWidth: '500px',
                width: '90%',
                maxHeight: '90vh',
                overflow: 'auto'
            }}>
                <h2 style={{ marginTop: 0 }}>
                    {item ? `Edit ${title}` : `Create ${title}`}
                </h2>
                
                <form onSubmit={handleSubmit}>
                    {fields.map(field => (
                        <div 
                            key={field.name} 
                            style={{ 
                                marginBottom: '15px',
                                display: field.type === 'checkbox' ? 'flex' : 'block',
                                alignItems: field.type === 'checkbox' ? 'center' : 'initial'
                            }}
                        >
                            <label style={{ 
                                display: field.type === 'checkbox' ? 'flex' : 'block',
                                marginBottom: field.type === 'checkbox' ? '0' : '5px',
                                fontWeight: 'bold',
                                alignItems: 'center'
                            }}>
                                {field.type === 'checkbox' && renderField(field)}
                                {field.label}
                                {field.required && field.type !== 'checkbox' && ' *'}
                            </label>
                            {field.type !== 'checkbox' && renderField(field)}
                            {errors[field.name] && (
                                <div style={{ color: '#dc3545', fontSize: '0.875rem', marginTop: '5px' }}>
                                    {errors[field.name]}
                                </div>
                            )}
                        </div>
                    ))}

                    {apiError && (
                        <div style={{ marginBottom: '15px' }}>
                            <ErrorDetails error={apiError} />
                        </div>
                    )}

                    <div style={{ display: 'flex', gap: '10px', justifyContent: 'flex-end' }}>
                        <button
                            type="button"
                            onClick={onClose}
                            disabled={saving}
                            style={{
                                padding: '8px 16px',
                                border: '1px solid #ddd',
                                borderRadius: '4px',
                                backgroundColor: 'white',
                                cursor: saving ? 'not-allowed' : 'pointer',
                                opacity: saving ? 0.5 : 1
                            }}
                        >
                            Cancel
                        </button>
                        <button
                            type="submit"
                            disabled={saving}
                            style={{
                                padding: '8px 16px',
                                border: 'none',
                                borderRadius: '4px',
                                backgroundColor: '#0073aa',
                                color: 'white',
                                cursor: saving ? 'not-allowed' : 'pointer',
                                opacity: saving ? 0.5 : 1
                            }}
                        >
                            {saving ? 'Saving...' : 'Save'}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    );
}

export default FormModal;
