<?php

use Illuminate\Support\Facades\Route;

// Ganti 'app' jika nama file di resources/views/ berbeda
Route::view('/{any?}', 'app')->where('any', '^(?!api).*$');