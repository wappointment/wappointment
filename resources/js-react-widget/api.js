export default async function bookingFetch(path, data, signal) {
    const config = window.wappointmentWidget;
    if (!config?.apiUrl) {
        throw new Error('Booking API is not configured.');
    }
    const headers = { 'Content-Type': 'application/json' };
    if (config.nonce) headers['X-WP-Nonce'] = config.nonce;
    const response = await fetch(`${config.apiUrl}${path}`, {
        method: data === undefined ? 'GET' : 'POST',
        credentials: 'same-origin',
        headers,
        ...(data === undefined ? {} : { body: JSON.stringify(data) }),
        signal,
    });
    const result = await response.json();
    if (!response.ok || result.success !== true) {
        throw new Error(result.message || result.data?.error || 'The booking request failed.');
    }
    return result.data;
}
