<x-guest-layout>
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8">
        <h2 class="text-2xl font-bold text-gray-900">Create your account</h2>
        <p class="mt-1 text-sm text-gray-500">Join {{ config('app.name', 'HRMS') }} to get started</p>

        <form method="POST" action="{{ route('register') }}" class="mt-6 space-y-5">
            @csrf

            {{-- Name --}}
            <div>
                <x-input-label for="name" :value="__('Name')" />
                <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name')" required autofocus autocomplete="name" placeholder="Your full name" />
                <x-input-error :messages="$errors->get('name')" class="mt-2" />
            </div>

            {{-- Email --}}
            <div>
                <x-input-label for="email" :value="__('Email')" />
                <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autocomplete="username" placeholder="you@company.com" />
                <x-input-error :messages="$errors->get('email')" class="mt-2" />
            </div>

            {{-- Password --}}
            <div>
                <x-input-label for="password" :value="__('Password')" />
                <x-text-input id="password" class="block mt-1 w-full" type="password" name="password" required autocomplete="new-password" placeholder="••••••••" />
                <x-input-error :messages="$errors->get('password')" class="mt-2" />
            </div>

            {{-- Confirm Password --}}
            <div>
                <x-input-label for="password_confirmation" :value="__('Confirm Password')" />
                <x-text-input id="password_confirmation" class="block mt-1 w-full" type="password" name="password_confirmation" required autocomplete="new-password" placeholder="••••••••" />
                <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
            </div>

            <button type="submit"
                    class="w-full flex justify-center items-center px-4 py-2.5 rounded-lg text-sm font-semibold text-white hover:opacity-90 transition focus:outline-none focus:ring-2 focus:ring-offset-2"
                    style="background-color:#2f80ed;">
                {{ __('Register') }}
            </button>

            <p class="text-center text-sm text-gray-500">
                {{ __('Already have an account?') }}
                <a href="{{ route('login') }}" class="font-medium hover:underline" style="color:#2f80ed;">Sign in</a>
            </p>
        </form>
    </div>
</x-guest-layout>
