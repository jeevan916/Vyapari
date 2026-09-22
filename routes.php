<?php

use App\Controllers\AuthController;
use App\Controllers\DashboardController;
use App\Controllers\LedgerController;
use App\Controllers\ProductController;
use App\Controllers\TransactionController;
use App\Controllers\VyapariController;

$router->get('/', [DashboardController::class, 'index']);
$router->get('/login', [AuthController::class, 'showLogin']);
$router->post('/login', [AuthController::class, 'login']);
$router->post('/logout', [AuthController::class, 'logout']);

$router->get('/vyaparis', [VyapariController::class, 'index']);
$router->get('/vyaparis/create', [VyapariController::class, 'create']);
$router->post('/vyaparis/store', [VyapariController::class, 'store']);
$router->get('/vyaparis/edit', [VyapariController::class, 'edit']);
$router->post('/vyaparis/update', [VyapariController::class, 'update']);
$router->post('/vyaparis/delete', [VyapariController::class, 'delete']);
$router->post('/vyaparis/settle', [VyapariController::class, 'settle']);
$router->post('/vyaparis/roundoff', [VyapariController::class, 'roundoff']);

$router->get('/products', [ProductController::class, 'index']);
$router->get('/products/create', [ProductController::class, 'create']);
$router->post('/products/store', [ProductController::class, 'store']);
$router->get('/products/edit', [ProductController::class, 'edit']);
$router->post('/products/update', [ProductController::class, 'update']);
$router->post('/products/delete', [ProductController::class, 'delete']);

$router->get('/transactions', [TransactionController::class, 'index']);
$router->get('/transactions/create', [TransactionController::class, 'create']);
$router->post('/transactions/store', [TransactionController::class, 'store']);
$router->get('/transactions/edit', [TransactionController::class, 'edit']);
$router->post('/transactions/update', [TransactionController::class, 'update']);
$router->post('/transactions/delete', [TransactionController::class, 'delete']);

$router->get('/ledger', [LedgerController::class, 'show']);
