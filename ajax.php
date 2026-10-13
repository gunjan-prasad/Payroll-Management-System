```php
<?php
ob_start();

/*
|--------------------------------------------------------------------------
| Start session safely
|--------------------------------------------------------------------------
*/
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

/*
|--------------------------------------------------------------------------
| Validate action parameter
|--------------------------------------------------------------------------
*/
$action = $_GET['action'] ?? '';

if (!is_string($action) || $action === '') {
    http_response_code(400);
    exit('Invalid action');
}

/*
|--------------------------------------------------------------------------
| Public actions
|--------------------------------------------------------------------------
| These actions must be accessible before login.
*/
$public_actions = [
    'login',
    'login2',
    'signup',
    'logout',
    'logout2'
];

/*
|--------------------------------------------------------------------------
| Protect all other actions
|--------------------------------------------------------------------------
*/
if (
    !in_array($action, $public_actions, true) &&
    !isset($_SESSION['login_id'])
) {
    http_response_code(401);
    exit('Unauthorized');
}

/*
|--------------------------------------------------------------------------
| Load application actions
|--------------------------------------------------------------------------
*/
require_once __DIR__ . '/admin_class.php';

$crud = new Action();

/*
|--------------------------------------------------------------------------
| Login
|--------------------------------------------------------------------------
*/
if ($action === 'login') {
    $login = $crud->login();

    if ($login) {
        echo $login;
    }
}

if ($action === 'login2') {
    $login = $crud->login2();

    if ($login) {
        echo $login;
    }
}

/*
|--------------------------------------------------------------------------
| Logout
|--------------------------------------------------------------------------
*/
if ($action === 'logout') {
    $logout = $crud->logout();

    if ($logout) {
        echo $logout;
    }
}

if ($action === 'logout2') {
    $logout = $crud->logout2();

    if ($logout) {
        echo $logout;
    }
}

/*
|--------------------------------------------------------------------------
| User management
|--------------------------------------------------------------------------
*/
if ($action === 'save_user') {
    $save = $crud->save_user();

    if ($save) {
        echo $save;
    }
}

if ($action === 'delete_user') {
    $save = $crud->delete_user();

    if ($save) {
        echo $save;
    }
}

if ($action === 'signup') {
    $save = $crud->signup();

    if ($save) {
        echo $save;
    }
}

/*
|--------------------------------------------------------------------------
| Settings
|--------------------------------------------------------------------------
*/
if ($action === 'save_settings') {
    $save = $crud->save_settings();

    if ($save) {
        echo $save;
    }
}

/*
|--------------------------------------------------------------------------
| Employee management
|--------------------------------------------------------------------------
*/
if ($action === 'save_employee') {
    $save = $crud->save_employee();

    if ($save) {
        echo $save;
    }
}

if ($action === 'delete_employee') {
    $save = $crud->delete_employee();

    if ($save) {
        echo $save;
    }
}

/*
|--------------------------------------------------------------------------
| Department management
|--------------------------------------------------------------------------
*/
if ($action === 'save_department') {
    $save = $crud->save_department();

    if ($save) {
        echo $save;
    }
}

if ($action === 'delete_department') {
    $save = $crud->delete_department();

    if ($save) {
        echo $save;
    }
}

/*
|--------------------------------------------------------------------------
| Position management
|--------------------------------------------------------------------------
*/
if ($action === 'save_position') {
    $save = $crud->save_position();

    if ($save) {
        echo $save;
    }
}

if ($action === 'delete_position') {
    $save = $crud->delete_position();

    if ($save) {
        echo $save;
    }
}

/*
|--------------------------------------------------------------------------
| Allowance management
|--------------------------------------------------------------------------
*/
if ($action === 'save_allowances') {
    $save = $crud->save_allowances();

    if ($save) {
        echo $save;
    }
}

if ($action === 'delete_allowances') {
    $save = $crud->delete_allowances();

    if ($save) {
        echo $save;
    }
}

if ($action === 'save_employee_allowance') {
    $save = $crud->save_employee_allowance();

    if ($save) {
        echo $save;
    }
}

if ($action === 'delete_employee_allowance') {
    $save = $crud->delete_employee_allowance();

    if ($save) {
        echo $save;
    }
}

/*
|--------------------------------------------------------------------------
| Deduction management
|--------------------------------------------------------------------------
*/
if ($action === 'save_deductions') {
    $save = $crud->save_deductions();

    if ($save) {
        echo $save;
    }
}

if ($action === 'delete_deductions') {
    $save = $crud->delete_deductions();

    if ($save) {
        echo $save;
    }
}

if ($action === 'save_employee_deduction') {
    $save = $crud->save_employee_deduction();

    if ($save) {
        echo $save;
    }
}

if ($action === 'delete_employee_deduction') {
    $save = $crud->delete_employee_deduction();

    if ($save) {
        echo $save;
    }
}

/*
|--------------------------------------------------------------------------
| Attendance management
|--------------------------------------------------------------------------
*/
if ($action === 'save_employee_attendance') {
    $save = $crud->save_employee_attendance();

    if ($save) {
        echo $save;
    }
}

if ($action === 'delete_employee_attendance') {
    $save = $crud->delete_employee_attendance();

    if ($save) {
        echo $save;
    }
}

if ($action === 'delete_employee_attendance_single') {
    $save = $crud->delete_employee_attendance_single();

    if ($save) {
        echo $save;
    }
}

/*
|--------------------------------------------------------------------------
| Payroll management
|--------------------------------------------------------------------------
*/
if ($action === 'save_payroll') {
    $save = $crud->save_payroll();

    if ($save) {
        echo $save;
    }
}

if ($action === 'delete_payroll') {
    $save = $crud->delete_payroll();

    if ($save) {
        echo $save;
    }
}

if ($action === 'calculate_payroll') {
    $save = $crud->calculate_payroll();

    if ($save) {
        echo $save;
    }
}
```
