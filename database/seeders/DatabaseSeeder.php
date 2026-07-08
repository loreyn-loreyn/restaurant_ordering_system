<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Orders/payments/attendance below are dated relative to "now" (rather
        // than a fixed calendar date) so seeded demo data always falls inside
        // reports' default windows (e.g. Sales.php's default 'daily' period
        // only looks back 7 days) no matter when the seeder or test suite runs.
        $orderDay = Carbon::now()->subDays(2)->toDateString();
        $laterOrderDay = Carbon::now()->subDay()->toDateString();
        // ---------------------------------------------------------
        // roles
        // ---------------------------------------------------------
        DB::table('roles')->insert([
            ['RoleID' => 1, 'RoleName' => 'Admin'],
            ['RoleID' => 2, 'RoleName' => 'Manager'],
            ['RoleID' => 3, 'RoleName' => 'Cashier'],
            ['RoleID' => 4, 'RoleName' => 'Kitchen Staff'],
            ['RoleID' => 5, 'RoleName' => 'Server'],
        ]);

        // ---------------------------------------------------------
        // categories
        // ---------------------------------------------------------
        DB::table('categories')->insert([
            ['CategoryID' => 1, 'CategoryName' => 'Main Dish'],
            ['CategoryID' => 2, 'CategoryName' => 'Dessert'],
            ['CategoryID' => 3, 'CategoryName' => 'Drinks'],
            ['CategoryID' => 4, 'CategoryName' => 'Sides'],
        ]);

        // ---------------------------------------------------------
        // users
        // Password = birthdate in MMDDYYYY format, hashed via Hash::make()
        // ---------------------------------------------------------
        DB::table('users')->insert([
            [
                'UserID' => 1, 'RoleID' => 1, 'UserName' => 'A001',
                'Password' => Hash::make('05121990'), // birthdate 1990-05-12
                'DateIssued' => '2026-01-05', 'AccountStatus' => 1, 'AccountApprovalStatus' => 1,
            ],
            [
                'UserID' => 2, 'RoleID' => 2, 'UserName' => 'M004',
                'Password' => Hash::make('09051995'), // birthdate 1995-09-05
                'DateIssued' => '2026-01-10', 'AccountStatus' => 1, 'AccountApprovalStatus' => 1,
            ],
            [
                'UserID' => 3, 'RoleID' => 3, 'UserName' => 'C002',
                'Password' => Hash::make('03212001'), // birthdate 2001-03-21
                'DateIssued' => '2026-02-01', 'AccountStatus' => 1, 'AccountApprovalStatus' => 1,
            ],
            [
                'UserID' => 4, 'RoleID' => 3, 'UserName' => 'C005',
                'Password' => Hash::make('07141998'), // birthdate 1998-07-14
                'DateIssued' => '2026-02-15', 'AccountStatus' => 0, 'AccountApprovalStatus' => 1, // inactive
            ],
            [
                'UserID' => 5, 'RoleID' => 4, 'UserName' => 'K003',
                'Password' => Hash::make('11301996'), // birthdate 1996-11-30
                'DateIssued' => '2026-02-20', 'AccountStatus' => 1, 'AccountApprovalStatus' => 1,
            ],
            [
                'UserID' => 6, 'RoleID' => 4, 'UserName' => 'K007',
                'Password' => Hash::make('12251999'), // birthdate 1999-12-25
                'DateIssued' => '2026-06-01', 'AccountStatus' => 1, 'AccountApprovalStatus' => 0, // pending approval
            ],
        ]);

        // ---------------------------------------------------------
        // staff_details
        // ---------------------------------------------------------
        DB::table('staff_details')->insert([
            [
                'StaffID' => 'A001', 'UserID' => 1, 'RoleID' => 1, 'LastName' => 'Dela Cruz', 'FirstName' => 'Juan',
                'MiddleName' => 'Santos', 'Age' => 35, 'BirthDate' => '1990-05-12', 'Sex' => 'M',
                'BirthPlace' => 'Manila', 'Nationality' => 'Filipino', 'Address' => '123 Rizal St, Manila',
                'ContactNumber' => '09171234567', 'Email' => 'juan.delacruz@example.com', 'HiredDate' => '2026-01-05',
            ],
            [
                'StaffID' => 'M004', 'UserID' => 2, 'RoleID' => 2, 'LastName' => 'Lim', 'FirstName' => 'Ana',
                'MiddleName' => 'Reyes', 'Age' => 30, 'BirthDate' => '1995-09-05', 'Sex' => 'F',
                'BirthPlace' => 'Calamba', 'Nationality' => 'Filipino', 'Address' => '45 Mabini St, Calamba',
                'ContactNumber' => '09181234567', 'Email' => 'ana.lim@example.com', 'HiredDate' => '2026-01-10',
            ],
            [
                'StaffID' => 'C002', 'UserID' => 3, 'RoleID' => 3, 'LastName' => 'Santos', 'FirstName' => 'Maria',
                'MiddleName' => 'Garcia', 'Age' => 24, 'BirthDate' => '2001-03-21', 'Sex' => 'F',
                'BirthPlace' => 'Laguna', 'Nationality' => 'Filipino', 'Address' => '67 Bonifacio St, Laguna',
                'ContactNumber' => '09191234567', 'Email' => 'maria.santos@example.com', 'HiredDate' => '2026-02-01',
            ],
            [
                'StaffID' => 'C005', 'UserID' => 4, 'RoleID' => 3, 'LastName' => 'Bautista', 'FirstName' => 'Carlos',
                'MiddleName' => 'Tan', 'Age' => 27, 'BirthDate' => '1998-07-14', 'Sex' => 'M',
                'BirthPlace' => 'Cavite', 'Nationality' => 'Filipino', 'Address' => '89 Aguinaldo St, Cavite',
                'ContactNumber' => '09201234567', 'Email' => 'carlos.bautista@example.com', 'HiredDate' => '2026-02-15',
            ],
            [
                'StaffID' => 'K003', 'UserID' => 5, 'RoleID' => 4, 'LastName' => 'Reyes', 'FirstName' => 'Pedro',
                'MiddleName' => 'Cruz', 'Age' => 29, 'BirthDate' => '1996-11-30', 'Sex' => 'M',
                'BirthPlace' => 'Batangas', 'Nationality' => 'Filipino', 'Address' => '12 Luna St, Batangas',
                'ContactNumber' => '09211234567', 'Email' => 'pedro.reyes@example.com', 'HiredDate' => '2026-02-20',
            ],
            [
                'StaffID' => 'K007', 'UserID' => 6, 'RoleID' => 4, 'LastName' => 'Doe', 'FirstName' => 'John',
                'MiddleName' => null, 'Age' => 26, 'BirthDate' => '1999-12-25', 'Sex' => 'M',
                'BirthPlace' => 'Quezon City', 'Nationality' => 'Filipino', 'Address' => '34 Magsaysay Ave, QC',
                'ContactNumber' => '09221234567', 'Email' => 'john.doe@example.com', 'HiredDate' => '2026-06-01',
            ],
        ]);

        // ---------------------------------------------------------
        // dishes
        // ---------------------------------------------------------
        DB::table('dishes')->insert([
            ['DishID' => 1, 'CategoryID' => 1, 'DishName' => 'Beef Sisig', 'Description' => 'Sizzling chopped beef with onions and chili', 'Price' => 180.00, 'DishCode' => 'MD-001', 'Availability' => 1],
            ['DishID' => 2, 'CategoryID' => 1, 'DishName' => 'Fried Chicken', 'Description' => 'Crispy fried chicken with house gravy', 'Price' => 150.00, 'DishCode' => 'MD-002', 'Availability' => 1],
            ['DishID' => 3, 'CategoryID' => 2, 'DishName' => 'Halo-Halo', 'Description' => 'Mixed shaved ice dessert', 'Price' => 120.00, 'DishCode' => 'DS-001', 'Availability' => 1],
            ['DishID' => 4, 'CategoryID' => 3, 'DishName' => 'Iced Tea', 'Description' => 'House blend iced tea', 'Price' => 50.00, 'DishCode' => 'DR-001', 'Availability' => 1],
            ['DishID' => 5, 'CategoryID' => 3, 'DishName' => 'Bottled Water', 'Description' => '500ml bottled water', 'Price' => 25.00, 'DishCode' => 'DR-002', 'Availability' => 0],
            ['DishID' => 6, 'CategoryID' => 4, 'DishName' => 'Garlic Rice', 'Description' => 'Fried rice with garlic', 'Price' => 40.00, 'DishCode' => 'SD-001', 'Availability' => 1],
        ]);

        // ---------------------------------------------------------
        // discounts
        // ---------------------------------------------------------
        DB::table('discounts')->insert([
            ['DiscountID' => 1, 'Type' => 'Discount', 'Reason' => 'Senior Citizen', 'Amount' => 20.00],
            ['DiscountID' => 2, 'Type' => 'Discount', 'Reason' => 'PWD', 'Amount' => 20.00],
            ['DiscountID' => 3, 'Type' => 'Discount', 'Reason' => 'Promo Code', 'Amount' => 10.00],
            ['DiscountID' => 4, 'Type' => 'Comp', 'Reason' => 'Customer Complaint', 'Amount' => 350.00],
            ['DiscountID' => 5, 'Type' => 'Comp', 'Reason' => 'Manager Goodwill', 'Amount' => 200.00],
        ]);
        // ---------------------------------------------------------
        // orders (PaymentID left null for now; orders <-> payments are circular)
        // ---------------------------------------------------------
        DB::table('orders')->insert([
            ['OrderID' => 1, 'UserID' => 3, 'PaymentID' => null, 'DiscountID' => null, 'OrderType' => true, 'OrderStatus' => true, 'OrderDate' => $orderDay, 'TotalAmount' => 380.00, 'Change' => 20.00],
            ['OrderID' => 2, 'UserID' => 4, 'PaymentID' => null, 'DiscountID' => null, 'OrderType' => false, 'OrderStatus' => true, 'OrderDate' => $orderDay, 'TotalAmount' => 175.00, 'Change' => 25.00],
            ['OrderID' => 3, 'UserID' => 3, 'PaymentID' => null, 'DiscountID' => null, 'OrderType' => true, 'OrderStatus' => false, 'OrderDate' => $laterOrderDay, 'TotalAmount' => 120.00, 'Change' => 0.00],
        ]);

        // ---------------------------------------------------------
        // payments
        // ---------------------------------------------------------
        DB::table('payments')->insert([
            ['PaymentID' => 1, 'OrderID' => 1, 'StaffID' => 'C002', 'Method' => 'Cash', 'RenderedAmount' => 400.00, 'Reference' => null, 'TransactionDate' => $orderDay],
            ['PaymentID' => 2, 'OrderID' => 2, 'StaffID' => 'C005', 'Method' => 'GCash', 'RenderedAmount' => 175.00, 'Reference' => 100002, 'TransactionDate' => $orderDay],
        ]);
 
        DB::table('orders')->where('OrderID', 1)->update(['PaymentID' => 1]);
        DB::table('orders')->where('OrderID', 2)->update(['PaymentID' => 2]);

        // Link orders back to their payments now that payments exist
        DB::table('orders')->where('OrderID', 1)->update(['PaymentID' => 1]);
        DB::table('orders')->where('OrderID', 2)->update(['PaymentID' => 2]);

        // ---------------------------------------------------------
        // order_items
        // ---------------------------------------------------------
        DB::table('order_items')->insert([
            ['OrderItemID' => 1, 'OrderID' => 1, 'DishID' => 1, 'Quantity' => 1, 'ItemStatus' => 'R', 'Choice' => 'Regular', 'SpecialInstruction' => 'No chili'],
            ['OrderItemID' => 2, 'OrderID' => 1, 'DishID' => 4, 'Quantity' => 2, 'ItemStatus' => 'R', 'Choice' => 'Regular', 'SpecialInstruction' => null],
            ['OrderItemID' => 3, 'OrderID' => 2, 'DishID' => 2, 'Quantity' => 1, 'ItemStatus' => 'S', 'Choice' => 'Regular', 'SpecialInstruction' => 'Extra gravy'],
            ['OrderItemID' => 4, 'OrderID' => 2, 'DishID' => 5, 'Quantity' => 1, 'ItemStatus' => 'R', 'Choice' => 'Regular', 'SpecialInstruction' => null],
            ['OrderItemID' => 5, 'OrderID' => 3, 'DishID' => 3, 'Quantity' => 1, 'ItemStatus' => 'P', 'Choice' => 'Regular', 'SpecialInstruction' => null],
        ]);
        // ---------------------------------------------------------
        // attendances
        // ---------------------------------------------------------
        DB::table('attendances')->insert([
            ['AttendanceID' => 1, 'StaffID' => 'C002', 'AttendanceDate' => $orderDay . ' 08:00:00', 'Status' => 'P', 'TimeIn' => '08:00:00', 'TimeOut' => '17:00:00'],
            ['AttendanceID' => 2, 'StaffID' => 'C005', 'AttendanceDate' => $orderDay . ' 08:05:00', 'Status' => 'P', 'TimeIn' => '08:05:00', 'TimeOut' => '17:10:00'],
            ['AttendanceID' => 3, 'StaffID' => 'K003', 'AttendanceDate' => $orderDay . ' 07:45:00', 'Status' => 'P', 'TimeIn' => '07:45:00', 'TimeOut' => '16:45:00'],
            ['AttendanceID' => 4, 'StaffID' => 'K007', 'AttendanceDate' => $orderDay . ' 00:00:00', 'Status' => 'A', 'TimeIn' => null, 'TimeOut' => null],
            ['AttendanceID' => 5, 'StaffID' => 'M004', 'AttendanceDate' => $orderDay . ' 07:30:00', 'Status' => 'P', 'TimeIn' => '07:30:00', 'TimeOut' => '18:00:00'],
        ]);

        $this->command->info('Seeding complete. Login credentials:');
        $this->command->table(
            ['Username', 'Password', 'Role', 'Notes'],
            [
                ['A001', '05121990', 'Admin', ''],
                ['M004', '09051995', 'Manager', ''],
                ['C002', '03212001', 'Cashier', ''],
                ['C005', '07141998', 'Cashier', 'inactive account'],
                ['K003', '11301996', 'Kitchen Staff', ''],
                ['K007', '12251999', 'Kitchen Staff', 'pending approval'],
            ]
        );
    }
}