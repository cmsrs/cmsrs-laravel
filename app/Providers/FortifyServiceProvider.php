<?php

declare(strict_types=1);

namespace App\Providers;

use App\Actions\Fortify\CreateNewUser;
use App\Actions\Fortify\ResetUserPassword;
use App\Actions\Fortify\UpdateUserPassword;
use App\Actions\Fortify\UpdateUserProfileInformation;
use App\Models\User;
use App\Services\Cmsrs\ConfigService;
use App\Services\Cmsrs\Helpers\RequestService;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Laravel\Fortify\Actions\AttemptToAuthenticate;
use Laravel\Fortify\Actions\CanonicalizeUsername;
use Laravel\Fortify\Actions\EnsureLoginIsNotThrottled;
use Laravel\Fortify\Actions\PrepareAuthenticatedSession;
use Laravel\Fortify\Contracts\LoginResponse;
use Laravel\Fortify\Contracts\LogoutResponse;
use Laravel\Fortify\Contracts\RegisterResponse;
use Laravel\Fortify\Fortify;

class FortifyServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->instance(LogoutResponse::class, new class implements LogoutResponse
        {
            public function toResponse($request)
            {
                // if (! env('IS_LOGIN', true)) {
                if (! config('cmsrs.features.login')) {
                    abort(404);
                }

                $configService = new ConfigService;
                if (! $configService->isManyLangs()) {
                    return redirect('/');
                }

                $lang = app(RequestService::class)->getLangFromCookie();
                App::setLocale($lang);
                $redirect = ($lang == $configService->getDefaultLang()) ? '/' : '/'.$lang;

                return redirect($redirect);
            }
        });

        $this->app->instance(LoginResponse::class, new class implements LoginResponse
        {
            public function toResponse($request)
            {
                // if (! env('IS_LOGIN', true)) {
                if (! config('cmsrs.features.login')) {
                    abort(404);
                }

                $configService = new ConfigService;
                if (! $configService->isManyLangs()) {
                    return redirect()->route('home');
                }

                $lang = app(RequestService::class)->getLangFromCookie();
                App::setLocale($lang);

                return redirect()->route('home', ['lang' => $lang]);
            }
        });

        $this->app->instance(RegisterResponse::class, new class implements RegisterResponse
        {
            public function toResponse($request)
            {
                if (! config('cmsrs.features.register')) {
                    // if (! env('IS_REGISTER', true)) {
                    abort(404);
                }

                $configService = new ConfigService;
                if (! $configService->isManyLangs()) {
                    return redirect()->route('home');
                }

                $lang = app(RequestService::class)->getLangFromCookie();
                App::setLocale($lang);

                return redirect()->route('home', ['lang' => $lang]);
            }
        });

    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Fortify::createUsersUsing(CreateNewUser::class);
        Fortify::updateUserProfileInformationUsing(UpdateUserProfileInformation::class);
        Fortify::updateUserPasswordsUsing(UpdateUserPassword::class);
        Fortify::resetUserPasswordsUsing(ResetUserPassword::class);

        RateLimiter::for('login', function (Request $request) {
            $throttleKey = Str::transliterate(Str::lower($request->input(Fortify::username())).'|'.$request->ip());

            return Limit::perMinute(5)->by($throttleKey);
        });

        // RateLimiter::for('two-factor', function (Request $request) {
        //     return Limit::perMinute(5)->by($request->session()->get('login.id'));
        // });

        Fortify::loginView(function () {
            // if (! env('IS_LOGIN', true)) {
            if (! config('cmsrs.features.login')) {
                abort(404);
            }

            return view('auth.login');
        });

        Fortify::registerView(function () {
            // This condition is redundant because it is already defined in the configuration - but it is a good practice to check it
            // if (! env('IS_REGISTER', true)) {
            if (! config('cmsrs.features.register')) {
                abort(404);
            }

            return view('auth.register');
        });

        Fortify::authenticateUsing(function (Request $request) {
            // if (! env('IS_LOGIN', true)) {
            if (! config('cmsrs.features.login')) {
                abort(404);
            }

            $user = User::where('email', $request->email)->first();

            if ($user &&
                Hash::check($request->password, $user->password)) {

                return $user;
            }
        });

        Fortify::authenticateThrough(function (Request $request) {
            return array_filter([
                config('fortify.limiters.login') ? null : EnsureLoginIsNotThrottled::class,
                config('fortify.lowercase_usernames') ? CanonicalizeUsername::class : null,
                // Features::enabled(Features::twoFactorAuthentication()) ? RedirectIfTwoFactorAuthenticatable::class : null,
                AttemptToAuthenticate::class,
                PrepareAuthenticatedSession::class,
            ]);
        });

    }
}
