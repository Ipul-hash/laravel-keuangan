<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;

class PageController extends Controller
{
    public function dashboard()
    {
        return view('dashboard');
    }

    public function pemasukan()
    {
        return view('pemasukan.index');
    }

    public function pengeluaran()
    {
        return view('pengeluaran.index');
    }

    public function laporan()
    {
        return view('laporan.index');
    }
    public function login()
    {
        return view('auth.login');
    }
    public function register()
    {
        return view('auth.register');
    }
    public function anggaran()
{
    return view('anggaran'); // pastikan view ini ada
}
    public function settings()
{
    return view('settings.index'); // pastikan view ini ada
}
}
