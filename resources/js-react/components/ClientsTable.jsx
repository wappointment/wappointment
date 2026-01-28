import React from 'react';
import DataList from './DataList';
import ClientActions from './ClientActions';
import { buildRoute } from '../config/routes';

function ClientsTable({ refreshKey, onClientClick, onEdit, onDelete }) {
    const columns = [
        { key: 'id', label: 'ID' },
        { key: 'name', label: 'Name' },
        { key: 'email', label: 'Email' },
        { key: 'created_at', label: 'Created' },
        { key: 'actions', label: 'Actions' },
    ];

    const renderRow = (client) => (
        <>
            <td>{client.id}</td>
            <td>{client.name || 'N/A'}</td>
            <td>{client.email || 'N/A'}</td>
            <td>{client.created_at || 'N/A'}</td>
            <td>
                <ClientActions 
                    client={client}
                    onEdit={onEdit}
                    onDelete={onDelete}
                />
            </td>
        </>
    );

    const { path } = buildRoute('clients.list');

    return (
        <DataList
            key={refreshKey}
            endpoint={path}
            title="Clients"
            columns={columns}
            renderRow={renderRow}
            onRowClick={onClientClick}
            searchPlaceholder="Search by name or email..."
            emptyMessage="No clients found"
        />
    );
}

export default ClientsTable;
