<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Konya Büyükşehir - Atık Merkezleri</title>
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            font-family: sans-serif;
            background-color: #F5F5F5;
            margin: 0;
            padding: 0;
        }

        header {
    background-color:rgb(57, 66, 77);
    color: white;
    padding: 1.5rem 1rem;
    text-align: center;
}

.logo-wrapper {
    display: flex;
    justify-content: center;
    gap: 2rem;
    align-items: center;
    margin-bottom: 1rem;
}

.logo-wrapper img {
    height: 60px;
    object-fit: contain;
}

header h1 {
    font-size: 1.5rem;
    margin: 0;
}

        header img {
            height: 50px;
            margin-right: 1rem;
        }

        .container {
            padding: 2rem;
        }

        footer {
            background-color: #34373b;
            color: white;
            padding: 1rem;
            text-align: center;
            margin-top: 2rem;
        }
    </style>
</head>
<body>
<header class="py-4" style="background-color:rgb(57, 66, 77); color:white;">
    <div class="container">
        <div class="row align-items-center mb-3 justify-content-center">
            <div class="col-auto">
                <img src="{{ asset('images/benim-sehrim-logo.png') }}" alt="Benim Şehrim Logosu" style="height:60px; object-fit:contain;">
            </div>
            <div class="col-auto">
                <img src="{{ asset('images/kbb-logo.png') }}" alt="Konya BB Logosu" style="height:60px; object-fit:contain;">
            </div>
        </div>
        <h1 class="h4 m-0 text-center">Konya Büyükşehir Belediyesi Atık Merkezleri</h1>
    </div>
</header>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-12 text-center">
            <button type="button" class="btn btn-primary btn-lg" data-bs-toggle="modal" data-bs-target="#filtreModal">
                FİLTRELEME
            </button>
        </div>
    </div>
</div>

<!-- Filtreleme Modalı -->
<div class="modal fade" id="filtreModal" tabindex="-1" aria-labelledby="filtreModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title fw-bold" id="filtreModalLabel">Atık Merkezi Türüne Göre Filtrele</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Kapat"></button>
      </div>
      <div class="modal-body">
        <form id="filterForm">
          <div class="mb-4">
            <select class="form-select" id="contentFilter">
              <option value="">Tümü</option>
              <option value="mobil">MOBİL ATIK GETİRME MERKEZİ (KAĞIT, PLASTİK, CAM, METAL, PİL)</option>
              <option value="bitkisel">BİTKİSEL ATIK YAĞ</option>
              <option value="cam">ATIK CAM</option>
              <option value="tekstil">TEKSTİL KUMBARASI</option>
              <option value="gecici">ATIK GEÇİCİ DEPOLAMA ÜNİTESİ</option>
              <option value="ilac">ATIK İLAÇ</option>
              <option value="sinif1">1. SINIF ATIK GETİRME MERKEZİ</option>
              <option value="inert">İNERT ATIK</option>
              <option value="hafriyat">HAFRİYAT</option>
            </select>
          </div>
        </form>
        <div id="merkezListesi">
          <!-- Burada filtreye uygun atık merkezleri listelenecek -->
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Kapat</button>
      </div>
    </div>
  </div>
</div>

<footer class="py-3" style="background-color: #34373b; color: white;">
    <div class="container text-center">
        &copy; 2025 Konya Büyükşehir Belediyesi
    </div>
</footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    // Basit filtreleme (sadece frontend)
    document.getElementById('contentFilter').addEventListener('change', function() {
        var value = this.value;
        var merkezler = document.querySelectorAll('.merkez');
        merkezler.forEach(function(merkez) {
            if (!value || merkez.getAttribute('data-content').includes(value)) {
                merkez.style.display = '';
            } else {
                merkez.style.display = 'none';
            }
        });
    });
    </script>
</body>
</html>
