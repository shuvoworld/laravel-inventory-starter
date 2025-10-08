<x-layouts.auth :title="__('Login')">
    <!-- Login Card -->
    <div
        class="bg-white dark:bg-gray-800 rounded-lg shadow-md border border-gray-200 dark:border-gray-700 overflow-hidden">
        <div class="p-6">
            <div class="mb-3">
                <h1 class="text-2xl font-bold text-gray-800 dark:text-gray-100">{{ __('Log in to your account') }}</h1>
            </div>

            <!-- Demo account helper -->
            <div class="mb-4 text-sm space-y-2">
                <!-- Store Admin Demo -->
                <div class="p-3 rounded border border-purple-200 bg-purple-50 text-purple-900 dark:bg-purple-900/20 dark:border-purple-800 dark:text-purple-200">
                    <div class="flex items-start justify-between gap-2">
                        <div>
                            <div class="font-medium flex items-center gap-2">
                                <i class="fas fa-user-shield"></i>
                                Store Admin Demo
                            </div>
                            <div>Email: <code class="select-all">admin@demo.com</code></div>
                            <div>Password: <code class="select-all">password</code></div>
                        </div>
                        <button type="button" data-email="admin@demo.com" data-password="password" class="fill-demo-btn inline-flex items-center gap-1 px-2 py-1 rounded bg-purple-600 text-white hover:bg-purple-700 text-xs">
                            Use Admin
                        </button>
                    </div>
                </div>

                <!-- Store User Demo -->
                <div class="p-3 rounded border border-blue-200 bg-blue-50 text-blue-900 dark:bg-blue-900/20 dark:border-blue-800 dark:text-blue-200">
                    <div class="flex items-start justify-between gap-2">
                        <div>
                            <div class="font-medium flex items-center gap-2">
                                <i class="fas fa-user"></i>
                                Store User Demo
                            </div>
                            <div>Email: <code class="select-all">user@demo.com</code></div>
                            <div>Password: <code class="select-all">password</code></div>
                        </div>
                        <button type="button" data-email="user@demo.com" data-password="password" class="fill-demo-btn inline-flex items-center gap-1 px-2 py-1 rounded bg-blue-600 text-white hover:bg-blue-700 text-xs">
                            Use User
                        </button>
                    </div>
                </div>
            </div>

            <form method="POST" action="{{ route('login') }}" class="space-y-3" id="login-form">
                @csrf
                <!-- Email Input -->
                <div>
                    <x-forms.input label="Email" name="email" type="email" placeholder="your@email.com" autofocus />
                </div>

                <!-- Password Input -->
                <div>
                    <x-forms.input label="Password" name="password" type="password" placeholder="••••••••" />

                    <!-- Remember me & password reset -->
                    <div class="flex items-center justify-between mt-2">
                        @if (Route::has('password.request'))
                            <a href="{{ route('password.request') }}"
                                class="text-xs text-blue-600 dark:text-blue-400 hover:underline">{{ __('Forgot password?') }}</a>
                        @endif
                        <x-forms.checkbox label="Remember me" name="remember" />
                    </div>
                </div>

                <!-- Login Button -->
                <x-button type="primary" class="w-full">{{ __('Sign In') }}</x-button>
            </form>

            @if (Route::has('register'))
                <!-- Register Link -->
                <div class="text-center mt-6">
                    <p class="text-sm text-gray-600 dark:text-gray-400">
                        {{ __('Don\'t have an account?') }}
                        <a href="{{ route('register') }}"
                            class="text-blue-600 dark:text-blue-400 hover:underline font-medium">{{ __('Sign up') }}</a>
                    </p>
                </div>
            @endif
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function(){
            const btns = document.querySelectorAll('.fill-demo-btn');
            btns.forEach(btn => {
                btn.addEventListener('click', function(){
                    const email = document.querySelector('input[name="email"]');
                    const password = document.querySelector('input[name="password"]');
                    if (email) email.value = this.dataset.email;
                    if (password) password.value = this.dataset.password;
                    email?.focus();
                });
            });
        });
    </script>
</x-layouts.auth>
