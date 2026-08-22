<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Models\CustomerModel;
use App\Models\ActivityModel;
use App\Libraries\ApiAuth;

class CustomersController extends BaseController
{
    protected $customerModel;
    protected $activityModel;

    public function __construct()
    {
        $this->customerModel = new CustomerModel();
        $this->activityModel = new ActivityModel();
    }

    // GET /api/customers
    public function index()
    {
        $user = ApiAuth::user();

        $page = max(1, (int) ($this->request->getGet('page') ?? 1));
        $perPage = max(1, min(100, (int) ($this->request->getGet('per_page') ?? 20)));

        $status = $this->request->getGet('status');
        $city = $this->request->getGet('city');

        $sort = $this->request->getGet('sort') ?? 'id';
        $allowedSort = ['id', 'name', 'email', 'city', 'status', 'created_at'];
        if (!in_array($sort, $allowedSort, true)) {
            $sort = 'id';
        }

        $order = strtolower($this->request->getGet('order') ?? 'desc');
        $order = $order === 'asc' ? 'asc' : 'desc';

        $query = $this->customerModel;

        // Sales API users only see their own customers
        if ($user['role'] === 'sales') {
            $query = $query->where('assigned_to', $user['sub']);
        }

        if (!empty($status)) {
            $query = $query->where('status', $status);
        }

        if (!empty($city)) {
            $query = $query->like('city', $city);
        }

        $total = $query->countAllResults(false);
        $customers = $query->orderBy($sort, $order)->paginate($perPage, 'default', $page);

        return $this->response->setStatusCode(200)->setJSON([
            'status' => 200,
            'data' => $customers,
            'pagination' => [
                'page' => $page,
                'per_page' => $perPage,
                'total' => $total,
                'total_pages' => (int) ceil($total / $perPage),
            ],
        ]);
    }

    // GET /api/customers/{id}
    public function show($id = null)
    {
        $customer = $this->customerModel->find($id);

        if (!$customer) {
            return $this->response->setStatusCode(404)->setJSON([
                'status' => 404,
                'message' => 'Customer not found',
            ]);
        }

        $user = ApiAuth::user();
        if ($user['role'] === 'sales' && (int) $customer['assigned_to'] !== (int) $user['sub']) {
            return $this->response->setStatusCode(403)->setJSON([
                'status' => 403,
                'message' => 'You do not have permission to view this customer',
            ]);
        }

        return $this->response->setStatusCode(200)->setJSON([
            'status' => 200,
            'data' => $customer,
        ]);
    }

    // POST /api/customers
    public function create()
    {
        $data = $this->request->getJSON(true);

        if (empty($data['name']) || empty($data['email'])) {
            return $this->response->setStatusCode(400)->setJSON([
                'status' => 400,
                'message' => 'name and email are required',
            ]);
        }

        $user = ApiAuth::user();

        $insertData = [
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'company' => $data['company'] ?? null,
            'city' => $data['city'] ?? null,
            'status' => $data['status'] ?? 'active',
            'notes' => $data['notes'] ?? null,
            'assigned_to' => $data['assigned_to'] ?? $user['sub'],
        ];

        if (!$this->customerModel->insert($insertData)) {
            return $this->response->setStatusCode(400)->setJSON([
                'status' => 400,
                'message' => 'Validation failed',
                'errors' => $this->customerModel->errors(),
            ]);
        }

        $id = $this->customerModel->getInsertID();

        $this->activityModel->insert([
            'customer_id' => $id,
            'action' => 'created',
            'description' => 'Customer created via API',
            'user_id' => $user['sub'],
        ]);

        return $this->response->setStatusCode(201)->setJSON([
            'status' => 201,
            'message' => 'Customer created',
            'data' => $this->customerModel->find($id),
        ]);
    }

    // PUT /api/customers/{id}
    public function update($id = null)
    {
        $customer = $this->customerModel->find($id);

        if (!$customer) {
            return $this->response->setStatusCode(404)->setJSON([
                'status' => 404,
                'message' => 'Customer not found',
            ]);
        }

        $user = ApiAuth::user();
        if ($user['role'] === 'sales' && (int) $customer['assigned_to'] !== (int) $user['sub']) {
            return $this->response->setStatusCode(403)->setJSON([
                'status' => 403,
                'message' => 'You do not have permission to update this customer',
            ]);
        }

        $data = $this->request->getJSON(true) ?? [];

        $allowed = ['name', 'email', 'phone', 'company', 'city', 'status', 'notes', 'assigned_to'];
        $updateData = [];
        foreach ($allowed as $field) {
            if (array_key_exists($field, $data)) {
                $updateData[$field] = $data[$field];
            }
        }

        if (empty($updateData)) {
            return $this->response->setStatusCode(400)->setJSON([
                'status' => 400,
                'message' => 'No valid fields provided to update',
            ]);
        }

        $this->customerModel->update($id, $updateData);

        $this->activityModel->insert([
            'customer_id' => $id,
            'action' => 'updated',
            'description' => 'Customer updated via API',
            'user_id' => $user['sub'],
        ]);

        return $this->response->setStatusCode(200)->setJSON([
            'status' => 200,
            'message' => 'Customer updated',
            'data' => $this->customerModel->find($id),
        ]);
    }

    // DELETE /api/customers/{id}
    public function delete($id = null)
    {
        $customer = $this->customerModel->find($id);

        if (!$customer) {
            return $this->response->setStatusCode(404)->setJSON([
                'status' => 404,
                'message' => 'Customer not found',
            ]);
        }

        $user = ApiAuth::user();
        if ($user['role'] !== 'admin') {
            return $this->response->setStatusCode(403)->setJSON([
                'status' => 403,
                'message' => 'Only admin can delete customers',
            ]);
        }

        $this->activityModel->insert([
            'customer_id' => $id,
            'action' => 'deleted',
            'description' => 'Customer deleted via API',
            'user_id' => $user['sub'],
        ]);

        $this->customerModel->delete($id);

        return $this->response->setStatusCode(200)->setJSON([
            'status' => 200,
            'message' => 'Customer deleted',
        ]);
    }
}