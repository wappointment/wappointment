import React, { useState } from 'react';

const ErrorDetails = ({ error }) => {
    const [showErrorDetails, setShowErrorDetails] = useState(false);
    const [expandedSections, setExpandedSections] = useState({});

    const toggleSection = (section) => {
        setExpandedSections(prev => ({
            ...prev,
            [section]: !prev[section]
        }));
    };

    const parseResponse = (response) => {
        if (!response) return null;
        
        try {
            return JSON.parse(response);
        } catch {
            return response;
        }
    };

    const renderValue = (value, depth = 0) => {
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
                            <span style={{ color: '#999' }}>[{index}]:</span> {renderValue(item, depth + 1)}
                        </div>
                    ))}
                </div>
            );
        }
        
        if (typeof value === 'object') {
            const keys = Object.keys(value);
            if (keys.length === 0) return <span>{{}}</span>;
            
            return (
                <div style={{ marginLeft: depth > 0 ? '20px' : '0' }}>
                    {keys.map(key => (
                        <div key={key} style={{ marginBottom: '5px' }}>
                            <a
                                href="#"
                                onClick={(e) => {
                                    e.preventDefault();
                                    toggleSection(`${depth}-${key}`);
                                }}
                                style={{ 
                                    color: '#0073aa',
                                    textDecoration: 'none',
                                    fontWeight: 'bold'
                                }}
                            >
                                {expandedSections[`${depth}-${key}`] ? '▼' : '▶'} {key}:
                            </a>
                            {expandedSections[`${depth}-${key}`] && (
                                <div style={{ marginLeft: '20px', marginTop: '5px' }}>
                                    {renderValue(value[key], depth + 1)}
                                </div>
                            )}
                        </div>
                    ))}
                </div>
            );
        }
        
        return <span>{String(value)}</span>;
    };

    if (!error) return null;

    return (
        <div className="notice notice-error">
            <p><strong>Error:</strong> {error.message}</p>
            {(error.status || error.response || error.url) && (
                <p>
                    <a 
                        href="#" 
                        onClick={(e) => {
                            e.preventDefault();
                            setShowErrorDetails(!showErrorDetails);
                        }}
                        style={{ textDecoration: 'underline' }}
                    >
                        {showErrorDetails ? 'Hide' : 'Show'} error details
                    </a>
                </p>
            )}
            {showErrorDetails && (
                <div style={{ 
                    marginTop: '10px', 
                    padding: '10px', 
                    background: '#f8f8f8',
                    border: '1px solid #ddd',
                    fontFamily: 'monospace',
                    fontSize: '12px',
                    overflow: 'auto',
                    maxHeight: '400px'
                }}>
                    {error.status && (
                        <div style={{ marginBottom: '10px' }}>
                            <strong>Status:</strong> {error.status} {error.statusText}
                        </div>
                    )}
                    {error.url && (
                        <div style={{ marginBottom: '10px' }}>
                            <strong>URL:</strong> {error.url}
                        </div>
                    )}
                    {error.response && (
                        <div style={{ marginBottom: '10px' }}>
                            <div style={{ marginBottom: '5px' }}>
                                <strong>Response:</strong>
                            </div>
                            <div style={{ 
                                padding: '10px',
                                background: '#fff',
                                border: '1px solid #ccc',
                                borderRadius: '3px'
                            }}>
                                {renderValue(parseResponse(error.response))}
                            </div>
                        </div>
                    )}
                    {error.originalError && (
                        <div style={{ marginBottom: '10px' }}>
                            <strong>Original Error:</strong> {error.originalError.toString()}
                        </div>
                    )}
                    {error.stack && (
                        <div>
                            <a
                                href="#"
                                onClick={(e) => {
                                    e.preventDefault();
                                    toggleSection('stack');
                                }}
                                style={{ 
                                    color: '#0073aa',
                                    textDecoration: 'none',
                                    fontWeight: 'bold'
                                }}
                            >
                                {expandedSections.stack ? '▼' : '▶'} Stack Trace
                            </a>
                            {expandedSections.stack && (
                                <pre style={{ 
                                    whiteSpace: 'pre-wrap', 
                                    fontSize: '11px',
                                    marginTop: '5px',
                                    marginLeft: '20px'
                                }}>
                                    {error.stack}
                                </pre>
                            )}
                        </div>
                    )}
                </div>
            )}
        </div>
    );
};

export default ErrorDetails;
