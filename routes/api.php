<?php

use App\Http\Controllers\Api\PriceCalculateController;
use Illuminate\Support\Facades\Route;

Route::get('/teste', function () {
  return response()->json("OI");
});