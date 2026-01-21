import React, { useState, useEffect } from 'react';
import apiFetch from '../utils/apiFetch';
import ErrorDetails from './ErrorDetails';
import SearchBox from './SearchBox';

const DataList = ({ 
    endpoint, 
    title, 
    columns, 
    renderRow, 
    onRowClick,
    searchPlaceholder,
    emptyMessage = 'No items found'
}) => {
    const [data, setData] = useState([]);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState(null);
    const [searchTerm, setSearchTerm] = useState('');
    const [pagination, setPagination] = useState({
        page: 1,
        perPage: 10,
        total: 0,
        totalPages: 0
    });

    useEffect(() => {
        loadData(pagination.page, searchTerm);
    }, []);

    const loadData = async (page, search = '') => {
        setLoading(true);
        setError(null);

        try {
            let url = `${endpoint}?page_num=${page}&per_page=${pagination.perPage}`;
            if (search) {
                url += `&search=${encodeURIComponent(search)}`;
            }
            
            const response = await apiFetch(url);
            const responseData = response.data;
            
            setData(responseData.data);
            setPagination({
                page: responseData.page,
                perPage: responseData.per_page,
                total: responseData.total,
                totalPages: responseData.total_pages
            });
        } catch (err) {
            setError(err);
        } finally {
            setLoading(false);
        }
    };

    const handlePageChange = (newPage) => {
        loadData(newPage, searchTerm);
    };

    const handleSearch = () => {
        loadData(1, searchTerm);
    };

    const handleClearSearch = () => {
        setSearchTerm('');
        loadData(1, '');
    };

    if (loading) {
        return <div className="wrap"><h1>{title}</h1><p>Loading...</p></div>;
    }

    if (error) {
        return (
            <div className="wrap">
                <h1>{title}</h1>
                <ErrorDetails error={error} />
                <button className="button" onClick={() => loadData(pagination.page, searchTerm)}>
                    Retry
                </button>
            </div>
        );
    }

    return (
        <div className="wrap">
            <h1>{title}</h1>
            
            {searchPlaceholder && (
                <SearchBox
                    value={searchTerm}
                    onChange={setSearchTerm}
                    onSearch={handleSearch}
                    onClear={handleClearSearch}
                    placeholder={searchPlaceholder}
                />
            )}
            
            <table className="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        {columns.map(col => (
                            <th key={col.key}>{col.label}</th>
                        ))}
                    </tr>
                </thead>
                <tbody>
                    {data.length === 0 ? (
                        <tr>
                            <td colSpan={columns.length}>{emptyMessage}</td>
                        </tr>
                    ) : (
                        data.map(item => (
                            <tr 
                                key={item.id}
                                onClick={() => onRowClick && onRowClick(item)}
                                style={{ cursor: onRowClick ? 'pointer' : 'default' }}
                                onMouseEnter={(e) => onRowClick && (e.currentTarget.style.backgroundColor = '#f0f0f0')}
                                onMouseLeave={(e) => onRowClick && (e.currentTarget.style.backgroundColor = '')}
                            >
                                {renderRow(item)}
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
};

export default DataList;
