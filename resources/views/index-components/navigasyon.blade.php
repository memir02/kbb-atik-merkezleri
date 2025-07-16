<!-- Arama ve Navigasyon Bölümü -->
<div class="navigation-section bg-light py-4">
    <div class="container">
        <!-- Arama Barı -->
        <div class="row justify-content-center mb-4">
            <div class="col-12 col-md-8">
                <form method="GET" action="{{ route('atik-merkezleri.index') }}" class="search-form">
                    <div class="input-group input-group-lg">
                        <input type="text" 
                               class="form-control" 
                               name="search" 
                               placeholder="Atık Merkezi Bul"
                               value="{{ request('search') }}">
                        <button class="btn btn-outline-primary" type="submit">
                            <i class="fas fa-search me-1"></i> Ara
                        </button>
                        @if(request('search'))
                            <a href="{{ route('atik-merkezleri.index') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-times"></i>
                            </a>
                        @endif
                    </div>
                </form>
            </div>
        </div>
        
        <!-- Aksiyon Butonları -->
        <div class="row justify-content-center">
            <div class="col-12 col-md-8">
                <div class="d-flex justify-content-center gap-3 flex-wrap">
                    <button type="button" 
                            class="btn btn-primary btn-lg" 
                            data-bs-toggle="modal" 
                            data-bs-target="#filtreModal">
                        <i class="fas fa-filter me-1"></i> FİLTRELE
                    </button>
                    <button type="button" id="konuma-gore-ara" class="btn btn-success btn-lg">
                        <i class="fas fa-location-arrow me-1"></i> KONUMUMA GÖRE BUL
                    </button>
                    <button type="button" 
                            class="btn btn-danger btn-lg"
                            data-bs-toggle="modal" 
                            data-bs-target="#youtubeModal">
                            <i class="fab fa-youtube"></i>
                    </button>
                    @if(request('filter'))
                        <a href="{{ route('atik-merkezleri.index') }}" class="btn btn-outline-danger btn-lg">
                            <i class="fas fa-times me-1"></i> Filtreyi Temizle
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div> 