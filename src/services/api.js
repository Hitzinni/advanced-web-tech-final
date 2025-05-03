const BASE = import.meta.env.VITE_API_BASE_URL;

/**
 * request – low-level wrapper around fetch
 * @param {string} path – API path (e.g. '/login')
 * @param {object} opts – method, body, headers
 */
async function request(path, { method = 'GET', body, headers = {} } = {}) {
  const options = {
    method,
    credentials: 'include',
    headers: { 'Content-Type': 'application/json', ...headers }
  };
  if (body) options.body = JSON.stringify(body);

  const res = await fetch(`${BASE}${path}`, options);
  const data = await res.json();
  if (!res.ok) throw new Error(data.error || 'API error');
  return data;
}

export default {
  get:    (path) => request(path, { method: 'GET' }),
  post:   (path, body) => request(path, { method: 'POST', body }),
  put:    (path, body) => request(path, { method: 'PUT', body }),
  delete: (path) => request(path, { method: 'DELETE' })
};
