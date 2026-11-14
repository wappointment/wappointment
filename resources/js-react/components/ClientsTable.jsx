import React from 'react';
import DataList from './DataList';
import ClientActions from './ClientActions';
import { buildRoute } from '../config/routes';

function ClientsTable({ refreshKey, onClientClick, onEdit, onDelete, permissions = {} }) {
    const hasActions = permissions.clientsEdit || permissions.clientsDelete;
    const columns = [
        { key: 'id', label: 'ID' },
        { key: 'name', label: 'Name' },
        { key: 'email', label: 'Email' },
        { key: 'created_at', label: 'Created' },
        ...(hasActions ? [{ key: 'actions', label: 'Actions' }] : []),
    ];

    const renderRow = (client) => (
        <>
            <td>{client.id}</td>
            <td>{client.name || 'N/A'}</td>
            <td>{client.email || 'N/A'}</td>
            <td>{client.created_at || 'N/A'}</td>
            {hasActions && <td>
                <ClientActions 
                    client={client}
                    onEdit={onEdit}
                    onDelete={onDelete}
                    canEdit={permissions.clientsEdit}
                    canDelete={permissions.clientsDelete}
                />
            </td>}
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
            searchPlaceholder="Search by name, email or client fields..."
            emptyMessage="No clients found"
        />
    );
}

export default ClientsTable;
