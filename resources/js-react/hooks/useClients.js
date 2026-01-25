import { useState } from 'react';
import apiFetch from '../utils/apiFetch';

export function useClients() {
    const [refreshKey, setRefreshKey] = useState(0);

    const createClient = async (formData) => {
        await apiFetch('/clients', {
            method: 'POST',
            body: JSON.stringify(formData),
        });
        setRefreshKey(prev => prev + 1);
    };

    const updateClient = async (clientId, formData) => {
        await apiFetch(`/clients/${clientId}`, {
            method: 'POST',
            body: JSON.stringify(formData),
        });
        setRefreshKey(prev => prev + 1);
    };

    const deleteClient = async (clientId) => {
        await apiFetch(`/clients/delete/${clientId}`, {
            method: 'POST',
        });
        setRefreshKey(prev => prev + 1);
    };

    return {
        refreshKey,
        createClient,
        updateClient,
        deleteClient,
    };
}
