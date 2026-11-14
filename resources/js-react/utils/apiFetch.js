/**
 * API fetch utility using WordPress REST API
 */
const apiFetch = async (endpoint, options = {}) => {
  const { apiUrl, nonce } = window.wappointmentData || {};
  
  if (!apiUrl) {
    throw new Error('API URL not configured');
  }

  const queryStart = endpoint.indexOf('?');
  const path = queryStart === -1 ? endpoint : endpoint.slice(0, queryStart);
  const query = queryStart === -1 ? '' : endpoint.slice(queryStart + 1);
  const routeUrl = `${apiUrl}${path}`;
  const url = query ? `${routeUrl}${routeUrl.includes('?') ? '&' : '?'}${query}` : routeUrl;
  const method = options.method || 'GET';
  
  const fetchOptions = {
    method,
    headers: {
      'Content-Type': 'application/json',
      'X-WP-Nonce': nonce
    },
    ...options
  };

  // Remove body for GET requests
  if (method === 'GET' && fetchOptions.body) {
    delete fetchOptions.body;
  }
  
  try {
    const response = await fetch(url, fetchOptions);

    if (!response.ok) {
      const errorData = await response.text();
      let detail = '';
      if (response.headers.get('content-type')?.includes('application/json')) {
        try {
          const parsed = JSON.parse(errorData);
          const message = parsed.message || parsed.data?.error;
          if (typeof message === 'string') detail = message;
        } catch (parseError) {
          if (!(parseError instanceof SyntaxError)) throw parseError;
          detail = 'The server returned a malformed error response.';
        }
      }
      const error = new Error(`HTTP ${response.status}: ${response.statusText}${detail ? ` - ${detail}` : ''}`);
      error.status = response.status;
      error.statusText = response.statusText;
      error.response = errorData;
      error.url = url;
      throw error;
    }

    return await response.json();
  } catch (err) {
    // If it's already our custom error, rethrow it
    if (err.status) throw err;
    
    // Otherwise, wrap the error with more details
    const error = new Error(`Network error: ${err.message}`);
    error.originalError = err;
    error.url = url;
    throw error;
  }
};

export default apiFetch;
