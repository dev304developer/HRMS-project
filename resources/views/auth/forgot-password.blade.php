<x-guest-layout>
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8">
        <h2 class="text-2xl font-bold text-gray-900">Forgot password?</h2>
        <p class="mt-1 text-sm text-gray-500">
            Enter your email and we'll send you a link to reset your password.
        </p>

        {{-- Session Status --}}
        <x-auth-session-status class="mt-4" :status="session('status')" />

        <form method="POST" action="{{ route('password.email') }}" class="mt-6 space-y-5">
            @csrf

            <div>
                <x-input-label for="email" :value="__('Email')" />
                <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autofocus placeholder="you@company.com" />
                <x-input-error :messages="$errors->get('email')" class="mt-2" />
            </div>

            <button type="submit"
                    class="w-full flex justify-center items-center px-4 py-2.5 rounded-lg text-sm font-semibold text-white hover:opacity-90 transition focus:outline-none focus:ring-2 focus:ring-offset-2"
                    style="background-color:#2f80ed;">
                {{ __('Email Password Reset Link') }}
            </button>

            <p class="text-center text-sm text-gray-500">
                <a href="{{ route('login') }}" class="font-medium hover:underline" style="color:#2f80ed;">&larr; Back to sign in</a>
            </p>
        </form>
    </div>
</x-guest-layout>
