<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\CustomerModel;
use App\Models\ActivityModel;
use App\Models\UserModel;
use App\Services\EmailService;

class Customers extends BaseController
{
    protected $customerModel;
    protected $activityModel;
    protected $userModel;

    protected $emailService;

    public function __construct()
    {
        $this->emailService = new EmailService();
        $this->customerModel = new CustomerModel();
        $this->activityModel = new ActivityModel();
        $this->userModel = new UserModel();
    }

    private function canManage(array $customer): bool
    {
        $role = session()->get('role');

        if ($role === 'admin') {
            return true;
        }

        if (empty($customer['assigned_to'])) {
            return false;
        }

        if ($role === 'sales') {
            return (int) $customer['assigned_to'] === (int) session()->get('user_id');
        }

        if ($role === 'manager') {
            $assignedUser = $this->userModel->find($customer['assigned_to']);
            return $assignedUser
                && $assignedUser['team_id'] !== null
                && (int) $assignedUser['team_id'] === (int) session()->get('team_id');
        }

        return false;
    }

    /**
     * Only admin and manager are allowed to pick/change who a customer is assigned to.
     * Sales users never see this control; their customers stay assigned to them.
     */
    private function canAssign(): bool
    {
        return in_array(session()->get('role'), ['admin', 'manager'], true);
    }

    /**
     * List of users a customer can be assigned to, for the dropdown.
     * Admin sees everyone. Manager sees only their own team + themself.
     */
    private function getAssignableUsers(): array
    {
        if (session()->get('role') === 'admin') {
            return $this->userModel->orderBy('name', 'ASC')->findAll();
        }

        if (session()->get('role') === 'manager') {
            return $this->userModel
                ->where('team_id', session()->get('team_id'))
                ->orderBy('name', 'ASC')
                ->findAll();
        }

        return [];
    }

    public function index()
    {
        $search = $this->request->getGet('search');
        $status = $this->request->getGet('status');
        $city = $this->request->getGet('city');

        $query = $this->customerModel;

        if (session()->get('role') === 'sales') {
            $query = $query->where('assigned_to', session()->get('user_id'));
        }

        if (!empty($search)) {
            $query = $query->groupStart()
                ->like('name', $search)
                ->orLike('email', $search)
                ->orLike('phone', $search)
                ->groupEnd();
        }

        if (!empty($status)) {
            $query = $query->where('status', $status);
        }

        if (!empty($city)) {
            $query = $query->like('city', $city);
        }

        $customers = $query->orderBy('id', 'DESC')->paginate(20);

        foreach ($customers as &$customer) {
            $customer['can_manage'] = $this->canManage($customer);
        }
        unset($customer);

        $data = [
            'customers' => $customers,
            'pager' => $this->customerModel->pager,
            'search' => $search,
            'status' => $status,
            'city' => $city
        ];

        return view('customers/index', $data);
    }

    public function create()
    {
        $data = [
            'canAssign' => $this->canAssign(),
            'assignableUsers' => $this->canAssign() ? $this->getAssignableUsers() : [],
        ];

        return view('customers/create', $data);
    }

    public function store()
    {
        $rules = [
            'name'  => 'required|min_length[2]|max_length[255]',
            'email' => 'required|valid_email',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        // Only admin/manager may pick who it's assigned to; sales users
        // always get customers assigned to themselves.
        if ($this->canAssign() && $this->request->getPost('assigned_to')) {
            $assignedTo = $this->request->getPost('assigned_to');
        } else {
            $assignedTo = session()->get('user_id');
        }

        $data = [
            'name' => $this->request->getPost('name'),
            'email' => $this->request->getPost('email'),
            'phone' => $this->request->getPost('phone'),
            'company' => $this->request->getPost('company'),
            'city' => $this->request->getPost('city'),
            'status' => $this->request->getPost('status') ?? 'active',
            'notes' => $this->request->getPost('notes'),
            'assigned_to' => $assignedTo,
        ];

        if ($this->customerModel->insert($data)) {
            $newId = $this->customerModel->getInsertID();

            $this->activityModel->insert([
                'customer_id' => $newId,
                'action' => 'created',
                'description' => 'Customer created',
                'user_id' => session()->get('user_id')
            ]);

            // Send welcome email. Wrapped so a failure here (bad SMTP creds,
            // network issue, etc.) never breaks the create flow — it just logs.
            try {
                $this->emailService->sendWelcomeEmail($this->customerModel->find($newId));
            } catch (\Throwable $e) {
                log_message('error', 'Welcome email failed for customer ' . $newId . ': ' . $e->getMessage());
            }

            return redirect()->to('/customers')->with('success', 'Customer created successfully');
        }

        return redirect()->back()->withInput()->with('error', 'Failed to create customer');
    }

    public function edit($id)
    {
        $customer = $this->customerModel->find($id);

        if (!$customer) {
            return redirect()->to('/customers')->with('error', 'Customer not found');
        }

        if (!$this->canManage($customer)) {
            return redirect()->to('/access-denied');
        }

        $data = [
            'customer' => $customer,
            'canAssign' => $this->canAssign(),
            'assignableUsers' => $this->canAssign() ? $this->getAssignableUsers() : [],
        ];

        return view('customers/edit', $data);
    }

    public function update($id)
    {
        $customer = $this->customerModel->find($id);

        if (!$customer) {
            return redirect()->to('/customers')->with('error', 'Customer not found');
        }

        if (!$this->canManage($customer)) {
            return redirect()->to('/access-denied');
        }

        $rules = [
            'name'  => 'required|min_length[2]|max_length[255]',
            'email' => 'required|valid_email',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = [
            'name' => $this->request->getPost('name'),
            'email' => $this->request->getPost('email'),
            'phone' => $this->request->getPost('phone'),
            'company' => $this->request->getPost('company'),
            'city' => $this->request->getPost('city'),
            'status' => $this->request->getPost('status'),
            'notes' => $this->request->getPost('notes')
        ];

        // Only admin/manager may re-assign; sales can't change ownership
        if ($this->canAssign() && $this->request->getPost('assigned_to')) {
            $data['assigned_to'] = $this->request->getPost('assigned_to');
        }

        if ($this->customerModel->update($id, $data)) {
            $this->activityModel->insert([
                'customer_id' => $id,
                'action' => 'updated',
                'description' => 'Customer information updated',
                'user_id' => session()->get('user_id')
            ]);

            return redirect()->to('/customers')->with('success', 'Customer updated successfully');
        }

        return redirect()->back()->withInput()->with('error', 'Failed to update customer');
    }

    public function delete($id)
    {
        $customer = $this->customerModel->find($id);

        if (!$customer) {
            return redirect()->to('/customers')->with('error', 'Customer not found');
        }

        $this->activityModel->insert([
            'customer_id' => $id,
            'action' => 'deleted',
            'description' => 'Customer deleted',
            'user_id' => session()->get('user_id')
        ]);

        $this->customerModel->delete($id);

        return redirect()->to('/customers')->with('success', 'Customer deleted successfully');
    }

    public function view($id)
    {
        $customer = $this->customerModel->find($id);

        if (!$customer) {
            return redirect()->to('/customers')->with('error', 'Customer not found');
        }

        if (session()->get('role') === 'sales' && !$this->canManage($customer)) {
            return redirect()->to('/access-denied');
        }

        $customer['can_manage'] = $this->canManage($customer);

        $activities = $this->activityModel
            ->where('customer_id', $id)
            ->orderBy('created_at', 'DESC')
            ->limit(20)
            ->find();

        $data = [
            'customer' => $customer,
            'activities' => $activities
        ];

        return view('customers/view', $data);
    }

    public function export()
    {
        $query = $this->customerModel;

        if (session()->get('role') === 'sales') {
            $query = $query->where('assigned_to', session()->get('user_id'));
        }

        $customers = $query->findAll();

        $filename = 'customers_' . date('Y-m-d') . '.csv';
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        $output = fopen('php://output', 'w');

        fputcsv($output, ['ID', 'Name', 'Email', 'Phone', 'Company', 'City', 'Status']);

        foreach ($customers as $customer) {
            fputcsv($output, [
                $customer['id'],
                $customer['name'],
                $customer['email'],
                $customer['phone'],
                $customer['company'],
                $customer['city'],
                $customer['status'],
            ]);
        }

        fclose($output);
        exit;
    }
}