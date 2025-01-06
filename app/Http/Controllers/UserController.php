<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;

class UserController extends Controller
{


  

    public function __construct()
    {
       
    }

    public function index()
    {
        return view('users.index');
    }

    public function getData()
    {
        $lastViewed = session()->get('last_viewed');
        // create the request variables for the filters
        $name = \request()->input('filters.name');
        $status = \request()->input('filters.status');
        $role = \request()->input('filters.roles');

        $users = User::query()->with('employee')
            ->where('id', '!=', auth()->user()->id)
            ->whereHas('roles', function ($query) {
                $query->where('name', '!=', 'super admin');
            })
            ->orderByDesc('updated_at');
        if(auth()->user()->hasRole("Gestionnaire") || auth()->user()->hasRole("Agent") || auth()->user()->hasRole("Service traitante")  )
            $users = User::query()->with('employee')->where('id',auth()->user()->id);
        else
            $users = User::query()->with('employee')
            ->whereHas('roles', function ($query) {
                $query->where('name', '!=', 'super admin');
            })
            ->orderByDesc('updated_at');

        //dd(\request()->all());

        if ($status !== null) {
           $users->whereHas('employee', function ($query) use ($status) {

               $query->where('status', (int)$status);
            });
        }

        if ($role) {
            $users->whereHas('roles', function ($query) use ($role) {
                $query->where('id', $role);
            });
        }

//        dd(\request()->all());

        if ($name) {
            $users->where('name', 'like', '%' . $name . '%');
        }

        return DataTables::of($users)
            ->addColumn('actions', function ($user) {
                //if ($user->id != auth()->user()->id)
                    return view('users.action', compact('user'));
            })
            ->editColumn('roles', function ($user) {
                return '<span class="badge bg-primary">' . $user->getRoleNames()->implode(', ') . '</span>';
            })
            ->editColumn('phone', function ($user) {
                return $user->employee->phone;
            })
            ->editColumn('status', function ($user) {
                return view('users.status', compact('user'));
            })
            ->addColumn('photo', function ($user) {
                return view('users.profile-image', compact('user'));
            })
            ->addIndexColumn()
            ->rawColumns(['actions', 'roles', 'photo'])
            ->make(true);
    }

    public function create()
    {
        if (auth()->user()->hasRole('Super admin'))
            $roles = Role::all();
        else
            $roles = Role::where('name', '!=', 'Super admin')->get();
        return view('users.create', compact('roles'));
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'name' => 'required',
            'email' => 'required|email|unique:users,email',
            'gender' => 'required',
            'delta' => 'required',
            'agence' => 'required',
            'roles' => 'required'
        ]);

        $input = $request->all();
        $input['password'] = Hash::make('DELTA');

        $user = new User () ;
        $user->nomComplet = $request->name;
        $user->email = $request->email;
        $user->etat = 1;
        $user->username = $request->delta;
        $user->agence_id = $request->agence;
        $user->password = Hash::make('DELTA');
        $user->save();
        $user->assignRole($request->input('roles'));
        $employee = new Employee();
        $employee->user_id = $user->id;
        $employee->gender = $request->input('gender');
        $employee->save();

        return redirect()->route('users.index')
            ->with('success', 'User created successfully');
    }

    public function show($id)
    {
        $user = User::find($id);
        return view('users.show', compact('user'));
    }

    public function edit($id)
    {
        $user = User::find($id);
        $roles = Role::all();
        $userRole = $user->roles->all();
        return view('users.edit', compact('user', 'roles', 'userRole'));
    }

    public function update(Request $request, $id)
    {
        $this->validate($request, [
            'nomComplet' => 'required',
            'email' => 'required|email|unique:users,email,' . $id,
                'roles' => Rule::requiredIf(function () use ($request) {
                    return $request->input('roles');
                }),
            'phone' => Rule::unique('employees', 'phone')
                ->whereNotNull('phone')
                ->ignore($id, 'user_id'),
            'nni' => Rule::unique('employees', 'nni')
                ->whereNotNull('nni')
                ->ignore($id, 'user_id'),
        ]);
        // DB::beginTransaction();
        // try {
            $input = $request->all();
            $user = User::find($id);
            $user->update($input);
            $employee = Employee::where('user_id', $id)->first();
            $employee->phone = $request->input('phone');
            $employee->nni = $request->input('nni');
            $employee->address = $request->input('address');
            $employee->gender = $request->input('gender');
            $employee->about = $request->input('about');
            if ($request->hasFile('photo')) {
                $file = $request->file('photo');
                $fileName = time() . '.' . $file->getClientOriginalExtension();
                $file->move(public_path('assets/img'), $fileName);
                $employee->photo = 'assets/img/' . $fileName;
            }
            $employee->save();
           // if(auth()->user()->hasRole('Admin') )

            if (!$request->ajax() ) {
                
                DB::table('model_has_roles')->where('model_id', $id)->delete();
                $user->assignRole($request->input('roles'));
            }
           // DB::commit();
            return redirect()->route('users.index')
                    ->with('success', 'User updated successfully');

        // } catch (\Exception $e) {
        //     DB::rollBack();
        //     return redirect()->route('users.index')
        //         ->with('error', 'User updated failed');
        // }
    }

    public function destroy($id)
    {
        User::find($id)->delete();
        return response()->json('User deleted successfully', 200);
    }

    public function resetPassword($id)
    {
        return view('users.reset-password', [
            'user' => User::find($id)
        ]);
    }

    public function updatePassword(Request $request, $id)
    {
        $this->validate($request, [
            'password' => 'required|confirmed'
        ]);
        $user = User::find($id);
        if ($request->has('old_password')) {
            if (!Hash::check($request->input('old_password'), $user->password)) {
                return response()->json([
                    'message' => 'Old password is not correct'
                ], 422);
            }
        }

        $user->password = $request->input('password');
        $user->save();

       if ($request->ajax()) {
            return response()->json([
                'success' => 'Password updated successfully'
            ], 201);
        }
        return redirect()->route('users.index')
            ->with('success', 'Password updated successfully');
    }

    public function profile($id)
    {
        $user = User::find($id);
        return view('users.profile', compact('user'));
    }

    public function block($id)
    {
        $employee = Employee::where('user_id', $id)->first();
        $employee->status = !$employee->status;
        $employee->save();
        return redirect()->back()->with([
            'success' => 'User status updated successfully'
        ], 201);
    }

    public function authenticate(Request $request)
    {
        
        $this->validate($request, [
            'email' => 'required',
            'password' => 'required']);
        //$input = $request->all();
        // $fieldType = filter_var($request->email, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';
        //     dd($fieldType);
        //$remember = ($request->remember == 'on') ? true : false;
            
            if (Auth::attempt(["email" => $request->email,'password' => $request->password])) {     
                    return redirect()->route('home');
            }else{
                return redirect()->route('login')
                    ->with('error','Nom utilisateur ou mots de passe incorrecte.');
                     
            } 
    }
}
