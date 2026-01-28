import React, { useState } from 'react';
import ClientsTable from './ClientsTable';
import ClientDetailsModal from './ClientDetailsModal';
import ClientFormModal from './ClientFormModal';
import Notification from './Notification';
import { useClients } from '../hooks/useClients';
import { useNotification } from '../hooks/useNotification';

function Clients() {
    const [selectedClient, setSelectedClient] = useState(null);
    const [editingClient, setEditingClient] = useState(null);
    const [isCreating, setIsCreating] = useState(false);

    const { refreshKey, createClient, updateClient, deleteClient } = useClients();
    const { notification, showNotification, hideNotification } = useNotification();

    const handleSave = async (formData) => {
        try {
            if (editingClient) {
                await updateClient(editingClient.id, formData);
                showNotification('Client updated successfully');
            } else {
                await createClient(formData);
                showNotification('Client created successfully');
            }
        } catch (error) {
            showNotification('Operation failed: ' + error.message, 'error');
        }
    };

    const handleDelete = async (client) => {
        if (!confirm(`Are you sure you want to delete ${client.name || client.email}?`)) {
            return;
        }

        try {
            await deleteClient(client.id);
            showNotification('Client deleted successfully');
        } catch (error) {
            showNotification('Failed to delete client: ' + error.message, 'error');
        }
    };

    const handleCloseModal = () => {
        setIsCreating(false);
        setEditingClient(null);
    };

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
            
            <ClientsTable
                refreshKey={refreshKey}
                onClientClick={setSelectedClient}
                onEdit={setEditingClient}
                onDelete={handleDelete}
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
                    onClose={handleCloseModal}
                    onSave={handleSave}
                />
            )}
            
            {notification && (
                <Notification
                    message={notification.message}
                    type={notification.type}
                    onClose={hideNotification}
                />
            )}
        </>
    );
}

export default Clients;
