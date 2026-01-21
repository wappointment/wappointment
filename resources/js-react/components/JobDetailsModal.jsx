import React from 'react';
import DetailsModal from './DetailsModal';

const JobDetailsModal = ({ job, onClose }) => {
    if (!job) return null;

    const fields = [
        { label: 'ID', value: job.id },
        { label: 'Queue', value: job.queue },
        { label: 'Appointment ID', value: job.appointment_id },
        { label: 'Attempts', value: job.attempts },
        { label: 'Reserved At', value: job.reserved_at, format: 'timestamp' },
        { label: 'Available At', value: job.available_at, format: 'timestamp' },
        { label: 'Created At', value: job.created_at, format: 'timestamp' },
    ];

    const jsonFields = [
        { label: 'Payload', value: job.payload }
    ];

    return (
        <DetailsModal
            title={`Job Details - ID: ${job.id}`}
            fields={fields}
            jsonFields={jsonFields}
            onClose={onClose}
        />
    );
};

export default JobDetailsModal;
