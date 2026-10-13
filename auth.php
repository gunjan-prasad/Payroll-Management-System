```php
<?php

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

if (!isset($_SESSION['login_id'])) {
    header('Location: login.php');
    exit;
}
```
