<?php

namespace App\Http\Controllers;

use App\Models\Cabang;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Merchant;
use App\User;
use Spatie\Permission\Models\Role;
use DB;
use Hash;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    function __construct()
    {
        $this->middleware('permission:user-list|user-create|user-edit|user-delete', ['only' => ['index', 'show']]);
        $this->middleware('permission:user-create', ['only' => ['create', 'store']]);
        $this->middleware('permission:user-edit', ['only' => ['edit', 'update']]);
        $this->middleware('permission:user-delete', ['only' => ['destroy']]);
        $this->middleware('permission:user-broadcast', ['only' => ['broadcast']]);
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $cabang = $user->cabangs->first();
        $cabangName = $cabang ? $cabang->CPC_MC_NAMA : null;
        $cabangId = $cabang ? $cabang->CPC_MC_KODE_CABANG : null;
        $cabangLokasi = $cabang ? $cabang->CPC_MC_KODE_LOKASI : null;
        $search = $request->search;
        $sql = User::orderBy('id', 'DESC');
        if ($user->hasRole('KCP')) {
            $sql->whereHas('cabangs', function ($query) use ($cabangId, $cabangLokasi) {
                $query->where('CPC_MC_KODE_CABANG', $cabangId);
                $query->where('CPC_MC_KODE_LOKASI', $cabangLokasi);
            });
        } elseif ($user->hasRole('KC')) {
            $sql->whereHas('cabangs', function ($query) use ($cabangId) {
                $query->where('CPC_MC_KODE_CABANG', $cabangId);
            });
        }
        if ($search != '') {
            $sql->where('email', 'like', '%' . $search . '%');
        }
        $data = $sql->paginate(5);
        return view('users.index', compact('data'))
            ->with('i', ($request->input('page', 1) - 1) * 5);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $user = Auth::user();
        $cabang = $user->cabangs->first();
        $cabangName = $cabang ? $cabang->CPC_MC_NAMA : null;
        $cabangId = $cabang ? $cabang->CPC_MC_KODE_CABANG : null;
        $cabangLokasi = $cabang ? $cabang->CPC_MC_KODE_LOKASI : null;
        if ($user->hasRole(['Superadmin', 'Admin'])) {
            $roles = Role::orderBy('name')->pluck('name', 'name');
        } elseif ($user->hasRole('KC')) {
            $roles = Role::orderBy('name')->whereIn('name', ['KC', 'KCP'])->pluck('name', 'name');
        } elseif ($user->hasRole('KCP')) {
            $roles = Role::orderBy('name')->whereIn('name', ['KCP'])->pluck('name', 'name');
        } else {
            $roles = [];
        }
        $roles->all();

        if ($user->hasRole(['Superadmin', 'Admin'])) {
            $merchants = Merchant::orderBy('MERCHANT_NAME')->pluck('MERCHANT_NAME', 'MERCHANT_NAME');
        } else {
            $merchants = $user->merchants()->pluck('MERCHANT_NAME', 'MERCHANT_NAME');
        }
        $merchants->all();

        $sql = Cabang::orderBy('CPC_MC_NAMA');
        if ($user->hasRole('KC')) {
            $sql->where('CPC_MC_KODE_CABANG', $cabangId);
        } elseif ($user->hasRole('KCP')) {
            $sql->where('CPC_MC_KODE_CABANG', $cabangId);
            $sql->where('CPC_MC_KODE_LOKASI', $cabangLokasi);
        }
        $cabangs = $sql->get();

        return view('users.create', compact('roles', 'merchants', 'cabangs'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'name' => 'required',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|same:confirm-password',
            'roles' => 'required'
        ]);

        $input = $request->all();
        $input['password'] = Hash::make($input['password']);

        $user = User::create($input);
        $user->assignRole($request->input('roles'));

        // === Simpan cabang jika dipilih ===
        if ($request->filled('cabang')) {
            // pakai attach dengan pivot tambahan
            $user->cabangs()->attach(
                $request->input('cabang'),
                ['KODE_LOKASI' => $request->input('kode_lokasi')]
            );
        }

        // === Simpan merchant jika dipilih ===
        if ($request->filled('merchants')) {
            // Kalau single select
            $user->merchants()->attach($request->input('merchants'));

            // Kalau multiple select pakai array
            // $user->merchants()->attach($request->input('merchants', []));
        }

        return redirect()->route('users.index')
            ->with('success', 'User created successfully');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $id = Crypt::decrypt($id);
        $user = User::find($id);
        return view('users.show', compact('user'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $id = Crypt::decrypt($id);
        $user = User::find($id);
        $userAuth = Auth::user();
        $authCabang = $userAuth->cabangs->first();
        $authCabangId = $authCabang ? $authCabang->CPC_MC_KODE_CABANG : null;
        $authCabangLokasi = $authCabang ? $authCabang->CPC_MC_KODE_LOKASI : null;
        if ($userAuth->hasRole(['Superadmin', 'Admin'])) {
            $roles = Role::orderBy('name')->pluck('name', 'name');
        } elseif ($userAuth->hasRole('KC')) {
            $roles = Role::orderBy('name')->whereIn('name', ['KC', 'KCP', 'Merchant'])->pluck('name', 'name');
        } elseif ($userAuth->hasRole('KCP')) {
            $roles = Role::orderBy('name')->whereIn('name', ['KCP', 'Merchant'])->pluck('name', 'name');
        } else {
            $roles = [];
        }
        $roles->all();
        $userRole = $user->roles->pluck('name')->first(); // for single select

        if ($userAuth->hasRole(['Superadmin', 'Admin'])) {
            $merchants = Merchant::orderBy('MERCHANT_NAME')->pluck('MERCHANT_NAME', 'MERCHANT_NAME');
        } else {
            $merchants = $userAuth->merchants()->pluck('MERCHANT_NAME', 'MERCHANT_NAME');
        }
        $merchants->all();

        $sql = Cabang::orderBy('CPC_MC_NAMA');
        if ($userAuth->hasRole('KC')) {
            $sql->where('CPC_MC_KODE_CABANG', $authCabangId);
        } elseif ($userAuth->hasRole('KCP')) {
            $sql->where('CPC_MC_KODE_CABANG', $authCabangId);
            $sql->where('CPC_MC_KODE_LOKASI', $authCabangLokasi);
        }
        $cabangs = $sql->get();

        $userMerchant = $user->merchants->pluck('MERCHANT_NAME')->first();
        $userCabang = $user->cabangs->first();

        return view('users.edit', compact('user', 'roles', 'userRole', 'merchants', 'cabangs', 'userMerchant', 'userCabang'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $this->validate($request, [
            'name' => 'required',
            'email' => 'required|email|unique:users,email,' . $id,
            'password' => 'same:confirm-password',
            'roles' => 'required'
        ]);

        $input = $request->all();
        if (!empty($input['password'])) {
            $input['password'] = Hash::make($input['password']);
        } else {
            $input = array_except($input, array('password'));
        }

        $user = User::find($id);
        $user->update($input);
        DB::table('model_has_roles')->where('model_id', $id)->delete();

        $user->assignRole($request->input('roles'));

        // === Update cabang ===
        $user->cabangs()->detach();
        if ($request->filled('cabang')) {
            $user->cabangs()->attach(
                $request->input('cabang'),
                ['KODE_LOKASI' => $request->input('kode_lokasi')]
            );
        }

        // === Update merchant ===
        $user->merchants()->detach();
        if ($request->filled('merchants')) {
            $user->merchants()->attach($request->input('merchants'));
        }

        return redirect()->route('users.index')
            ->with('success', 'User updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        User::find($id)->delete();
        return redirect()->route('users.index')
            ->with('success', 'User deleted successfully');
    }

    public function formChangePassword()
    {
        $user = Auth::user();
        return view('users.changePassword', compact('user'));
    }

    public function changePassword(Request $request)
    {
        // Validate the input
        $request->validate([
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:8|confirmed',
        ], [
            'new_password.min' => 'Minimal password 8 karakter',
            'new_password.confirmed' => 'Password dan konfirmasi password tidak cocok.',
        ]);

        // Check if the current password matches
        if (!Hash::check($request->current_password, Auth::user()->password)) {
            return back()->withErrors(['current_password' => 'Password tidak sesuai']);
        }
        $user_id = Crypt::decryptString($request->user_id);
        $user = DB::table('users')
            ->where('id', $user_id)
            ->update([
                'password' => bcrypt($request->new_password),
                'updated_at' => now()->format('Y-m-d H:i:s')
            ]);

        if ($user) {
            return back()->with('status', 'Password berhasil direset');
        } else {
            return back()->withErrors(['email' => 'Password gagal direset']);
        }
    }
}