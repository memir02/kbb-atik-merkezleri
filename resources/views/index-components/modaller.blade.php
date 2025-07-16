<!-- Filtreleme Modalı -->
<div class="modal fade" id="filtreModal" tabindex="-1" aria-labelledby="filtreModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title fw-bold" id="filtreModalLabel">Atık Merkezi Türüne Göre Filtrele</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Kapat"></button>
      </div>
      <form method="GET" action="{{ route('atik-merkezleri.index') }}">
        <div class="modal-body">
          <div class="mb-4">
            <div class="row row-cols-1 row-cols-md-2 g-2">
              <div class="col">
                <div class="form-check">
                  <input class="form-check-input" type="checkbox" name="filter[]" value="cam" id="filter-cam">
                  <label class="form-check-label" for="filter-cam">CAM</label>
                </div>
              </div>
              <div class="col">
                <div class="form-check">
                  <input class="form-check-input" type="checkbox" name="filter[]" value="kagit" id="filter-kağıt">
                  <label class="form-check-label" for="filter-kağıt">KAĞIT</label>
                </div>
              </div>
              <div class="col">
                <div class="form-check">
                  <input class="form-check-input" type="checkbox" name="filter[]" value="plastik" id="filter-plastik">
                  <label class="form-check-label" for="filter-plastik">PLASTİK</label>
                </div>
              </div>
              <div class="col">
                <div class="form-check">
                  <input class="form-check-input" type="checkbox" name="filter[]" value="metal" id="filter-metal">
                  <label class="form-check-label" for="filter-metal">METAL</label>
                </div>
              </div>
              <div class="col">
                <div class="form-check">
                  <input class="form-check-input" type="checkbox" name="filter[]" value="pil" id="filter-pil">
                  <label class="form-check-label" for="filter-pil">PİL</label>
                </div>
              </div>
              <div class="col">
                <div class="form-check">
                  <input class="form-check-input" type="checkbox" name="filter[]" value="bitkisel" id="filter-bitkisel">
                  <label class="form-check-label" for="filter-bitkisel">BİTKİSEL ATIK YAĞ</label>
                </div>
              </div>
              <div class="col">
                <div class="form-check">
                  <input class="form-check-input" type="checkbox" name="filter[]" value="atıkcam" id="filter-atıkcam">
                  <label class="form-check-label" for="filter-atıkcam">ATIK CAM</label>
                </div>
              </div>
              <div class="col">
                <div class="form-check">
                  <input class="form-check-input" type="checkbox" name="filter[]" value="tekstil" id="filter-tekstil">
                  <label class="form-check-label" for="filter-tekstil">TEKSTİL KUMBARASI</label>
                </div>
              </div>
              <div class="col">
                <div class="form-check">
                  <input class="form-check-input" type="checkbox" name="filter[]" value="gecici" id="filter-gecici">
                  <label class="form-check-label" for="filter-gecici">ATIK GEÇİCİ DEPOLAMA ÜNİTESİ</label>
                </div>
              </div>
              <div class="col">
                <div class="form-check">
                  <input class="form-check-input" type="checkbox" name="filter[]" value="ilac" id="filter-ilac">
                  <label class="form-check-label" for="filter-ilac">ATIK İLAÇ</label>
                </div>
              </div>
              <div class="col">
                <div class="form-check">
                  <input class="form-check-input" type="checkbox" name="filter[]" value="sinif1" id="filter-sinif1">
                  <label class="form-check-label" for="filter-sinif1">1. SINIF ATIK GETİRME MERKEZİ</label>
                </div>
              </div>
              <div class="col">
                <div class="form-check">
                  <input class="form-check-input" type="checkbox" name="filter[]" value="inert" id="filter-inert">
                  <label class="form-check-label" for="filter-inert">İNERT ATIK</label>
                </div>
              </div>
              <div class="col">
                <div class="form-check">
                  <input class="form-check-input" type="checkbox" name="filter[]" value="hafriyat" id="filter-hafriyat">
                  <label class="form-check-label" for="filter-hafriyat">HAFRİYAT</label>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Kapat</button>
          <button type="button" class="btn btn-primary" id="filterSubmitBtn">Listele</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Detay ve Harita Modalı -->
<div class="modal fade" id="mapModal" tabindex="-1" aria-labelledby="mapModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header pb-1" style="min-height: 80px;">
        <div class="w-100">
          <h5 class="modal-title fw-bold mb-3" id="mapModalLabel">
            <i class="fas fa-map-marker-alt me-2"></i>Atık Merkezi Bilgileri
          </h5>
          <!-- Tab Navigation -->
          <ul class="nav nav-tabs" id="modalTabs" role="tablist">
            <li class="nav-item" role="presentation">
              <button class="nav-link active fw-bold" id="detay-tab" data-bs-toggle="tab" data-bs-target="#detay-content" type="button" role="tab" aria-controls="detay-content" aria-selected="true">
                <i class="fas fa-info-circle me-2"></i>DETAY
              </button>
            </li>
            <li class="nav-item" role="presentation">
              <button class="nav-link fw-bold" id="harita-tab" data-bs-toggle="tab" data-bs-target="#harita-content" type="button" role="tab" aria-controls="harita-content" aria-selected="false">
                <i class="fas fa-map me-2"></i>HARİTA
              </button>
            </li>
          </ul>
        </div>
        <button type="button" class="btn-close ms-3" data-bs-dismiss="modal" aria-label="Kapat"></button>
      </div>
      <div class="modal-body p-0">
        <!-- Tab Content -->
        <div class="tab-content" id="modalTabContent">
          <!-- DETAY Tab -->
          <div class="tab-pane fade show active" id="detay-content" role="tabpanel" aria-labelledby="detay-tab">
            <div class="p-4" id="detay-container">
              <div class="text-center py-5">
                <div class="spinner-border text-primary" role="status">
                  <span class="visually-hidden">Yükleniyor...</span>
                </div>
                <p class="mt-3 text-muted">Merkez bilgileri yükleniyor...</p>
              </div>
            </div>
          </div>
          <!-- HARİTA Tab -->
          <div class="tab-pane fade" id="harita-content" role="tabpanel" aria-labelledby="harita-tab">
            <div id="map" style="height: 500px; width: 100%;"></div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- YouTube Video Modalı -->
<div class="modal fade" id="youtubeModal" tabindex="-1" aria-labelledby="youtubeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold" id="youtubeModalLabel">
                    <i class="fab fa-youtube text-danger me-2"></i>
                    Atık Merkezleri Yönetim Tesisleri 3D Videosu
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Kapat"></button>
            </div>
            <div class="modal-body">
                <div class="ratio ratio-16x9">
                    <iframe src="https://www.youtube.com/embed/VBFfARZh9QI?start=2" 
                            title="Atık Merkezleri Yönetim Tesisleri 3D Videosu" 
                            allowfullscreen>
                    </iframe>
                </div>
            </div>
        </div>
    </div>
</div> 