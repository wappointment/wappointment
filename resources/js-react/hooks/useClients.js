import { useState } from 'react';
import apiFetch from '../utils/apiFetch';
import { buildRoute } from '../config/routes';

export function useClients() {
    const [refreshKey, setRefreshKey] = useState(0);

    const createClient = async (formData) => {
        const { path, method } = buildRoute('clients.create');
        await apiFetch(path, {
            method,
            body: JSON.stringify(formData),
        });
        setRefreshKey(prev => prev + 1);
    };

    const updateClient = async (clientId, formData) => {
        const { path, method } = buildRoute('clients.update', { id: clientId });
        await apiFetch(path, {
            method,
            body: JSON.stringify(formData),
        });
        setRefreshKey(prev => prev + 1);
    };

    const deleteClient = async (clientId) => {
        const { path, method } = buildRoute('clients.delete', { id: clientId });
        await apiFetch(path, {
            method,
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
