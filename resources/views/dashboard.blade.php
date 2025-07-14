<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Kullanıcı Panelim') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="overflow-hidden shadow-sm sm:rounded-lg" style="background-color: #2c2f33;">
                <div class="p-6" style="color: #ffffff;">
                  <button class="btn btn-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                      {{ __("Yorumlarım") }}
                  </button>
                </div>
                <div class="p-6" style="color: #ffffff;">
                  <button class="btn btn-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                      {{ __("Puanlarım") }}
                  </button>
                </div>
                <div class="p-6" style="color: #ffffff;">
                  <button class="btn btn-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                      {{ __("Favori Atık Merkezlerim") }}
                  </button>
            </div>
            </div>
        </div>
    </div>
</x-app-layout>
