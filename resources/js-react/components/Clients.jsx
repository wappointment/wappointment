import React, { useState, useEffect } from 'react';
import apiFetch from '../utils/apiFetch';

function Clients() {
    const [clients, setClients] = useState([]);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState(null);
    const [pagination, setPagination] = useState({
        page: 1,
        perPage: 10,
        total: 0,
        totalPages: 0
    });

    useEffect(() => {
        loadClients(pagination.page);
    }, []);

    const loadClients = async (page) => {
        setLoading(true);
        setError(null);

        try {
            const response = await apiFetch(`/clients?page_num=${page}&per_page=${pagination.perPage}`);
            
            setClients(response.data);
            setPagination({
                page: response.page,
                perPage: response.per_page,
                total: response.total,
                totalPages: response.total_pages
            });
        } catch (err) {
            setError(err.message);
        } finally {
            setLoading(false);
        }
    };

    const handlePageChange = (newPage) => {
        loadClients(newPage);
    };

    if (loading) {
        return <div className="wrap"><h1>Clients</h1><p>Loading...</p></div>;
    }

    if (error) {
        return <div className="wrap"><h1>Clients</h1><p>Error: {error}</p></div>;
    }

    return (
        <div className="wrap">
            <h1>Clients</h1>
            
            <table className="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Created</th>
                    </tr>
                </thead>
                <tbody>
                    {clients.length === 0 ? (
                        <tr>
                            <td colSpan="4">No clients found</td>
                        </tr>
                    ) : (
                        clients.map(client => (
                            <tr key={client.id}>
                                <td>{client.id}</td>
                                <td>{client.name || 'N/A'}</td>
                                <td>{client.email || 'N/A'}</td>
                                <td>{client.created_at || 'N/A'}</td>
                            </tr>
                        ))
                    )}
                </tbody>
            </table>

            {pagination.totalPages > 1 && (
                <div className="tablenav">
                    <div className="tablenav-pages">
                        <span className="displaying-num">{pagination.total} items</span>
                        {pagination.page > 1 && (
                            <button 
                                className="button" 
                                onClick={() => handlePageChange(pagination.page - 1)}
                            >
                                Previous
                            </button>
                        )}
                        <span className="paging-input">
                            Page {pagination.page} of {pagination.totalPages}
                        </span>
                        {pagination.page < pagination.totalPages && (
                            <button 
                                className="button" 
                                onClick={() => handlePageChange(pagination.page + 1)}
                            >
                                Next
                            </button>
                        )}
                    </div>
                </div>
            )}
        </div>
    );
}

export default Clients;
