<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddAssignedToToCustomers extends Migration
{
    public function up()
    {
        $this->forge->addColumn('customers', [
            'assigned_to' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
                'after'      => 'status',
            ],
        ]);

        // Nullable + SET NULL so deleting a user doesn't delete their customers
        $this->forge->addForeignKey('assigned_to', 'users', 'id', 'SET NULL', 'CASCADE');
        $this->forge->processIndexes('customers');
    }

    public function down()
    {
        $this->db->disableForeignKeyChecks();
        $this->forge->dropForeignKey('customers', 'customers_assigned_to_foreign');
        $this->forge->dropColumn('customers', 'assigned_to');
        $this->db->enableForeignKeyChecks();
    }
}