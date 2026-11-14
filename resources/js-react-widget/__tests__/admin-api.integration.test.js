import apiFetch from '../../js-react/utils/apiFetch';

const originalFetch = global.fetch;

afterEach(() => {
    global.fetch = originalFetch;
    delete window.wappointmentData;
});

test.each([
    ['http://127.0.0.1:18089/index.php?rest_route=/wappointment/v1', '/index.php', '/wappointment/v1/clients'],
    ['http://127.0.0.1:18089/?rest_route=/wappointment/v1', '/', '/wappointment/v1/clients'],
    ['http://127.0.0.1:18089/wp-json/wappointment/v1', '/wp-json/wappointment/v1/clients', null],
    ['/wp-json/wappointment/v1', '/wp-json/wappointment/v1/clients', null],
])('client pagination and search work with API base %s', async (apiUrl, pathname, restRoute) => {
    window.wappointmentData = { apiUrl, nonce: 'test-nonce' };
    const result = { success: true, data: { data: [], total: 0 } };
    global.fetch = jest.fn().mockResolvedValue({ ok: true, json: () => Promise.resolve(result) });

    await expect(apiFetch('/clients?page_num=2&per_page=10&search=Jane%20%26%20John%3F')).resolves.toEqual(result);

    const [url, options] = global.fetch.mock.calls[0];
    const parsed = new URL(url, window.location.origin);
    expect(parsed.pathname).toBe(pathname);
    expect(parsed.searchParams.get('rest_route')).toBe(restRoute);
    expect(parsed.searchParams.get('page_num')).toBe('2');
    expect(parsed.searchParams.get('per_page')).toBe('10');
    expect(parsed.searchParams.get('search')).toBe('Jane & John?');
    expect(options.headers['X-WP-Nonce']).toBe('test-nonce');
});

test('plain permalink client mutations keep their route and JSON body', async () => {
    const apiUrl = 'http://127.0.0.1:18089/index.php?rest_route=/wappointment/v1';
    window.wappointmentData = { apiUrl, nonce: 'test-nonce' };
    global.fetch = jest.fn().mockResolvedValue({ ok: true, json: () => Promise.resolve({ success: true }) });
    const options = { method: 'POST', body: JSON.stringify({ name: 'Updated' }) };
    await apiFetch('/clients/7/put', options);
    expect(global.fetch).toHaveBeenCalledWith(`${apiUrl}/clients/7/put`, expect.objectContaining(options));
});
