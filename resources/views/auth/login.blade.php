<x-guest-layout>
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8">
        <h2 class="text-2xl font-bold text-gray-900">Welcome back</h2>
        <p class="mt-1 text-sm text-gray-500">Sign in to your {{ config('app.name', 'HRMS') }} account</p>

        {{-- Session Status --}}
        <x-auth-session-status class="mt-4" :status="session('status')" />

        <form method="POST" action="{{ route('login') }}" class="mt-6 space-y-5">
            @csrf

            {{-- Email --}}
            <div>
                <x-input-label for="email" :value="__('Email')" />
                <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" placeholder="you@company.com" />
                <x-input-error :messages="$errors->get('email')" class="mt-2" />
            </div>

            {{-- Password --}}
            <div>
                <x-input-label for="password" :value="__('Password')" />
                <x-text-input id="password" class="block mt-1 w-full" type="password" name="password" required autocomplete="current-password" placeholder="••••••••" />
                <x-input-error :messages="$errors->get('password')" class="mt-2" />
            </div>

            {{-- Remember + Forgot --}}
            <div class="flex items-center justify-between">
                <label for="remember_me" class="inline-flex items-center">
                    <input id="remember_me" type="checkbox" class="rounded border-gray-300 shadow-sm focus:ring-2" style="color:#2f80ed;" name="remember">
                    <span class="ms-2 text-sm text-gray-600">{{ __('Remember me') }}</span>
                </label>

                @if (Route::has('password.request'))
                    <a class="text-sm hover:underline" style="color:#2f80ed;" href="{{ route('password.request') }}">
                        {{ __('Forgot password?') }}
                    </a>
                @endif
            </div>

            {{-- Submit --}}
            <button type="submit"
                    class="w-full flex justify-center items-center px-4 py-2.5 rounded-lg text-sm font-semibold text-white hover:opacity-90 transition focus:outline-none focus:ring-2 focus:ring-offset-2"
                    style="background-color:#2f80ed;">
                {{ __('Log in') }}
            </button>

            @if (Route::has('register'))
                <p class="text-center text-sm text-gray-500">
                    Don't have an account?
                    <a href="{{ route('register') }}" class="font-medium hover:underline" style="color:#2f80ed;">Register</a>
                </p>
            @endif
        </form>
    </div>
</x-guest-layout>
