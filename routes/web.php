<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\KuitansiController;
Route::get('/', function () {
    return view('welcome');
});
Route::get('/kuitansi/pemasukan/{id}', [KuitansiController::class, 'pemasukan'])->name('kuitansi.pemasukan');
Route::get('/kuitansi/pengeluaran/{id}', [KuitansiController::class, 'pengeluaran'])->name('kuitansi.pengeluaran');