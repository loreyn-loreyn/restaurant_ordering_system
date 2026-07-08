<?php

// @formatter:off
// phpcs:ignoreFile
/**
 * A helper file for your Eloquent Models
 * Copy the phpDocs from this file to the correct Model,
 * And remove them from this file, to prevent double declarations.
 *
 * @author Barry vd. Heuvel <barryvdh@gmail.com>
 */


namespace App\Models{
/**
 * @property int $AttendanceID
 * @property string $StaffID
 * @property \Illuminate\Support\Carbon $AttendanceDate
 * @property string $Status
 * @property string|null $TimeIn
 * @property string|null $TimeOut
 * @property string|null $Note
 * @property-read float $hours_worked
 * @property-read \App\Models\StaffDetails $staff
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Attendance newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Attendance newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Attendance query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Attendance whereAttendanceDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Attendance whereAttendanceID($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Attendance whereNote($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Attendance whereStaffID($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Attendance whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Attendance whereTimeIn($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Attendance whereTimeOut($value)
 */
	class Attendance extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $CategoryID
 * @property string $CategoryName
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Dish> $dishes
 * @property-read int|null $dishes_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category whereCategoryID($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category whereCategoryName($value)
 */
	class Category extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $DiscountID
 * @property string $Type
 * @property string $Reason
 * @property numeric $Amount
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Order> $orders
 * @property-read int|null $orders_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Discount newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Discount newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Discount query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Discount whereAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Discount whereDiscountID($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Discount whereReason($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Discount whereType($value)
 */
	class Discount extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $DishID
 * @property int $CategoryID
 * @property string $DishName
 * @property string $Description
 * @property numeric $Price
 * @property string $DishCode
 * @property string|null $Photo
 * @property array<array-key, mixed>|null $Choices
 * @property bool $Availability
 * @property-read \App\Models\Category $category
 * @property-read array $choice_list
 * @property-read string|null $photo_url
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\OrderItem> $orderItems
 * @property-read int|null $order_items_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Dish menuOrder()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Dish newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Dish newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Dish query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Dish whereAvailability($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Dish whereCategoryID($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Dish whereChoices($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Dish whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Dish whereDishCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Dish whereDishID($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Dish whereDishName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Dish wherePhoto($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Dish wherePrice($value)
 */
	class Dish extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $LoginLogID
 * @property int $UserID
 * @property \Illuminate\Support\Carbon $LoginAt
 * @property \Illuminate\Support\Carbon|null $LogoutAt
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LoginLog newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LoginLog newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LoginLog query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LoginLog whereLoginAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LoginLog whereLoginLogID($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LoginLog whereLogoutAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LoginLog whereUserID($value)
 */
	class LoginLog extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $OrderID
 * @property int $UserID
 * @property int|null $PaymentID
 * @property int|null $DiscountID
 * @property bool $OrderType
 * @property bool $OrderStatus
 * @property \Illuminate\Support\Carbon $OrderDate
 * @property string|null $OrderTime
 * @property numeric $TotalAmount
 * @property numeric $Change
 * @property-read \App\Models\Discount|null $discount
 * @property-read int $item_count
 * @property-read string $order_type_label
 * @property-read float $subtotal
 * @property-read float $total_after_discount
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\OrderItem> $items
 * @property-read int|null $items_count
 * @property-read \App\Models\Payment|null $payment
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereChange($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereDiscountID($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereOrderDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereOrderID($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereOrderStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereOrderTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereOrderType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order wherePaymentID($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereTotalAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereUserID($value)
 */
	class Order extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $OrderItemID
 * @property int $OrderID
 * @property int $DishID
 * @property int $Quantity
 * @property string $ItemStatus
 * @property string $Choice
 * @property string|null $SpecialInstruction
 * @property-read \App\Models\Dish $dish
 * @property-read float $line_total
 * @property-read \App\Models\Order $order
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderItem newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderItem newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderItem query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderItem whereChoice($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderItem whereDishID($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderItem whereItemStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderItem whereOrderID($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderItem whereOrderItemID($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderItem whereQuantity($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderItem whereSpecialInstruction($value)
 */
	class OrderItem extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $PaymentID
 * @property int $OrderID
 * @property string $StaffID
 * @property string $Method
 * @property numeric $RenderedAmount
 * @property int|null $Reference
 * @property \Illuminate\Support\Carbon $TransactionDate
 * @property-read \App\Models\Order $order
 * @property-read \App\Models\StaffDetails $staff
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payment newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payment newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payment query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payment whereMethod($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payment whereOrderID($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payment wherePaymentID($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payment whereReference($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payment whereRenderedAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payment whereStaffID($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payment whereTransactionDate($value)
 */
	class Payment extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $RoleID
 * @property string $RoleName
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\User> $users
 * @property-read int|null $users_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Role newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Role newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Role query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Role whereRoleID($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Role whereRoleName($value)
 */
	class Role extends \Eloquent {}
}

namespace App\Models{
/**
 * @property string $StaffID
 * @property int|null $UserID
 * @property int|null $RoleID
 * @property string $LastName
 * @property string $FirstName
 * @property string|null $MiddleName
 * @property string|null $Photo
 * @property int $Age
 * @property \Illuminate\Support\Carbon $BirthDate
 * @property string $Sex
 * @property string $BirthPlace
 * @property string $Nationality
 * @property string $Address
 * @property string $ContactNumber
 * @property string $Email
 * @property \Illuminate\Support\Carbon $HiredDate
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Attendance> $attendances
 * @property-read int|null $attendances_count
 * @property-read string $full_name
 * @property-read bool $has_account
 * @property-read string|null $photo_url
 * @property-read string|null $section
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Payment> $payments
 * @property-read int|null $payments_count
 * @property-read \App\Models\Role|null $role
 * @property-read \App\Models\User|null $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StaffDetails newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StaffDetails newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StaffDetails query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StaffDetails whereAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StaffDetails whereAge($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StaffDetails whereBirthDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StaffDetails whereBirthPlace($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StaffDetails whereContactNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StaffDetails whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StaffDetails whereFirstName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StaffDetails whereHiredDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StaffDetails whereLastName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StaffDetails whereMiddleName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StaffDetails whereNationality($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StaffDetails wherePhoto($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StaffDetails whereRoleID($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StaffDetails whereSex($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StaffDetails whereStaffID($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StaffDetails whereUserID($value)
 */
	class StaffDetails extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $UserID
 * @property int $RoleID
 * @property string $UserName
 * @property string $Password
 * @property \Illuminate\Support\Carbon $DateIssued
 * @property bool $AccountStatus
 * @property bool $AccountApprovalStatus
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection<int, \Illuminate\Notifications\DatabaseNotification> $notifications
 * @property-read int|null $notifications_count
 * @property-read \App\Models\Role $role
 * @property-read \App\Models\StaffDetails|null $staffDetails
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereAccountApprovalStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereAccountStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereDateIssued($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereRoleID($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereUserID($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereUserName($value)
 */
	class User extends \Eloquent {}
}

