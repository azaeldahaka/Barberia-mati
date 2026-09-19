<?php
header('Content-Type: text/plain');
if (file_exists('../storage/logs/laravel.log')) {
    echo file_get_contents('../storage/logs/laravel.log');
} else {
    echo 'No laravel.log found.';
}
