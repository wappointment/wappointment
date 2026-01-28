import React, { useState } from 'react';

const DetailsModal = ({ title, fields, jsonFields = [], onClose }) => {
    const [expandedSections, setExpandedSections] = useState({});

    const toggleSection = (section) => {
        setExpandedSections(prev => ({
            ...prev,
            [section]: !prev[section]
        }));
    };

    const parseJSON = (value) => {
        if (!value) return null;
        try {
            return JSON.parse(value);
        } catch {
            return value;
        }
    };

    const renderValue = (value, depth = 0, prefix = '') => {
        if (value === null) return <span style={{ color: '#999' }}>null</span>;
        if (value === undefined) return <span style={{ color: '#999' }}>undefined</span>;
        if (typeof value === 'boolean') return <span style={{ color: '#0086b3' }}>{value.toString()}</span>;
        if (typeof value === 'number') return <span style={{ color: '#0086b3' }}>{value}</span>;
        if (typeof value === 'string') return <span style={{ color: '#d14' }}>"{value}"</span>;
        
        if (Array.isArray(value)) {
            if (value.length === 0) return <span>[]</span>;
            return (
                <div style={{ marginLeft: '20px' }}>
                    {value.map((item, index) => (
                        <div key={index}>
                            <span style={{ color: '#999' }}>[{index}]:</span> {renderValue(item, depth + 1, `${prefix}-${index}`)}
                        </div>
                    ))}
                </div>
            );
        }
        
        if (typeof value === 'object') {
            const keys = Object.keys(value);
            if (keys.length === 0) return <span>{'{}'}</span>;
            
            return (
                <div style={{ marginLeft: depth > 0 ? '20px' : '0' }}>
                    {keys.map(key => {
                        const sectionKey = `${prefix}-${depth}-${key}`;
                        return (
                            <div key={key} style={{ marginBottom: '5px' }}>
                                <a
                                    href="#"
                                    onClick={(e) => {
                                        e.preventDefault();
                                        toggleSection(sectionKey);
                                    }}
                                    style={{ 
                                        color: '#0073aa',
                                        textDecoration: 'none',
                                        fontWeight: 'bold'
                                    }}
                                >
                                    {expandedSections[sectionKey] ? '▼' : '▶'} {key}:
                                </a>
                                {expandedSections[sectionKey] && (
                                    <div style={{ marginLeft: '20px', marginTop: '5px' }}>
                                        {renderValue(value[key], depth + 1, sectionKey)}
                                    </div>
                                )}
                            </div>
                        );
                    })}
                </div>
            );
        }
        
        return <span>{String(value)}</span>;
    };

    const formatTimestamp = (timestamp) => {
        if (!timestamp) return 'N/A';
        // Check if it's a Unix timestamp (number)
        if (typeof timestamp === 'number') {
            return new Date(timestamp * 1000).toLocaleString();
        }
        // Otherwise assume it's already a date string
        return timestamp;
    };

    return (
        <div style={{
            position: 'fixed',
            top: 0,
            left: 0,
            right: 0,
            bottom: 0,
            backgroundColor: 'rgba(0,0,0,0.5)',
            display: 'flex',
            alignItems: 'center',
            justifyContent: 'center',
            zIndex: 9999
        }} onClick={onClose}>
            <div style={{
                backgroundColor: 'white',
                padding: '30px',
                borderRadius: '5px',
                maxWidth: '800px',
                maxHeight: '80vh',
                overflow: 'auto',
                position: 'relative',
                width: '90%'
            }} onClick={(e) => e.stopPropagation()}>
                <button
                    onClick={onClose}
                    style={{
                        position: 'absolute',
                        top: '10px',
                        right: '10px',
                        border: 'none',
                        background: '#f0f0f0',
                        padding: '5px 10px',
                        cursor: 'pointer',
                        borderRadius: '3px',
                        fontSize: '18px'
                    }}
                >
                    ×
                </button>

                <h2 style={{ marginTop: 0 }}>{title}</h2>

                <div style={{ marginBottom: '20px' }}>
                    <table className="wp-list-table widefat" style={{ marginBottom: '20px' }}>
                        <tbody>
                            {fields.map(field => (
                                <tr key={field.label}>
                                    <th style={{ width: '200px' }}>{field.label}</th>
                                    <td>
                                        {field.format === 'timestamp' 
                                            ? formatTimestamp(field.value)
                                            : (field.value || 'N/A')
                                        }
                                    </td>
                                </tr>
                            ))}
                        </tbody>
                    </table>

                    {jsonFields.map((field, index) => (
                        <div key={index} style={{ marginBottom: '20px' }}>
                            <h3>{field.label}</h3>
                            <div style={{
                                padding: '15px',
                                background: '#f8f8f8',
                                border: '1px solid #ddd',
                                borderRadius: '3px',
                                fontFamily: 'monospace',
                                fontSize: '12px',
                                overflow: 'auto',
                                maxHeight: '400px'
                            }}>
                                {renderValue(parseJSON(field.value), 0, field.label)}
                            </div>
                        </div>
                    ))}
                </div>

                <button className="button button-primary" onClick={onClose}>
                    Close
                </button>
            </div>
        </div>
    );
};

export default DetailsModal;
