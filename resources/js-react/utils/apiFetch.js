/**
 * API fetch utility using WordPress REST API
 */
const apiFetch = async (endpoint) => {
  const { apiUrl, nonce } = window.wappointmentData || {};
  
  if (!apiUrl) {
    throw new Error('API URL not configured');
  }

  const url = `${apiUrl}${endpoint}`;
  
  const response = await fetch(url, {
    method: 'GET',
    headers: {
      'Content-Type': 'application/json',
      'X-WP-Nonce': nonce
    }
  });

  if (!response.ok) {
    throw new Error(`HTTP error! status: ${response.status}`);
  }

  return await response.json();
};

export default apiFetch;
