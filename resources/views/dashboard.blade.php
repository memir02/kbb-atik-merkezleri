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
                  <ul class="dropdown-menu">
                    @forelse($ratings as $rating)
                        @if($rating->comment)
                            <li class="dropdown-item">
                                {{ $rating->atikMerkezi->title ?? 'Merkez' }}:
                                <strong>{{ $rating->comment }}</strong>
                            </li>
                        @endif
                    @empty
                        <li class="dropdown-item text-muted">Henüz yorum yapmadınız.</li>
                    @endforelse
                  </ul>
                </div>
                <div class="p-6" style="color: #ffffff;">
                  <button class="btn btn-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                      {{ __("Puanlarım") }}
                  </button>
                  <ul class="dropdown-menu">
                    @forelse($ratings as $rating)
                      <li class="dropdown-item">
                      {{ $rating->atikMerkezi->title ?? 'Merkez' }}:
                        <strong>
                            {{ str_repeat('⭐', $rating->rating) }}
                        </strong>
                      </li>
                    @empty
                      <li class="dropdown-item text-muted">Henüz puan vermediniz.</li>
                    @endforelse
                  </ul>
                </div>
                <div class="p-6" style="color: #ffffff;">
                  <button class="btn btn-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                      {{ __("Favori Atık Merkezlerim") }}                    
                  </button>
                  <ul class="dropdown-menu">
                    @forelse($favoriMerkezler as $favorite)
                      <li class="dropdown-item">
                      {{ $favorite->atikMerkezi->title ?? 'Merkez' }}
                      </li>
                    @empty
                      <li class="dropdown-item text-muted">Henüz favori atık merkezi eklememişsiniz.</li>
                    @endforelse
                  </ul>
            </div>
            </div>
        </div>
    </div>
    <script>
        document.title = "Kullanıcı Panelim - Atık Merkezleri";
    </script>
</x-app-layout>
