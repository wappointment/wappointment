import React from 'react';

function ClientActions({ client, onEdit, onDelete }) {
    return (
        <>
            <button
                onClick={(e) => {
                    e.stopPropagation();
                    onEdit(client);
                }}
                style={{
                    padding: '4px 8px',
                    marginRight: '5px',
                    border: '1px solid #0073aa',
                    borderRadius: '4px',
                    backgroundColor: 'white',
                    color: '#0073aa',
                    cursor: 'pointer',
                    fontSize: '12px'
                }}
            >
                Edit
            </button>
            <button
                onClick={(e) => {
                    e.stopPropagation();
                    onDelete(client);
                }}
                style={{
                    padding: '4px 8px',
                    border: '1px solid #dc3545',
                    borderRadius: '4px',
                    backgroundColor: 'white',
                    color: '#dc3545',
                    cursor: 'pointer',
                    fontSize: '12px'
                }}
            >
                Delete
            </button>
        </>
    );
}

export default ClientActions;
