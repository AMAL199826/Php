<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\CustomerModel;

class Dashboard extends BaseController
{
    public function index()
    {
        $cache = cache();
        $cacheKey = 'dashboard_analytics';

        // Refresh dashboard manually
        if ($this->request->getGet('refresh') == '1') {
            $cache->delete($cacheKey);
        }

        // Check cache
        $data = $cache->get($cacheKey);

        if ($data === null) {

            $customerModel = new CustomerModel();

            /*
             * ---------------------------------------------------------
             * SUMMARY CARDS
             * ---------------------------------------------------------
             */

            // Total customers
            $totalCustomers = (new CustomerModel())->countAllResults();

            // Active customers
            $activeCustomers = (new CustomerModel())
                ->where('status', 'active')
                ->countAllResults();

            // Inactive customers
            $inactiveCustomers = (new CustomerModel())
                ->where('status', 'inactive')
                ->countAllResults();

            // Pending customers
            $pendingCustomers = (new CustomerModel())
                ->where('status', 'pending')
                ->countAllResults();

            // New customers this month
            $startOfMonth = date('Y-m-01 00:00:00');
            $endOfMonth   = date('Y-m-t 23:59:59');

            $newThisMonth = (new CustomerModel())
                ->where('created_at >=', $startOfMonth)
                ->where('created_at <=', $endOfMonth)
                ->countAllResults();


            /*
             * ---------------------------------------------------------
             * CUSTOMER GROWTH - LAST 6 MONTHS
             * ---------------------------------------------------------
             */

            $growthQuery = $customerModel
                ->select("
                    DATE_FORMAT(created_at, '%Y-%m') AS month,
                    COUNT(*) AS total
                ")
                ->where(
                    'created_at >=',
                    date('Y-m-01', strtotime('-5 months'))
                )
                ->groupBy("DATE_FORMAT(created_at, '%Y-%m')")
                ->orderBy('month', 'ASC')
                ->get()
                ->getResultArray();

            $growthData = [];

            foreach ($growthQuery as $row) {
                $growthData[$row['month']] = (int) $row['total'];
            }

            $growthLabels = [];
            $growthValues = [];

            for ($i = 5; $i >= 0; $i--) {

                $monthDate = strtotime("-{$i} months");

                $monthKey = date('Y-m', $monthDate);

                $growthLabels[] = date('M Y', $monthDate);

                $growthValues[] = $growthData[$monthKey] ?? 0;
            }


            /*
             * ---------------------------------------------------------
             * STATUS DISTRIBUTION
             * ---------------------------------------------------------
             */

            $statusQuery = $customerModel
                ->select('status, COUNT(*) AS total')
                ->groupBy('status')
                ->get()
                ->getResultArray();

            $statusData = [
                'active'   => 0,
                'inactive' => 0,
                'pending'  => 0
            ];

            foreach ($statusQuery as $row) {

                $status = strtolower(trim($row['status']));

                if (isset($statusData[$status])) {
                    $statusData[$status] = (int) $row['total'];
                }
            }


            /*
             * ---------------------------------------------------------
             * TOP 5 CITIES
             * ---------------------------------------------------------
             */

            $cityQuery = $customerModel
                ->select('city, COUNT(*) AS total')
                ->where('city IS NOT NULL')
                ->where('city !=', '')
                ->groupBy('city')
                ->orderBy('total', 'DESC')
                ->limit(5)
                ->get()
                ->getResultArray();

            $cityLabels = [];
            $cityValues = [];

            foreach ($cityQuery as $row) {

                $cityLabels[] = $row['city'];
                $cityValues[] = (int) $row['total'];
            }


            /*
             * ---------------------------------------------------------
             * RECENT CUSTOMERS
             * ---------------------------------------------------------
             */

            $recentCustomers = (new CustomerModel())
                ->orderBy('created_at', 'DESC')
                ->limit(10)
                ->find();


            /*
             * ---------------------------------------------------------
             * RECENT ACTIVITY
             * ---------------------------------------------------------
             *
             * Assuming activity table is:
             * activity_logs
             *
             * If your actual table has another name, change it below.
             */

            $recentActivities = [];

            try {

                $db = \Config\Database::connect();

                $recentActivities = $db->table('activity_logs')
                    ->select('
                        activity_logs.*,
                        customers.name AS customer_name
                    ')
                    ->join(
                        'customers',
                        'customers.id = activity_logs.customer_id',
                        'left'
                    )
                    ->orderBy('activity_logs.created_at', 'DESC')
                    ->limit(10)
                    ->get()
                    ->getResultArray();

            } catch (\Throwable $e) {

                // Don't break dashboard if activity table is unavailable
                log_message(
                    'error',
                    'Dashboard activity error: ' . $e->getMessage()
                );

                $recentActivities = [];
            }


            /*
             * ---------------------------------------------------------
             * PREPARE DATA
             * ---------------------------------------------------------
             */

            $data = [

                // Cards
                'total_customers'    => $totalCustomers,
                'active_customers'   => $activeCustomers,
                'inactive_customers' => $inactiveCustomers,
                'pending_customers'  => $pendingCustomers,
                'new_this_month'     => $newThisMonth,

                // Growth chart
                'growth_labels'      => $growthLabels,
                'growth_values'      => $growthValues,

                // Status chart
                'status_labels'      => ['Active', 'Inactive', 'Pending'],
                'status_values'      => [
                    $statusData['active'],
                    $statusData['inactive'],
                    $statusData['pending']
                ],

                // City chart
                'city_labels'        => $cityLabels,
                'city_values'        => $cityValues,

                // Tables
                'recent_customers'   => $recentCustomers,
                'recent_activities'  => $recentActivities
            ];


            /*
             * ---------------------------------------------------------
             * CACHE FOR 1 HOUR
             * ---------------------------------------------------------
             */

            $cache->save(
                $cacheKey,
                $data,
                3600
            );
        }

        return view('dashboard/index', $data);
    }
}