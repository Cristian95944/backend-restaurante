<?php

use App\Controllers\ProductoController;

$app->get('/productos', [ProductoController::class, 'index']);