<section>
    <header>
        <h2 class="text-lg font-medium text-gray-900">
            {{ __('プロフィール情報') }}
        </h2>

        <p class="mt-1 text-sm text-gray-600">
            {{ __("アカウントのプロフィール情報とメールアドレスを更新します。") }}
        </p>
    </header>

    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <form method="post" action="{{ route('profile.update') }}" class="mt-6 space-y-6">
        @csrf
        @method('patch')

        <div>
            <x-input-label for="name" :value="__('名前')" />
            <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name', $user->name)" required autofocus autocomplete="name" />
            <x-input-error class="mt-2" :messages="$errors->get('name')" />
        </div>

        <div>
            <x-input-label for="email" :value="__('メールアドレス')" />
            <x-text-input id="email" name="email" type="email" class="mt-1 block w-full" :value="old('email', $user->email)" required autocomplete="username" />
            <x-input-error class="mt-2" :messages="$errors->get('email')" />

            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                <div>
                    <p class="text-sm mt-2 text-gray-800">
                        {{ __('あなたのメールアドレスは未確認です。') }}

                        <button form="send-verification" class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            {{ __('確認メールを再送信するにはここをクリックしてください。') }}
                        </button>
                    </p>

                    @if (session('status') === 'verification-link-sent')
                        <p class="mt-2 font-medium text-sm text-green-600">
                            {{ __('新しい確認リンクがあなたのメールアドレスに送信されました。') }}
                        </p>
                    @endif
                </div>
            @endif
        </div>

        <div>
            <x-input-label for="color" :value="__('ユーザーカラー')" />
            <input id="color" name="color" type="color" class="mt-1 block w-24 h-10" value="{{ old('color', $user->color) }}" required />
            <x-input-error class="mt-2" :messages="$errors->get('color')" />
        </div>

        <div>
            <x-input-label for="line_notifications" :value="__('LINE通知を有効にする(明日の予定をお知らせします)')" />
            <input id="line_notifications" name="line_notifications" type="checkbox" class="mt-1 block" {{ old('line_notifications', $user->line_notifications) ? 'checked' : '' }} />
            <x-input-error class="mt-2" :messages="$errors->get('line_notifications')" />
        </div>

        <div>
            <x-input-label for="notification_time" :value="__('通知時間')" />
            <input id="notification_time" name="notification_time" type="time" class="mt-1 block w-full" value="{{ old('notification_time', $user->notification_time ? date('H:i', strtotime($user->notification_time)) : '20:00') }}" required />
            <x-input-error class="mt-2" :messages="$errors->get('notification_time')" />
        </div>

        <div class="flex items-center gap-4">
            <x-primary-button>{{ __('保存') }}</x-primary-button>

            @if (session('status') === 'profile-updated')
                <p
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 2000)"
                    class="text-sm text-gray-600"
                >{{ __('保存されました。') }}</p>
            @endif
        </div>
    </form>

    <div class="mt-6">
        <h2 class="text-lg font-medium text-gray-900">
            {{ __('※通知を受け取るには、LINE公式アカウントの友達追加が必要です') }}
        </h2>
        <p class="mt-1 text-sm text-gray-600">
            {{ __("以下のリンクをクリックして公式LINEアカウントを友達追加してください。") }}
        </p>
        <div class="mt-4">
            <a href="https://line.me/R/ti/p/%40918xvqxc" target="_blank" class="text-indigo-600 hover:text-indigo-900 underline">
                {{ __('友達追加はこちら') }}
            </a>
        </div>
        <div class="mt-4">
            <img src="https://qr-official.line.me/sid/L/918xvqxc.png" alt="友達追加用QRコード" class="w-32 h-32">
        </div>
    </div>
</section>