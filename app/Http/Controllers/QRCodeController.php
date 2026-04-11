<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class QRCodeController extends Controller
{
    /**
     * Tampilkan halaman generator QR Code.
     */
    public function index()
    {
        return view('qrcode.index');
    }
}
