<?= $this->include('layout/header') ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="bi bi-people"></i> Customers</h2>
    <div>
        <a href="<?= base_url('customers/export') ?>" class="btn btn-success">
            <i class="bi bi-download"></i> Export CSV
        </a>
        <a href="<?= base_url('customers/create') ?>" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> Add Customer
        </a>
    </div>
</div>

<!-- Search & Filter Form -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="<?= base_url('customers') ?>" class="row g-3">
            <div class="col-md-4">
                <input type="text" name="search" class="form-control" placeholder="Search name or email..." value="<?= esc($search ?? '') ?>">
            </div>
            <div class="col-md-3">
                <select name="status" class="form-select">
                    <option value="">All Status</option>
                    <option value="active" <?= isset($status) && $status == 'active' ? 'selected' : '' ?>>Active</option>
                    <option value="inactive" <?= isset($status) && $status == 'inactive' ? 'selected' : '' ?>>Inactive</option>
                    <option value="pending" <?= isset($status) && $status == 'pending' ? 'selected' : '' ?>>Pending</option>
                </select>
            </div>
            <div class="col-md-3">
                <input type="text" name="city" class="form-control" placeholder="Filter by city..." value="<?= esc($city ?? '') ?>">
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="bi bi-search"></i> Filter
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Customers Table -->
<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Company</th>
                        <th>City</th>
                        <th>Status</th>
                        <th>Recent Activities</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($customers)): ?>
                        <?php foreach ($customers as $customer): ?>
                        <tr>
                            <td><?= esc($customer['id']) ?></td>
                            <td>
                                <a href="<?= base_url('customers/view/' . $customer['id']) ?>">
                                    <?= esc($customer['name']) ?>
                                </a>
                            </td>
                            <td><?= esc($customer['email']) ?></td>
                            <td><?= esc($customer['phone']) ?></td>
                            <td><?= esc($customer['company']) ?></td>
                            <td><?= esc($customer['city']) ?></td>
                            <td>
                                <span class="status-badge status-<?= esc($customer['status']) ?>">
                                    <?= esc($customer['status']) ?>
                                </span>
                            </td>
                            <td>
                                <?php if (!empty($customer['activities'])): ?>
                                    <small class="text-muted">
                                        <?php foreach (array_slice($customer['activities'], 0, 2) as $activity): ?>
                                            <div><?= esc($activity['action']) ?>: <?= esc($activity['description']) ?></div>
                                        <?php endforeach; ?>
                                    </small>
                                <?php else: ?>
                                    <small class="text-muted">No activities</small>
                                <?php endif; ?>
                            </td>
                            <td>
                                <a href="<?= base_url('customers/view/' . $customer['id']) ?>" class="btn btn-sm btn-info" title="View">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <?php if (!empty($customer['can_manage'])): ?>
                                    <a href="<?= base_url('customers/edit/' . $customer['id']) ?>" class="btn btn-sm btn-warning" title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                <?php endif; ?>
                                <?php if (session()->get('role') === 'admin'): ?>
                                    <a href="<?= base_url('customers/delete/' . $customer['id']) ?>" class="btn btn-sm btn-danger" title="Delete" onclick="return confirm('Are you sure?')">
                                        <i class="bi bi-trash"></i>
                                    </a>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="9" class="text-center text-muted">No customers found</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <?php if ($pager): ?>
            <div class="mt-3">
                <?= $pager->links() ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<?= $this->include('layout/footer') ?>