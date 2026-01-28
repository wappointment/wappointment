import { useState } from 'react';

export function useNotification() {
    const [notification, setNotification] = useState(null);

    const showNotification = (message, type = 'success') => {
        setNotification({ message, type });
    };

    const hideNotification = () => {
        setNotification(null);
    };

    return {
        notification,
        showNotification,
        hideNotification,
    };
}
