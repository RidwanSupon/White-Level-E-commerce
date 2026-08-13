<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            ['module' => 'products', 'name' => 'View Products', 'slug' => 'product.view'],
            ['module' => 'products', 'name' => 'Create Product', 'slug' => 'product.create'],
            ['module' => 'products', 'name' => 'Update Product', 'slug' => 'product.update'],
            ['module' => 'products', 'name' => 'Delete Product', 'slug' => 'product.delete'],
            
            ['module' => 'orders', 'name' => 'View Orders', 'slug' => 'order.view'],
            ['module' => 'orders', 'name' => 'Update Order', 'slug' => 'order.update'],
            ['module' => 'orders', 'name' => 'Cancel Order', 'slug' => 'order.cancel'],
            ['module' => 'orders', 'name' => 'Refund Order', 'slug' => 'order.refund'],
            
            ['module' => 'customers', 'name' => 'View Customers', 'slug' => 'customer.view'],
            ['module' => 'customers', 'name' => 'Update Customer', 'slug' => 'customer.update'],
            
            ['module' => 'inventory', 'name' => 'Manage Inventory', 'slug' => 'inventory.manage'],
            ['module' => 'reports', 'name' => 'View Reports', 'slug' => 'reports.view'],
            ['module' => 'settings', 'name' => 'Manage Settings', 'slug' => 'settings.manage'],

            ['module' => 'tax', 'name' => 'View Tax Settings', 'slug' => 'tax.view'],
            ['module' => 'tax', 'name' => 'Create Tax Rate', 'slug' => 'tax.create'],
            ['module' => 'tax', 'name' => 'Update Tax Rate', 'slug' => 'tax.update'],
            ['module' => 'tax', 'name' => 'Delete Tax Rate', 'slug' => 'tax.delete'],
            ['module' => 'tax', 'name' => 'Activate/Deactivate Tax Rate', 'slug' => 'tax.activate'],
        ];

        foreach ($permissions as $perm) {
            Permission::updateOrCreate(['slug' => $perm['slug']], $perm);
        }

        $superAdmin = Role::updateOrCreate(
            ['slug' => 'super-admin'],
            ['name' => 'Super Admin', 'description' => 'Unrestricted access to all features.', 'is_system' => true]
        );

        $admin = Role::updateOrCreate(
            ['slug' => 'admin'],
            ['name' => 'Admin', 'description' => 'General administrator access.', 'is_system' => true]
        );

        $orderManager = Role::updateOrCreate(
            ['slug' => 'order-manager'],
            ['name' => 'Order Manager', 'description' => 'Manages sales and customer orders.', 'is_system' => false]
        );

        $inventoryManager = Role::updateOrCreate(
            ['slug' => 'inventory-manager'],
            ['name' => 'Inventory Manager', 'description' => 'Manages products and stock.', 'is_system' => false]
        );

        // Assign all permissions to Super Admin & Admin
        $allPermissionIds = Permission::pluck('id')->toArray();
        $superAdmin->permissions()->sync($allPermissionIds);
        $admin->permissions()->sync($allPermissionIds);

        // Assign specific permissions to Order Manager & Inventory Manager
        $orderPerms = Permission::whereIn('slug', ['order.view', 'order.update', 'customer.view'])->pluck('id')->toArray();
        $orderManager->permissions()->sync($orderPerms);

        $invPerms = Permission::whereIn('slug', ['product.view', 'product.create', 'product.update', 'inventory.manage'])->pluck('id')->toArray();
        $inventoryManager->permissions()->sync($invPerms);
    }
}
