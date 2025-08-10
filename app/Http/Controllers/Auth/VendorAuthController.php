<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Vendor;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Auth\Events\Registered;

class VendorAuthController extends Controller
{
    /**
     * Show the vendor registration form.
     */
    public function showRegistrationForm()
    {
        return view('auth.vendor-register');
    }

    /**
     * Handle a vendor registration request.
     */
    public function register(Request $request)
    {
        $validator = $this->validator($request->all());

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Create user
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        // Create vendor profile
        $vendor = Vendor::create([
            'user_id' => $user->id,
            'name' => $request->store_name,
            'slug' => Str::slug($request->store_name),
            'status' => 'pending',
            'commission_rate' => config('app.default_commission_rate', 10.00),
        ]);

        // Assign vendor role to user
        $user->roles()->attach(config('roles.vendor_id'));

        // Send verification email
        event(new Registered($user));

        // Redirect with success message
        return redirect()->route('login')
            ->with('success', 'تم إنشاء حساب البائع بنجاح. يرجى الانتظار حتى يتم مراجعة طلبك من قبل المسؤول.');
    }

    /**
     * Show the vendor login form.
     */
    public function showLoginForm()
    {
        return view('auth.vendor-login');
    }

    /**
     * Handle a vendor login request.
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (auth()->attempt($credentials)) {
            $request->session()->regenerate();

            $user = auth()->user();

            // Check if user has vendor role
            if (!$user->roles()->where('id', config('roles.vendor_id'))->exists()) {
                auth()->logout();
                return back()
                    ->withErrors(['email' => 'هذا الحساب ليس لديه صلاحية بائع.'])
                    ->onlyInput('email');
            }

            // Check if vendor is approved
            $vendor = $user->vendor;
            if (!$vendor || $vendor->status !== 'active') {
                auth()->logout();
                return back()
                    ->withErrors(['email' => 'حساب البائع الخاص بك لم يتم الموافقة عليه بعد.'])
                    ->onlyInput('email');
            }

            return redirect()->intended(route('vendor.dashboard'));
        }

        return back()
            ->withErrors([
                'email' => 'بيانات الاعتماد التي أدخلتها غير صحيحة.',
            ])
            ->onlyInput('email');
    }

    /**
     * Log the user out of the application.
     */
    public function logout(Request $request)
    {
        auth()->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('vendor.login');
    }

    /**
     * Get a validator for an incoming registration request.
     */
    protected function validator(array $data)
    {
        return Validator::make($data, [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'store_name' => ['required', 'string', 'max:255', 'unique:vendors,name'],
            'store_description' => ['nullable', 'string'],
            'phone' => ['nullable', 'string', 'max:20'],
            'address' => ['nullable', 'string', 'max:255'],
        ]);
    }
}
