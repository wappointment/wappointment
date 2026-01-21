import React, { useEffect } from 'react';

function Notification({ message, type = 'success', onClose, duration = 3000 }) {
    useEffect(() => {
        if (duration > 0) {
            const timer = setTimeout(() => {
                onClose();
            }, duration);
            
            return () => clearTimeout(timer);
        }
    }, [duration, onClose]);

    const getTypeStyles = () => {
        switch (type) {
            case 'success':
                return {
                    backgroundColor: '#d4edda',
                    color: '#155724',
                    borderColor: '#c3e6cb'
                };
            case 'error':
                return {
                    backgroundColor: '#f8d7da',
                    color: '#721c24',
                    borderColor: '#f5c6cb'
                };
            case 'warning':
                return {
                    backgroundColor: '#fff3cd',
                    color: '#856404',
                    borderColor: '#ffeeba'
                };
            case 'info':
                return {
                    backgroundColor: '#d1ecf1',
                    color: '#0c5460',
                    borderColor: '#bee5eb'
                };
            default:
                return {
                    backgroundColor: '#d4edda',
                    color: '#155724',
                    borderColor: '#c3e6cb'
                };
        }
    };

    const typeStyles = getTypeStyles();

    return (
        <div style={{
            position: 'fixed',
            top: '20px',
            right: '20px',
            zIndex: 10001,
            minWidth: '300px',
            maxWidth: '500px',
            padding: '15px 20px',
            borderRadius: '4px',
            border: '1px solid',
            boxShadow: '0 2px 8px rgba(0,0,0,0.1)',
            display: 'flex',
            alignItems: 'center',
            justifyContent: 'space-between',
            ...typeStyles,
            animation: 'slideIn 0.3s ease-out'
        }}>
            <div style={{ flex: 1, marginRight: '10px' }}>
                {message}
            </div>
            <button
                onClick={onClose}
                style={{
                    background: 'none',
                    border: 'none',
                    fontSize: '20px',
                    cursor: 'pointer',
                    color: 'inherit',
                    padding: '0',
                    lineHeight: '1',
                    opacity: 0.7
                }}
                onMouseEnter={(e) => e.target.style.opacity = 1}
                onMouseLeave={(e) => e.target.style.opacity = 0.7}
            >
                ×
            </button>
            <style>{`
                @keyframes slideIn {
                    from {
                        transform: translateX(100%);
                        opacity: 0;
                    }
                    to {
                        transform: translateX(0);
                        opacity: 1;
                    }
                }
            `}</style>
        </div>
    );
}

export default Notification;
