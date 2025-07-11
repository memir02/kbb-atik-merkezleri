<section class="space-y-6">
    <header>
        <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
            {{ __('Hesabımı Sil') }}
        </h2>

        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
            {{ __('Hesabınız silindiğinde, tüm kaynakları ve verileriniz kalıcı olarak silinecektir. Hesabınızı silmeden önce, kalıcı olarak saklamak istediğiniz veri veya bilgileri indiriniz.') }}
        </p>
    </header>

    <x-danger-button
        x-data=""
        x-on:click.prevent="$dispatch('open-modal', 'confirm-user-deletion')"
    >{{ __('Hesabımı Sil') }}</x-danger-button>

    <x-modal name="confirm-user-deletion" :show="$errors->userDeletion->isNotEmpty()" focusable>
        <form method="post" action="{{ route('profile.destroy') }}" class="p-6">
            @csrf
            @method('delete')

            <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                {{ __('Hesabınızı Silmek İstediğinize Emin Misiniz?') }}
            </h2>

            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                {{ __('Hesabınız silindiğinde, tüm kaynakları ve verileriniz kalıcı olarak silinecektir. Lütfen hesabınızı kalıcı olarak silmek istediğinize emin olduğunuzu doğrulamak için şifrenizi giriniz.') }}
            </p>

            <div class="mt-6">
                <x-input-label for="password" value="{{ __('Password') }}" class="sr-only" />

                <x-text-input
                    id="password"
                    name="password"
                    type="password"
                    class="mt-1 block w-3/4"
                    placeholder="{{ __('Password') }}"
                />

                <x-input-error :messages="$errors->userDeletion->get('password')" class="mt-2" />
            </div>

            <div class="mt-6 flex justify-end">
                <x-secondary-button x-on:click="$dispatch('close')">
                    {{ __('İptal') }}
                </x-secondary-button>

                <x-danger-button class="ms-3">
                    {{ __('Hesabımı Sil') }}
                </x-danger-button>
            </div>
        </form>
    </x-modal>
</section>
