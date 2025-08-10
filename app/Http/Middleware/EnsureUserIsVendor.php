
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsVendor
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::check()) {
            return redirect()->route('vendor.login');
        }

        $user = Auth::user();

        // Check if user has vendor role
        if (!$user->roles()->where('id', config('roles.vendor_id'))->exists()) {
            return redirect()->route('home')->with('error', 'ليس لديك صلاحية الوصول إلى هذه الصفحة');
        }

        // Check if vendor is approved
        $vendor = $user->vendor;
        if (!$vendor || $vendor->status !== 'active') {
            return redirect()->route('home')->with('error', 'حساب البائع الخاص بك لم يتم الموافقة عليه بعد');
        }

        return $next($request);
    }
}
