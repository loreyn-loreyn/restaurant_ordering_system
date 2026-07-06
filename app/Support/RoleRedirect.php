<?php

namespace App\Support;

class RoleRedirect
{
    /**
     * Map of RoleName (as stored in the roles table) to the route name
     * that role should land on right after logging in.
     *
     * Adjust the RoleName keys here to match exactly what you seed
     * into the `roles` table (case-sensitive).
     */
    public static function map(): array
    {
        return [
            'Admin'         => 'admin.dashboard',
            'Manager'       => 'manager.sales',
            'Cashier'       => 'cashier.order-type',
            'Kitchen Staff' => 'kitchen.orders',
        ];
    }

    /**
     * Get the route name a given role name should be redirected to.
     * Falls back to 'login' if the role isn't recognized.
     */
    public static function routeFor(?string $roleName): string
    {
        $map = self::map();

        return $map[$roleName] ?? 'login';
    }
}
