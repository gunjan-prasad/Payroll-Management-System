```php
<?php
// Start the session before any HTML output.
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

// Stop execution if the user is not authenticated.
if (!isset($_SESSION['login_id'])) {
    header('Location: login.php');
    exit;
}

// Restore the centralized authentication check.
require_once __DIR__ . '/auth.php';

// Only allow pages that actually exist in this repository.
$allowed_pages = [
    'home',
    'attendance',
    'payroll',
    'employee',
    'department',
    'position',
    'allowances',
    'deductions',
    'users'
];

$page = $_GET['page'] ?? 'home';

// Reject arrays, unexpected page names, and unauthorized user management.
if (
    !is_string($page) ||
    !in_array($page, $allowed_pages, true) ||
    ($page === 'users' && (int)($_SESSION['login_type'] ?? 0) !== 1)
) {
    $page = null;
}

// Resolve the page only after validating the allowlist.
$page_file = $page !== null
    ? __DIR__ . DIRECTORY_SEPARATOR . $page . '.php'
    : null;

if ($page_file === null || !is_file($page_file)) {
    http_response_code(404);
    $page_file = null;
}

include __DIR__ . '/header.php';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <title>Admin | Employee's Payroll Management System</title>
</head>

<style>
    body {
        background: #80808045;
    }

    .modal-dialog.large {
        width: 80% !important;
        max-width: unset;
    }

    .modal-dialog.mid-large {
        width: 50% !important;
        max-width: unset;
    }

    div#confirm_modal {
        z-index: 9991;
    }
</style>

<body>

    <?php include __DIR__ . '/topbar.php'; ?>
    <?php include __DIR__ . '/navbar.php'; ?>

    <div class="toast" id="alert_toast" role="alert"
         aria-live="assertive" aria-atomic="true">
        <div class="toast-body text-white"></div>
    </div>

    <main id="view-panel">
        <?php
        if ($page_file !== null) {
            include $page_file;
        } else {
            echo '<div class="container mt-4">
                    <h4>404 - Page Not Found</h4>
                  </div>';
        }
        ?>
    </main>

    <div id="preloader"></div>

    <a href="#" class="back-to-top">
        <i class="icofont-simple-up"></i>
    </a>

    <div class="modal fade" id="confirm_modal" role="dialog">
        <div class="modal-dialog modal-md" role="document">
            <div class="modal-content">

                <div class="modal-header">
                    <h5 class="modal-title">Confirmation</h5>
                </div>

                <div class="modal-body">
                    <div id="delete_content"></div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-primary"
                            id="confirm" onclick="">
                        Continue
                    </button>

                    <button type="button" class="btn btn-secondary"
                            data-dismiss="modal">
                        Close
                    </button>
                </div>

            </div>
        </div>
    </div>

    <div class="modal fade" id="uni_modal" role="dialog">
        <div class="modal-dialog modal-md" role="document">
            <div class="modal-content">

                <div class="modal-header">
                    <h5 class="modal-title"></h5>
                </div>

                <div class="modal-body"></div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-primary"
                            id="submit"
                            onclick="$('#uni_modal form').submit()">
                        Save
                    </button>

                    <button type="button" class="btn btn-secondary"
                            data-dismiss="modal">
                        Cancel
                    </button>
                </div>

            </div>
        </div>
    </div>

</body>

<script>
window.start_load = function () {
    $('body').prepend('<div id="preloader2"></div>');
};

window.end_load = function () {
    $('#preloader2').fadeOut('fast', function () {
        $(this).remove();
    });
};

window.uni_modal = function ($title = '', $url = '', $size = '') {
    start_load();

    $.ajax({
        url: $url,

        error: function () {
            alert('An error occurred.');
            end_load();
        },

        success: function (resp) {
            if (resp) {
                $('#uni_modal .modal-title').html($title);
                $('#uni_modal .modal-body').html(resp);

                if ($size !== '') {
                    $('#uni_modal .modal-dialog')
                        .removeClass('modal-md')
                        .addClass($size);
                } else {
                    $('#uni_modal .modal-dialog')
                        .removeAttr('class')
                        .addClass('modal-dialog modal-md');
                }

                $('#uni_modal').modal({
                    show: true,
                    backdrop: 'static',
                    keyboard: false,
                    focus: true
                });
            }

            end_load();
        }
    });
};

window._conf = function ($msg = '', $func = '', $params = []) {
    $('#confirm_modal #confirm')
        .attr('onclick', $func + '(' + $params.join(',') + ')');

    $('#confirm_modal .modal-body').html($msg);

    $('#confirm_modal').modal({
        show: true,
        backdrop: 'static',
        keyboard: false,
        focus: true
    });
};

window.alert_toast = function ($msg = 'TEST', $bg = 'success') {
    $('#alert_toast')
        .removeClass('bg-success bg-danger bg-info bg-warning');

    if (['success', 'danger', 'info', 'warning'].includes($bg)) {
        $('#alert_toast').addClass('bg-' + $bg);
    } else {
        $('#alert_toast').addClass('bg-success');
    }

    $('#alert_toast .toast-body').html($msg);
    $('#alert_toast').toast({ delay: 3000 }).toast('show');
};

$(document).ready(function () {
    $('#preloader').fadeOut('fast', function () {
        $(this).remove();
    });

    $('.datetimepicker').datetimepicker({
        format: 'Y/m/d H:i',
        startDate: '+3d'
    });

    $('.select2').select2({
        placeholder: 'Please select here',
        width: '100%'
    });
});
</script>

</html>
```
