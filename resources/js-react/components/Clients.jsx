import React, { useState } from 'react';
import DataList from './DataList';
import ClientDetailsModal from './ClientDetailsModal';
import ClientFormModal from './ClientFormModal';
import apiFetch from '../utils/apiFetch';

function Clients() {
    const [selectedClient, setSelectedClient] = useState(null);
    const [editingClient, setEditingClient] = useState(null);
    const [isCreating, setIsCreating] = useState(false);
    const [refreshKey, setRefreshKey] = useState(0);

    const columns = [
        { key: 'id', label: 'ID' },
        { key: 'name', label: 'Name' },
        { key: 'email', label: 'Email' },
        { key: 'created_at', label: 'Created' },
        { key: 'actions', label: 'Actions' },
    ];

    const handleSave = async (formData) => {
        if (editingClient) {
            // Update existing client
            await apiFetch(`/clients/${editingClient.id}`, {
                method: 'PUT',
                body: JSON.stringify(formData),
            });
        } else {
            // Create new client
            await apiFetch('/clients', {
                method: 'POST',
                body: JSON.stringify(formData),
            });
        }
        
        // Refresh the list
        setRefreshKey(prev => prev + 1);
    };

    const handleDelete = async (client) => {
        if (!confirm(`Are you sure you want to delete ${client.name || client.email}?`)) {
            return;
        }

        try {
            await apiFetch(`/clients/${client.id}`, {
                method: 'DELETE',
            });
            
            // Refresh the list
            setRefreshKey(prev => prev + 1);
        } catch (error) {
            alert('Failed to delete client: ' + error.message);
        }
    };

    const renderRow = (client) => (
        <>
            <td>{client.id}</td>
            <td>{client.name || 'N/A'}</td>
            <td>{client.email || 'N/A'}</td>
            <td>{client.created_at || 'N/A'}</td>
            <td>
                <button
                    onClick={(e) => {
                        e.stopPropagation();
                        setEditingClient(client);
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
                        handleDelete(client);
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
            </td>
        </>
    );

    return (
        <>
            <div style={{ marginBottom: '20px' }}>
                <button
                    onClick={() => setIsCreating(true)}
                    style={{
                        padding: '8px 16px',
                        border: 'none',
                        borderRadius: '4px',
                        backgroundColor: '#0073aa',
                        color: 'white',
                        cursor: 'pointer',
                        fontSize: '14px'
                    }}
                >
                    Create Client
                </button>
            </div>
            
            <DataList
                key={refreshKey}
                endpoint="/clients"
                title="Clients"
                columns={columns}
                renderRow={renderRow}
                onRowClick={setSelectedClient}
                searchPlaceholder="Search by name or email..."
                emptyMessage="No clients found"
            />
            
            {selectedClient && (
                <ClientDetailsModal 
                    client={selectedClient} 
                    onClose={() => setSelectedClient(null)} 
                />
            )}
            
            {(isCreating || editingClient) && (
                <ClientFormModal
                    client={editingClient}
                    onClose={() => {
                        setIsCreating(false);
                        setEditingClient(null);
                    }}
                    onSave={handleSave}
                />
            )}
        </>
    );
}

export default Clients;
