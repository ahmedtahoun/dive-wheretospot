<?php
// Local-only router for `php -S`: serves real files (assets, uploads), sends everything else to index.php,
// and blocks the same folders the live .htaccess blocks.
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$root = rtrim($_SERVER['DOCUMENT_ROOT'], '/');
if (preg_match('#^/(app|dev)(/|$)|\.(md|yml|sql|sqlite)$|^/config\.local#', $uri)) { http_response_code(403); exit('Forbidden'); }
if (preg_match('#^/uploads/.+\.(php\d?|phtml|phar)$#i', $uri)) { http_response_code(403); exit('Forbidden'); }
if ($uri !== '/' && is_file($root . $uri)) return false;
require $root . '/index.php';
