import { http, HttpResponse } from 'msw';

const API_BASE = 'http://localhost:8080/wp-json/wappointment/v1';

// Mock data store
let clients = [
  {
    id: '1',
    name: 'John Doe',
    email: 'john@example.com',
    options: '{"source":"manual"}',
    created_at: '2026-01-01 10:00:00',
    updated_at: '2026-01-01 10:00:00',
    deleted_at: null,
  },
  {
    id: '2',
    name: 'Jane Smith',
    email: 'jane@example.com',
    options: '{"source":"booking"}',
    created_at: '2026-01-02 11:00:00',
    updated_at: '2026-01-02 11:00:00',
    deleted_at: null,
  },
];

let nextId = 3;

export const handlers = [
  // List clients
  http.get(`${API_BASE}/clients`, ({ request }) => {
    const url = new URL(request.url);
    const search = url.searchParams.get('search');
    const page = parseInt(url.searchParams.get('page') || '1');
    const perPage = parseInt(url.searchParams.get('per_page') || '10');

    let filteredClients = clients.filter(c => !c.deleted_at);

    if (search) {
      filteredClients = filteredClients.filter(
        c => c.name.toLowerCase().includes(search.toLowerCase()) ||
             c.email.toLowerCase().includes(search.toLowerCase())
      );
    }

    const start = (page - 1) * perPage;
    const end = start + perPage;
    const paginatedClients = filteredClients.slice(start, end);

    return HttpResponse.json({
      success: true,
      data: {
        data: paginatedClients,
        total: filteredClients.length,
        page,
        per_page: perPage,
        total_pages: Math.ceil(filteredClients.length / perPage),
      },
    });
  }),

  // Create client
  http.post(`${API_BASE}/clients`, async ({ request }) => {
    const body = await request.json();

    if (!body.email || !body.email.includes('@')) {
      return HttpResponse.json(
        {
          success: false,
          message: 'Invalid email address',
        },
        { status: 400 }
      );
    }

    const newClient = {
      id: String(nextId++),
      name: body.name || '',
      email: body.email,
      options: JSON.stringify(body.options || {}),
      created_at: new Date().toISOString().slice(0, 19).replace('T', ' '),
      updated_at: new Date().toISOString().slice(0, 19).replace('T', ' '),
      deleted_at: null,
    };

    clients.push(newClient);

    return HttpResponse.json({
      success: true,
      data: newClient,
    });
  }),

  // Update client
  http.post(`${API_BASE}/clients/:id/put`, async ({ params, request }) => {
    const body = await request.json();
    const clientIndex = clients.findIndex(c => c.id === params.id);

    if (clientIndex === -1) {
      return HttpResponse.json(
        {
          success: false,
          message: 'Client not found',
        },
        { status: 404 }
      );
    }

    clients[clientIndex] = {
      ...clients[clientIndex],
      ...body,
      updated_at: new Date().toISOString().slice(0, 19).replace('T', ' '),
    };

    return HttpResponse.json({
      success: true,
      data: clients[clientIndex],
    });
  }),

  // Delete client
  http.post(`${API_BASE}/clients/:id/delete`, ({ params }) => {
    const clientIndex = clients.findIndex(c => c.id === params.id);

    if (clientIndex === -1) {
      return HttpResponse.json(
        {
          success: false,
          message: 'Client not found',
        },
        { status: 404 }
      );
    }

    clients[clientIndex].deleted_at = new Date().toISOString().slice(0, 19).replace('T', ' ');

    return HttpResponse.json({
      success: true,
      data: { id: params.id },
    });
  }),
];

// Helper to reset mock data
export const resetClients = () => {
  clients = [
    {
      id: '1',
      name: 'John Doe',
      email: 'john@example.com',
      options: '{"source":"manual"}',
      created_at: '2026-01-01 10:00:00',
      updated_at: '2026-01-01 10:00:00',
      deleted_at: null,
    },
    {
      id: '2',
      name: 'Jane Smith',
      email: 'jane@example.com',
      options: '{"source":"booking"}',
      created_at: '2026-01-02 11:00:00',
      updated_at: '2026-01-02 11:00:00',
      deleted_at: null,
    },
  ];
  nextId = 3;
};
