<?php
declare(strict_types=1);

require __DIR__ . '/bootstrap.php';

require_login();

if (is_admin()) {
    redirect(app_url('admin/'));
} else {
    redirect(app_url('drafts.php'));
}
