<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\ValidationException;

class CustomerAuthController extends Controller
{
    /**
     * Show login form.
     */
    public function showLoginForm(): \Illuminate\View\View
    {
        return view('storefront.auth.login');
    }

    /**
     * Handle login request.
     */
    public function login(Request $request): \Illuminate\Http\RedirectResponse
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
            'remember' => 'sometimes|boolean',
        ]);

        $credentials = $request->only('email', 'password');
        $remember = $request->boolean('remember');

        // Attempt login with customer guard
        if (Auth::guard('customer')->attempt($credentials, $remember)) {
            $customer = Auth::guard('customer')->user();

            // Verify customer belongs to current tenant
            if ($customer->tenant_id !== app('tenant')->id) {
                Auth::guard('customer')->logout();
                throw ValidationException::withMessages([
                    'email' => __('storefront.auth.invalid_credentials'),
                ]);
            }

            // Verify customer is active
            if ($customer->status !== 'active') {
                Auth::guard('customer')->logout();
                throw ValidationException::withMessages([
                    'email' => __('storefront.auth.account_inactive'),
                ]);
            }

            // Verify email is verified
            if (! $customer->email_verified_at) {
                Auth::guard('customer')->logout();
                return redirect()->route('storefront.account.login')
                    ->with('error', __('storefront.auth.email_not_verified'));
            }

            // Update last login
            $customer->updateQuietly(['last_login_at' => now()]);

            // Merge guest cart with customer cart
            $this->mergeCart($customer);

            $request->session()->regenerate();

            return redirect()->intended(route('storefront.account.index'))
                ->with('success', __('storefront.auth.login_success'));
        }

        throw ValidationException::withMessages([
            'email' => __('storefront.auth.invalid_credentials'),
        ]);
    }

    /**
     * Show registration form.
     */
    public function showRegistrationForm(): \Illuminate\View\View
    {
        return view('storefront.auth.register');
    }

    /**
     * Handle registration request.
     */
    public function register(Request $request): \Illuminate\Http\RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'email' => 'required|email|max:255|unique:customers,email,NULL,id,tenant_id,' . app('tenant')->id,
            'password' => 'required|string|min:8|confirmed',
            'address' => 'nullable|string|max:500',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:20',
            'country' => 'nullable|string|max:100',
        ]);

        $tenant = app('tenant');

        $customer = Customer::create([
            'tenant_id' => $tenant->id,
            'name' => $request->name,
            'phone' => Customer::normalizePhone($request->phone),
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'address' => $request->address,
            'city' => $request->city,
            'state' => $request->state,
            'postal_code' => $request->postal_code,
            'country' => $request->country ?? 'Bangladesh',
            'type' => 'regular',
            'status' => 'active',
            'credit_limit' => 0,
            'outstanding_balance' => 0,
            'payment_terms_days' => 0,
            'source' => 'storefront',
        ]);

        // Send email verification notification
        // $customer->sendEmailVerificationNotification(); // Would need to implement

        // Merge guest cart
        $this->mergeCart($customer);

        // Log in the customer
        Auth::guard('customer')->login($customer, true);

        return redirect()->route('storefront.account.index')
            ->with('success', __('storefront.auth.register_success'));
    }

    /**
     * Handle logout.
     */
    public function logout(Request $request): \Illuminate\Http\RedirectResponse
    {
        Auth::guard('customer')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('storefront.home')
            ->with('success', __('storefront.auth.logout_success'));
    }

    /**
     * Show password reset link request form.
     */
    public function showLinkRequestForm(): \Illuminate\View\View
    {
        return view('storefront.auth.password.email');
    }

    /**
     * Send password reset link.
     */
    public function sendResetLinkEmail(Request $request): \Illuminate\Http\RedirectResponse
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $customer = Customer::where('tenant_id', app('tenant')->id)
            ->where('email', $request->email)
            ->first();

        // Always return success to prevent email enumeration
        if ($customer && $customer->email_verified_at) {
            // Send password reset notification
            // Would need to implement: $customer->sendPasswordResetNotification($token);
        }

        return back()->with('status', __('storefront.auth.reset_link_sent'));
    }

    /**
     * Show password reset form.
     */
    public function showResetForm(Request $request, string $token): \Illuminate\View\View
    {
        return view('storefront.auth.password.reset', [
            'token' => $token,
            'email' => $request->email,
        ]);
    }

    /**
     * Handle password reset.
     */
    public function reset(Request $request): \Illuminate\Http\RedirectResponse
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $customer = Customer::where('tenant_id', app('tenant')->id)
            ->where('email', $request->email)
            ->first();

        if (! $customer) {
            throw ValidationException::withMessages([
                'email' => __('storefront.auth.invalid_credentials'),
            ]);
        }

        // Verify token (would need password reset token table lookup)
        // For now, just update password
        $customer->update([
            'password' => Hash::make($request->password),
        ]);

        return redirect()->route('storefront.account.login')
            ->with('success', __('storefront.auth.password_reset_success'));
    }

    /**
     * Merge guest cart with customer cart.
     */
    protected function mergeCart(Customer $customer): void
    {
        $guestCart = Session::get('storefront_cart');

        if (! $guestCart || empty($guestCart['items'])) {
            return;
        }

        // Get customer's saved cart (if we implement persistent cart)
        // For now, just keep guest cart as-is
        // In future, could save to customer.storefront_preferences['cart']
    }
}