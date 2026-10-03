<?php
declare(strict_types=1);require __DIR__.'/bootstrap.php';unset($_SESSION['portal_user']);session_regenerate_id(true);header('Location:portal-login.php');
