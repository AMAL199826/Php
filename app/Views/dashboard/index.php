<?= $this->include('layout/header') ?>

<div class="d-flex justify-content-between align-items-center mb-4">

    <h2 class="mb-0">
        <i class="bi bi-speedometer2"></i>
        Dashboard
    </h2>

    <a
        href="<?= base_url('dashboard?refresh=1') ?>"
        class="btn btn-outline-primary"
    >
        <i class="bi bi-arrow-clockwise"></i>
        Refresh Data
    </a>

</div>


<!-- =========================================================
     SUMMARY CARDS
========================================================= -->

<div class="row g-4 mb-4">

    <!-- Total -->
    <div class="col-lg-3 col-md-6">

        <div class="card text-white bg-primary h-100 shadow-sm">

            <div class="card-body">

                <div class="d-flex justify-content-between">

                    <div>
                        <h6>Total Customers</h6>

                        <h2 class="fw-bold">
                            <?= $total_customers ?>
                        </h2>
                    </div>

                    <div>
                        <i class="bi bi-people fs-1"></i>
                    </div>

                </div>

                <small>
                    All registered customers
                </small>

            </div>

        </div>

    </div>


    <!-- Active -->
    <div class="col-lg-3 col-md-6">

        <div class="card text-white bg-success h-100 shadow-sm">

            <div class="card-body">

                <div class="d-flex justify-content-between">

                    <div>
                        <h6>Active Customers</h6>

                        <h2 class="fw-bold">
                            <?= $active_customers ?>
                        </h2>
                    </div>

                    <div>
                        <i class="bi bi-check-circle fs-1"></i>
                    </div>

                </div>

                <small>
                    Currently active
                </small>

            </div>

        </div>

    </div>


    <!-- Inactive -->
    <div class="col-lg-3 col-md-6">

        <div class="card text-white bg-danger h-100 shadow-sm">

            <div class="card-body">

                <div class="d-flex justify-content-between">

                    <div>
                        <h6>Inactive Customers</h6>

                        <h2 class="fw-bold">
                            <?= $inactive_customers ?>
                        </h2>
                    </div>

                    <div>
                        <i class="bi bi-x-circle fs-1"></i>
                    </div>

                </div>

                <small>
                    Currently inactive
                </small>

            </div>

        </div>

    </div>


    <!-- New this month -->
    <div class="col-lg-3 col-md-6">

        <div class="card text-white bg-info h-100 shadow-sm">

            <div class="card-body">

                <div class="d-flex justify-content-between">

                    <div>
                        <h6>New This Month</h6>

                        <h2 class="fw-bold">
                            <?= $new_this_month ?>
                        </h2>
                    </div>

                    <div>
                        <i class="bi bi-person-plus fs-1"></i>
                    </div>

                </div>

                <small>
                    Customers added this month
                </small>

            </div>

        </div>

    </div>

</div>



<!-- =========================================================
     CHARTS
========================================================= -->

<div class="row g-4 mb-4">


    <!-- Customer Growth -->
    <div class="col-lg-8">

        <div class="card shadow-sm h-100">

            <div class="card-header bg-white">

                <h5 class="mb-0">
                    <i class="bi bi-graph-up"></i>
                    Customer Growth - Last 6 Months
                </h5>

            </div>

            <div class="card-body">

                <div style="height: 350px;">
                    <canvas id="customerGrowthChart"></canvas>
                </div>

            </div>

        </div>

    </div>


    <!-- Status Distribution -->
    <div class="col-lg-4">

        <div class="card shadow-sm h-100">

            <div class="card-header bg-white">

                <h5 class="mb-0">
                    <i class="bi bi-pie-chart"></i>
                    Status Distribution
                </h5>

            </div>

            <div class="card-body">

                <div style="height: 300px;">
                    <canvas id="statusChart"></canvas>
                </div>

            </div>

        </div>

    </div>

</div>



<!-- =========================================================
     TOP CITIES
========================================================= -->

<div class="row g-4 mb-4">

    <div class="col-lg-6">

        <div class="card shadow-sm">

            <div class="card-header bg-white">

                <h5 class="mb-0">
                    <i class="bi bi-bar-chart"></i>
                    Top 5 Cities
                </h5>

            </div>

            <div class="card-body">

                <div style="height: 320px;">
                    <canvas id="cityChart"></canvas>
                </div>

            </div>

        </div>

    </div>


    <!-- Status Summary -->
    <div class="col-lg-6">

        <div class="card shadow-sm">

            <div class="card-header bg-white">

                <h5 class="mb-0">
                    <i class="bi bi-list-check"></i>
                    Status Summary
                </h5>

            </div>

            <div class="card-body">

                <div class="table-responsive">

                    <table class="table table-bordered">

                        <thead>

                            <tr>
                                <th>Status</th>
                                <th>Customers</th>
                                <th>Percentage</th>
                            </tr>

                        </thead>

                        <tbody>

                            <?php

                            $statusTotal =
                                $status_values[0]
                                + $status_values[1]
                                + $status_values[2];

                            ?>

                            <tr>

                                <td>
                                    <span class="badge bg-success">
                                        Active
                                    </span>
                                </td>

                                <td>
                                    <?= $status_values[0] ?>
                                </td>

                                <td>

                                    <?= $statusTotal > 0
                                        ? round(
                                            ($status_values[0] / $statusTotal) * 100,
                                            1
                                        )
                                        : 0
                                    ?>%

                                </td>

                            </tr>


                            <tr>

                                <td>
                                    <span class="badge bg-danger">
                                        Inactive
                                    </span>
                                </td>

                                <td>
                                    <?= $status_values[1] ?>
                                </td>

                                <td>

                                    <?= $statusTotal > 0
                                        ? round(
                                            ($status_values[1] / $statusTotal) * 100,
                                            1
                                        )
                                        : 0
                                    ?>%

                                </td>

                            </tr>


                            <tr>

                                <td>
                                    <span class="badge bg-warning text-dark">
                                        Pending
                                    </span>
                                </td>

                                <td>
                                    <?= $status_values[2] ?>
                                </td>

                                <td>

                                    <?= $statusTotal > 0
                                        ? round(
                                            ($status_values[2] / $statusTotal) * 100,
                                            1
                                        )
                                        : 0
                                    ?>%

                                </td>

                            </tr>

                        </tbody>

                    </table>

                </div>

            </div>

        </div>

    </div>

</div>



<!-- =========================================================
     RECENT ACTIVITY
========================================================= -->

<div class="card shadow-sm mb-4">

    <div class="card-header bg-white">

        <h5 class="mb-0">

            <i class="bi bi-clock-history"></i>

            Recent Activity

        </h5>

    </div>


    <div class="card-body">

        <?php if (!empty($recent_activities)): ?>

            <div class="table-responsive">

                <table class="table table-hover">

                    <thead>

                        <tr>

                            <th>Customer</th>

                            <th>Action</th>

                            <th>Date & Time</th>

                        </tr>

                    </thead>


                    <tbody>

                        <?php foreach ($recent_activities as $activity): ?>

                            <tr>

                                <td>

                                    <?= esc(
                                        $activity['customer_name']
                                        ?? 'Unknown'
                                    ) ?>

                                </td>


                                <td>

                                    <span class="badge bg-secondary">

                                        <?= esc(
                                            ucfirst(
                                                $activity['action']
                                                ?? 'Activity'
                                            )
                                        ) ?>

                                    </span>

                                </td>


                                <td>

                                    <?= !empty($activity['created_at'])
                                        ? date(
                                            'M d, Y h:i A',
                                            strtotime(
                                                $activity['created_at']
                                            )
                                        )
                                        : '-'
                                    ?>

                                </td>

                            </tr>

                        <?php endforeach; ?>

                    </tbody>

                </table>

            </div>

        <?php else: ?>

            <div class="text-center text-muted py-4">

                <i class="bi bi-info-circle"></i>

                No recent activity found.

            </div>

        <?php endif; ?>

    </div>

</div>



<!-- =========================================================
     RECENT CUSTOMERS
========================================================= -->

<div class="card shadow-sm mb-4">

    <div class="card-header bg-white">

        <h5 class="mb-0">

            <i class="bi bi-people"></i>

            Recent Customers

        </h5>

    </div>


    <div class="card-body">

        <div class="table-responsive">

            <table class="table table-hover">

                <thead>

                    <tr>

                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Company</th>
                        <th>Status</th>
                        <th>Created</th>

                    </tr>

                </thead>


                <tbody>

                    <?php if (!empty($recent_customers)): ?>

                        <?php foreach ($recent_customers as $customer): ?>

                            <tr>

                                <td>
                                    <?= esc($customer['id']) ?>
                                </td>

                                <td>
                                    <?= esc($customer['name']) ?>
                                </td>

                                <td>
                                    <?= esc($customer['email']) ?>
                                </td>

                                <td>
                                    <?= esc($customer['company']) ?>
                                </td>

                                <td>

                                    <?php

                                    $status =
                                        strtolower(
                                            $customer['status']
                                        );

                                    $badgeClass = match ($status) {

                                        'active' =>
                                            'bg-success',

                                        'inactive' =>
                                            'bg-danger',

                                        'pending' =>
                                            'bg-warning text-dark',

                                        default =>
                                            'bg-secondary'
                                    };

                                    ?>

                                    <span class="badge <?= $badgeClass ?>">

                                        <?= esc(
                                            ucfirst($status)
                                        ) ?>

                                    </span>

                                </td>

                                <td>

                                    <?= date(
                                        'M d, Y',
                                        strtotime(
                                            $customer['created_at']
                                        )
                                    ) ?>

                                </td>

                            </tr>

                        <?php endforeach; ?>

                    <?php else: ?>

                        <tr>

                            <td
                                colspan="6"
                                class="text-center text-muted"
                            >
                                No customers found
                            </td>

                        </tr>

                    <?php endif; ?>

                </tbody>

            </table>

        </div>

    </div>

</div>



<!-- =========================================================
     CHART.JS
========================================================= -->

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>


<script>

document.addEventListener('DOMContentLoaded', function () {


    /*
     * ---------------------------------------------------------
     * CUSTOMER GROWTH CHART
     * ---------------------------------------------------------
     */

    const growthCanvas =
        document.getElementById('customerGrowthChart');

    if (growthCanvas) {

        new Chart(growthCanvas, {

            type: 'line',

            data: {

                labels: <?= json_encode($growth_labels) ?>,

                datasets: [{

                    label: 'New Customers',

                    data: <?= json_encode($growth_values) ?>,

                    tension: 0.3,

                    fill: true,

                    borderWidth: 2,

                    pointRadius: 5

                }]

            },

            options: {

                responsive: true,

                maintainAspectRatio: false,

                plugins: {

                    legend: {
                        display: true
                    }

                },

                scales: {

                    y: {

                        beginAtZero: true,

                        ticks: {
                            precision: 0
                        }

                    }

                }

            }

        });

    }



    /*
     * ---------------------------------------------------------
     * STATUS PIE CHART
     * ---------------------------------------------------------
     */

    const statusCanvas =
        document.getElementById('statusChart');

    if (statusCanvas) {

        new Chart(statusCanvas, {

            type: 'pie',

            data: {

                labels: <?= json_encode($status_labels) ?>,

                datasets: [{

                    data: <?= json_encode($status_values) ?>,

                    borderWidth: 1

                }]

            },

            options: {

                responsive: true,

                maintainAspectRatio: false,

                plugins: {

                    legend: {

                        position: 'bottom'

                    }

                }

            }

        });

    }



    /*
     * ---------------------------------------------------------
     * TOP CITIES BAR CHART
     * ---------------------------------------------------------
     */

    const cityCanvas =
        document.getElementById('cityChart');

    if (cityCanvas) {

        new Chart(cityCanvas, {

            type: 'bar',

            data: {

                labels: <?= json_encode($city_labels) ?>,

                datasets: [{

                    label: 'Customers',

                    data: <?= json_encode($city_values) ?>,

                    borderWidth: 1

                }]

            },

            options: {

                responsive: true,

                maintainAspectRatio: false,

                scales: {

                    y: {

                        beginAtZero: true,

                        ticks: {

                            precision: 0

                        }

                    }

                }

            }

        });

    }

});

</script>


<?= $this->include('layout/footer') ?>