/**
 * API fetch utility using WordPress REST API
 */
const apiFetch = async (endpoint, options = {}) => {
  const { apiUrl, nonce } = window.wappointmentData || {};
  
  if (!apiUrl) {
    throw new Error('API URL not configured');
  }

  const url = `${apiUrl}${endpoint}`;
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
      const error = new Error(`HTTP ${response.status}: ${response.statusText}`);
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
