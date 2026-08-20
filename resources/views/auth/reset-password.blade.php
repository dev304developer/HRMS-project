<x-guest-layout>
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8">
        <h2 class="text-2xl font-bold text-gray-900">Reset password</h2>
        <p class="mt-1 text-sm text-gray-500">Choose a new password for your account.</p>

        <form method="POST" action="{{ route('password.store') }}" class="mt-6 space-y-5">
            @csrf

            <input type="hidden" name="token" value="{{ $request->route('token') }}">

            <div>
                <x-input-label for="email" :value="__('Email')" />
                <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email', $request->email)" required autofocus autocomplete="username" />
                <x-input-error :messages="$errors->get('email')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="password" :value="__('Password')" />
                <x-text-input id="password" class="block mt-1 w-full" type="password" name="password" required autocomplete="new-password" placeholder="••••••••" />
                <x-input-error :messages="$errors->get('password')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="password_confirmation" :value="__('Confirm Password')" />
                <x-text-input id="password_confirmation" class="block mt-1 w-full" type="password" name="password_confirmation" required autocomplete="new-password" placeholder="••••••••" />
                <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
            </div>

            <button type="submit"
                    class="w-full flex justify-center items-center px-4 py-2.5 rounded-lg text-sm font-semibold text-white hover:opacity-90 transition focus:outline-none focus:ring-2 focus:ring-offset-2"
                    style="background-color:#2f80ed;">
                {{ __('Reset Password') }}
            </button>
        </form>
    </div>
</x-guest-layout>
