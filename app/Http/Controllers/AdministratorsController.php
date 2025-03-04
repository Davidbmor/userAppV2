<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Middleware\AdminMiddleware;
use App\Http\Middleware\SuperAdminMiddleware;
use App\Models\User;

class AdministratorsController extends Controller
{
    function __construct()
    {
        $this->middleware(SuperAdminMiddleware::class)->only(['indexSuper']);
        $this->middleware(AdminMiddleware::class);
    }

    function index() {
        $users = User::where('role','<>', 'admin')->orderBy('name')->get();
        $users = User::where('id','<>', '1')->orderBy('name')->get();
        dd($users);
    }

    function indexSuper(){
        $users = User::orderBy('name')->get();
        dd($users);
    }
}
