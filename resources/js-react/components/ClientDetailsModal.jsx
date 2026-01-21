import React from 'react';
import DetailsModal from './DetailsModal';

const ClientDetailsModal = ({ client, onClose }) => {
    if (!client) return null;

    const fields = [
        { label: 'ID', value: client.id },
        { label: 'Email', value: client.email },
        { label: 'Name', value: client.name },
        { label: 'Deleted At', value: client.deleted_at },
        { label: 'Created At', value: client.created_at },
        { label: 'Updated At', value: client.updated_at },
    ];

    const jsonFields = client.options ? [
        { label: 'Options', value: client.options }
    ] : [];

    return (
        <DetailsModal
            title={`Client Details - ${client.name || client.email}`}
            fields={fields}
            jsonFields={jsonFields}
            onClose={onClose}
        />
    );
};

export default ClientDetailsModal;
