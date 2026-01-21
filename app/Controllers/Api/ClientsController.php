<?php
declare(strict_types=1);

namespace Wappointment\Controllers\Api;

use Wappointment\Models\Client;

class ClientsController extends BaseApiController
{
    public function __construct(
        private Client $model
    ) {}

    public function __invoke(\WP_REST_Request $request): void
    {
        $page = isset($_GET['page_num']) ? (int) $_GET['page_num'] : 1;
        $perPage = isset($_GET['per_page']) ? (int) $_GET['per_page'] : 10;
        $search = isset($_GET['search']) ? sanitize_text_field($_GET['search']) : '';
        
        if (!empty($search)) {
            $result = $this->model->search($search, $page, $perPage);
        } else {
            $result = $this->model->paginate($page, $perPage);
        }
        
        $this->sendJson($result);
    }
    
    public function create(\WP_REST_Request $request): void
    {
        $data = $request->get_json_params();
        
        if (empty($data['email'])) {
            $this->sendJson(['error' => 'Email is required'], 400);
            return;
        }
        
        $clientData = [
            'name' => $data['name'] ?? '',
            'email' => sanitize_email($data['email']),
            'options' => !empty($data['options']) ? json_encode($data['options']) : json_encode([]),
            'created_at' => current_time('mysql'),
            'updated_at' => current_time('mysql'),
        ];
        
        $id = $this->model->create($clientData);
        
        if ($id) {
            $client = $this->model->find($id);
            $this->sendJson(['success' => true, 'data' => $client], 201);
        } else {
            $this->sendJson(['error' => 'Failed to create client'], 500);
        }
    }
    
    public function update(\WP_REST_Request $request): void
    {
        $id = (int) $request->get_param('id');
        $data = $request->get_json_params();
        
        $client = $this->model->find($id);
        if (!$client) {
            $this->sendJson(['error' => 'Client not found'], 404);
            return;
        }
        
        $clientData = [];
        
        if (isset($data['name'])) {
            $clientData['name'] = $data['name'];
        }
        
        if (isset($data['email'])) {
            $clientData['email'] = sanitize_email($data['email']);
        }
        
        if (isset($data['options'])) {
            $clientData['options'] = json_encode($data['options']);
        }
        
        $clientData['updated_at'] = current_time('mysql');
        
        $updated = $this->model->update($id, $clientData);
        
        if ($updated !== false) {
            $client = $this->model->find($id);
            $this->sendJson(['success' => true, 'data' => $client]);
        } else {
            $this->sendJson(['error' => 'Failed to update client'], 500);
        }
    }
    
    public function delete(\WP_REST_Request $request): void
    {
        $id = (int) $request->get_param('id');
        
        $client = $this->model->find($id);
        if (!$client) {
            $this->sendJson(['error' => 'Client not found'], 404);
            return;
        }
        
        $deleted = $this->model->delete($id);
        
        if ($deleted) {
            $this->sendJson(['success' => true, 'message' => 'Client deleted']);
        } else {
            $this->sendJson(['error' => 'Failed to delete client'], 500);
        }
    }
}
