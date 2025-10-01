<?php

/*
 * Click nbfs://nbhost/SystemFileSystem/Templates/Licenses/license-default.txt to change this license
 * Click nbfs://nbhost/SystemFileSystem/Templates/Scripting/PHPClass.php to edit this template
 */

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Health;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Support\Facades\Auth;

/**
 * Description of HealthController
 *
 * @author User
 */
class HealthController extends Controller
{

    use AuthenticatesUsers;

    function __construct()
    {
        $this->middleware('permission:health-list', ['only' => ['index', 'show']]);
    }

    public function index()
    {

        $data = Health::where('STATUS', '1')->paginate(5);
        return view('health.index', compact('data'));
    }

    public function fetchData()
    {
        $data = Health::where('STATUS', '1')->paginate(5);
        return response()->json($data);
    }

}
