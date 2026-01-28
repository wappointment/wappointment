import React from 'react';

const SearchBox = ({ value, onChange, onSearch, onClear, placeholder = 'Search...' }) => {
    const handleSubmit = (e) => {
        e.preventDefault();
        onSearch();
    };

    return (
        <div style={{ marginBottom: '20px' }}>
            <form onSubmit={handleSubmit} style={{ display: 'flex', gap: '10px', alignItems: 'center' }}>
                <input
                    type="text"
                    className="regular-text"
                    placeholder={placeholder}
                    value={value}
                    onChange={(e) => onChange(e.target.value)}
                    style={{ maxWidth: '400px' }}
                />
                <button type="submit" className="button button-primary">
                    Search
                </button>
                {value && (
                    <button type="button" className="button" onClick={onClear}>
                        Clear
                    </button>
                )}
            </form>
            {value && (
                <p style={{ marginTop: '10px', color: '#666' }}>
                    Searching for: <strong>{value}</strong>
                </p>
            )}
        </div>
    );
};

export default SearchBox;
