import React from 'react';
import FormModal from './FormModal';

function ClientFormModal({ client, onClose, onSave }) {
    const fields = [
        {
            name: 'name',
            label: 'Name',
            type: 'text',
            required: false,
            defaultValue: ''
        },
        {
            name: 'email',
            label: 'Email',
            type: 'email',
            required: true,
            defaultValue: '',
            validate: (value) => {
                if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value)) {
                    return 'Invalid email format';
                }
                return null;
            }
        },
        {
            name: 'options',
            type: 'json',
            defaultValue: {}
        }
    ];

    return (
        <FormModal
            item={client}
            onClose={onClose}
            onSave={onSave}
            title="Client"
            fields={fields}
        />
    );
}

export default ClientFormModal;

