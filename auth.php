```php
<?php
// auth.php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

if (!isset($_SESSION['login_id'])) {
    http_response_code(401);
    exit('Unauthorized');
}
```
