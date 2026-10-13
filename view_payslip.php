```php
<?php
require_once __DIR__ . '/db_connect.php';

/*
|--------------------------------------------------------------------------
| Authentication
|--------------------------------------------------------------------------
| This file is loaded as a modal fragment, so return an HTTP error
| rather than redirecting to a full login page.
*/
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

if (!isset($_SESSION['login_id'])) {
    http_response_code(401);
    exit('Unauthorized');
}

/*
|--------------------------------------------------------------------------
| Validate payslip ID
|--------------------------------------------------------------------------
*/
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if ($id === false || $id === null || $id < 1) {
    http_response_code(400);
    exit('Invalid payslip ID');
}

/*
|--------------------------------------------------------------------------
| Load payroll item using a prepared statement
|--------------------------------------------------------------------------
*/
$stmt = $conn->prepare(
    "SELECT p.*,
            CONCAT(e.lastname, ', ', e.firstname, ' ', e.middlename) AS ename,
            e.employee_no
     FROM payroll_items AS p
     INNER JOIN employee AS e ON e.id = p.employee_id
     WHERE p.id = ?"
);

if (!$stmt) {
    http_response_code(500);
    exit('Unable to load payslip');
}

$stmt->bind_param('i', $id);
$stmt->execute();

$result = $stmt->get_result();
$payroll = $result->fetch_assoc();

$stmt->close();

if (!$payroll) {
    http_response_code(404);
    exit('Payslip not found');
}

/*
|--------------------------------------------------------------------------
| Assign payroll item values
|--------------------------------------------------------------------------
*/
foreach ($payroll as $key => $value) {
    $$key = $value;
}

/*
|--------------------------------------------------------------------------
| Load associated payroll record securely
|--------------------------------------------------------------------------
*/
$payroll_id_value = filter_var(
    $payroll_id ?? null,
    FILTER_VALIDATE_INT
);

if ($payroll_id_value === false || $payroll_id_value === null || $payroll_id_value < 1) {
    http_response_code(404);
    exit('Payroll record not found');
}

$stmt = $conn->prepare(
    "SELECT * FROM payroll WHERE id = ?"
);

if (!$stmt) {
    http_response_code(500);
    exit('Unable to load payroll details');
}

$stmt->bind_param('i', $payroll_id_value);
$stmt->execute();

$pay_result = $stmt->get_result();
$pay = $pay_result->fetch_assoc();

$stmt->close();

if (!$pay) {
    http_response_code(404);
    exit('Payroll record not found');
}

/*
|--------------------------------------------------------------------------
| Load allowance and deduction names
|--------------------------------------------------------------------------
*/
$all_arr = [];
$ded_arr = [];

$all_qry = $conn->query("SELECT id, allowance FROM allowances");

if ($all_qry) {
    while ($row = $all_qry->fetch_assoc()) {
        $all_arr[$row['id']] = $row['allowance'];
    }
}

$ded_qry = $conn->query("SELECT id, deduction FROM deductions");

if ($ded_qry) {
    while ($row = $ded_qry->fetch_assoc()) {
        $ded_arr[$row['id']] = $row['deduction'];
    }
}

/*
|--------------------------------------------------------------------------
| Decode allowance and deduction details safely
|--------------------------------------------------------------------------
*/
$allowance_items = json_decode($allowances ?? '[]');
$deduction_items = json_decode($deductions ?? '[]');

if (!is_array($allowance_items)) {
    $allowance_items = [];
}

if (!is_array($deduction_items)) {
    $deduction_items = [];
}

$pt = [
    1 => 'Monthly',
    2 => 'Semi-Monthly'
];

/*
|--------------------------------------------------------------------------
| Escape output
|--------------------------------------------------------------------------
*/
function payslip_escape($value)
{
    return htmlspecialchars(
        (string) ($value ?? ''),
        ENT_QUOTES | ENT_SUBSTITUTE,
        'UTF-8'
    );
}
?>

<div class="container-fluid">
    <div class="col-md-12">

        <h5>
            <b>
                <small>Employee ID :</small>
                <?php echo payslip_escape($employee_no); ?>
            </b>
        </h5>

        <h4>
            <b>
                <small>Name: </small>
                <?php echo payslip_escape(ucwords((string) $ename)); ?>
            </b>
        </h4>

        <hr class="divider">

        <div class="row">
            <div class="col-md-6">
                <p>
                    <b>
                        Payroll Ref :
                        <?php echo payslip_escape($pay['ref_no'] ?? ''); ?>
                    </b>
                </p>

                <p>
                    <b>
                        Payroll Range :
                        <?php
                        $date_from = strtotime($pay['date_from'] ?? '');
                        $date_to = strtotime($pay['date_to'] ?? '');

                        echo $date_from !== false
                            ? date('M d, Y', $date_from)
                            : 'N/A';

                        echo ' - ';

                        echo $date_to !== false
                            ? date('M d, Y', $date_to)
                            : 'N/A';
                        ?>
                    </b>
                </p>

                <p>
                    <b>
                        Payroll type :
                        <?php
                        $payroll_type = $pay['type'] ?? null;
                        echo payslip_escape(
                            $pt[$payroll_type] ?? 'Unknown'
                        );
                        ?>
                    </b>
                </p>
            </div>

            <div class="col-md-6">
                <p>
                    <b>
                        Days of Absent :
                        <?php echo payslip_escape($absent ?? 0); ?>
                    </b>
                </p>

                <p>
                    <b>
                        Tardy/Undertime (mins) :
                        <?php echo payslip_escape($late ?? 0); ?>
                    </b>
                </p>

                <p>
                    <b>
                        Total Allowance Amount :
                        <?php echo number_format((float) ($allowance_amount ?? 0), 2); ?>
                    </b>
                </p>

                <p>
                    <b>
                        Total Deduction Amount :
                        <?php echo number_format((float) ($deduction_amount ?? 0), 2); ?>
                    </b>
                </p>

                <p>
                    <b>
                        Net Pay :
                        <?php echo number_format((float) ($net ?? 0), 2); ?>
                    </b>
                </p>
            </div>
        </div>

        <hr class="divider">

        <div class="row">

            <!-- Allowances -->
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <span><b>Allowances</b></span>
                    </div>

                    <div class="card-body">
                        <ul class="list-group">
                            <?php foreach ($allowance_items as $val): ?>
                                <?php
                                if (
                                    !is_object($val) ||
                                    !isset($val->aid) ||
                                    !is_numeric($val->aid)
                                ) {
                                    continue;
                                }

                                $aid = (int) $val->aid;
                                $allowance_name = $all_arr[$aid] ?? 'Unknown allowance';
                                $amount = isset($val->amount) && is_numeric($val->amount)
                                    ? (float) $val->amount
                                    : 0.0;
                                ?>

                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <?php echo payslip_escape($allowance_name); ?> Allowance

                                    <span class="badge badge-primary badge-pill">
                                        <?php echo number_format($amount, 2); ?>
                                    </span>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Deductions -->
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <span><b>Deductions</b></span>
                    </div>

                    <div class="card-body">
                        <ul class="list-group">
                            <?php foreach ($deduction_items as $val): ?>
                                <?php
                                if (
                                    !is_object($val) ||
                                    !isset($val->did) ||
                                    !is_numeric($val->did)
                                ) {
                                    continue;
                                }

                                $did = (int) $val->did;
                                $deduction_name = $ded_arr[$did] ?? 'Unknown deduction';
                                $amount = isset($val->amount) && is_numeric($val->amount)
                                    ? (float) $val->amount
                                    : 0.0;
                                ?>

                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <?php echo payslip_escape($deduction_name); ?>

                                    <span class="badge badge-primary badge-pill">
                                        <?php echo number_format($amount, 2); ?>
                                    </span>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <hr>

    <div class="row">
        <div class="col-lg-12">
            <button
                class="btn btn-primary btn-sm btn-block col-md-2 float-right"
                type="button"
                data-dismiss="modal"
            >
                Close
            </button>
        </div>
    </div>
</div>

<style type="text/css">
    .list-group-item > span > p {
        margin: unset;
    }

    .list-group-item > span > p > small {
        font-weight: 700;
    }

    #uni_modal .modal-footer {
        display: none;
    }

    .alist,
    .dlist {
        width: 100%;
    }
</style>

<script>
</script>
```
