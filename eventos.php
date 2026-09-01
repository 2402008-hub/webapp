<?php
$uploadsDir = __DIR__ . '/uploads/';
$albums = [];

if (is_dir($uploadsDir)) {
    foreach (scandir($uploadsDir) as $item) {
        if ($item === '.' || $item === '..') continue;
        $albumPath = $uploadsDir . $item;
        if (is_dir($albumPath)) {
            $photos = [];
            foreach (scandir($albumPath) as $file) {
                $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
                if (in_array($ext, ['jpg','jpeg','png','webp','gif'])) {
                    $photos[] = $file;
                }
            }
            if (!empty($photos)) {
                $albums[] = [
                    'slug'   => $item,
                    'name'   => ucwords(str_replace(['-','_'], ' ', $item)),
                    'cover'  => $photos[0],
                    'count'  => count($photos),
                    'photos' => $photos,
                ];
            }
        }
    }
}

$view = isset($_GET['album']) ? $_GET['album'] : null;
$currentAlbum = null;
if ($view) {
    foreach ($albums as $a) {
        if ($a['slug'] === $view) { $currentAlbum = $a; break; }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= $currentAlbum ? htmlspecialchars($currentAlbum['name']) . ' — ' : '' ?>Eventos | 1ra Iglesia Apostólica</title>
  <link rel="stylesheet" href="style.css">
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&family=Lato:wght@300;400;700&display=swap" rel="stylesheet">
  <style>
    .ev-hero {
      background: linear-gradient(135deg, #0d1f3c 0%, #1a3a6e 100%);
      padding: 7rem 1.5rem 3rem;
      text-align: center;
    }
    .ev-hero h1 {
      font-family: 'Playfair Display', serif;
      color: #fff;
      font-size: clamp(2rem, 5vw, 3.2rem);
      margin-bottom: .5rem;
    }
    .ev-hero p { color: rgba(255,255,255,.6); font-size: 1rem; }
    .ev-hero .gold { color: #c9a84c; }

    .ev-body { padding: 3rem 1.5rem 5rem; background: #f9f5ef; min-height: 60vh; }
    .ev-container { max-width: 1100px; margin: 0 auto; }

    /* Breadcrumb */
    .breadcrumb { margin-bottom: 2rem; font-size: .85rem; color: #6b7280; }
    .breadcrumb a { color: #1a3a6e; text-decoration: none; }
    .breadcrumb a:hover { text-decoration: underline; }
    .breadcrumb span { margin: 0 .4rem; }

    /* Albums grid */
    .albums-grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
      gap: 1.5rem;
    }
    .album-card {
      background: #fff;
      border-radius: 10px;
      overflow: hidden;
      box-shadow: 0 2px 12px rgba(13,31,60,.07);
      text-decoration: none;
      display: block;
      transition: transform .3s, box-shadow .3s;
    }
    .album-card:hover { transform: translateY(-5px); box-shadow: 0 10px 30px rgba(13,31,60,.13); }
    .album-cover {
      width: 100%; height: 200px; object-fit: cover; display: block;
      background: #ddd;
    }
    .album-cover-placeholder {
      width: 100%; height: 200px;
      background: linear-gradient(135deg,#1a3a6e,#2d6bc4);
      display: flex; align-items: center; justify-content: center;
      font-size: 3rem; color: rgba(255,255,255,.4);
    }
    .album-info { padding: 1.1rem 1.25rem 1.25rem; }
    .album-name {
      font-family: 'Playfair Display', serif;
      font-size: 1.15rem; color: #0d1f3c; margin-bottom: .3rem;
    }
    .album-count { font-size: .8rem; color: #6b7280; }

    /* Empty state */
    .empty-state {
      text-align: center; padding: 4rem 1rem; color: #6b7280;
    }
    .empty-state .icon { font-size: 3rem; margin-bottom: 1rem; display: block; }
    .empty-state h3 { font-family: 'Playfair Display',serif; color:#0d1f3c; margin-bottom:.5rem; }

    /* Photo grid */
    .photos-grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
      gap: 1rem;
    }
    .photo-item {
      border-radius: 8px; overflow: hidden;
      box-shadow: 0 2px 8px rgba(13,31,60,.08);
      cursor: pointer;
      aspect-ratio: 4/3;
    }
    .photo-item img {
      width: 100%; height: 100%; object-fit: cover; display: block;
      transition: transform .4s;
    }
    .photo-item:hover img { transform: scale(1.05); }

    /* Lightbox */
    .lightbox {
      display: none; position: fixed; inset: 0;
      background: rgba(0,0,0,.92); z-index: 9999;
      align-items: center; justify-content: center;
    }
    .lightbox.active { display: flex; }
    .lightbox img {
      max-width: 90vw; max-height: 88vh;
      border-radius: 6px; object-fit: contain;
    }
    .lb-close {
      position: fixed; top: 1.2rem; right: 1.5rem;
      color: #fff; font-size: 2rem; cursor: pointer;
      background: none; border: none; line-height: 1;
    }
    .lb-prev, .lb-next {
      position: fixed; top: 50%; transform: translateY(-50%);
      color: #fff; font-size: 2rem; cursor: pointer;
      background: rgba(255,255,255,.1); border: none;
      padding: .5rem 1rem; border-radius: 4px;
      transition: background .2s;
    }
    .lb-prev { left: 1rem; }
    .lb-next { right: 1rem; }
    .lb-prev:hover, .lb-next:hover { background: rgba(255,255,255,.2); }

    .back-btn {
      display: inline-block; margin-bottom: 1.5rem;
      padding: .55rem 1.4rem;
      background: #0d1f3c; color: #fff;
      text-decoration: none; border-radius: 4px;
      font-size: .85rem; font-weight: 700;
      transition: background .2s;
    }
    .back-btn:hover { background: #1a3a6e; }
    .album-title-page {
      font-family: 'Playfair Display',serif;
      font-size: 2rem; color: #0d1f3c; margin-bottom: 1.5rem;
    }
  </style>
</head>
<body>

<!-- NAVBAR -->
<nav class="navbar">
  <div class="nav-inner">
    <a href="index.html" class="nav-logo" style="text-decoration:none;">✝ 1ra Iglesia Apostólica</a>
    <ul class="nav-links">
      <li><a href="index.html#inicio">Inicio</a></li>
      <li><a href="index.html#nosotros">Nosotros</a></li>
      <li><a href="index.html#horarios">Horarios</a></li>
      <li><a href="eventos.php" style="color:#c9a84c;">Eventos</a></li>
      <li><a href="index.html#ubicacion">Ubicación</a></li>
      <li><a href="index.html#contacto">Contacto</a></li>
    </ul>
    <button class="nav-toggle" onclick="toggleMenu()">☰</button>
  </div>
  <ul class="nav-mobile" id="navMobile">
    <li><a href="index.html#inicio" onclick="toggleMenu()">Inicio</a></li>
    <li><a href="index.html#nosotros" onclick="toggleMenu()">Nosotros</a></li>
    <li><a href="index.html#horarios" onclick="toggleMenu()">Horarios</a></li>
    <li><a href="eventos.php" onclick="toggleMenu()">Eventos</a></li>
    <li><a href="index.html#ubicacion" onclick="toggleMenu()">Ubicación</a></li>
    <li><a href="index.html#contacto" onclick="toggleMenu()">Contacto</a></li>
  </ul>
</nav>

<!-- HERO -->
<div class="ev-hero">
  <h1><?= $currentAlbum ? htmlspecialchars($currentAlbum['name']) : 'Nuestros <span class="gold">Eventos</span>' ?></h1>
  <p><?= $currentAlbum ? $currentAlbum['count'] . ' fotografías' : 'Momentos especiales de nuestra comunidad' ?></p>
</div>

<!-- BODY -->
<div class="ev-body">
  <div class="ev-container">

    <?php if ($currentAlbum): ?>
      <!-- VISTA DE ÁLBUM -->
      <div class="breadcrumb">
        <a href="eventos.php">Eventos</a>
        <span>›</span>
        <?= htmlspecialchars($currentAlbum['name']) ?>
      </div>
      <a href="eventos.php" class="back-btn">← Volver a álbumes</a>
      <h2 class="album-title-page"><?= htmlspecialchars($currentAlbum['name']) ?></h2>
      <div class="photos-grid">
        <?php foreach ($currentAlbum['photos'] as $i => $photo): ?>
          <div class="photo-item" onclick="openLightbox(<?= $i ?>)">
            <img
              src="uploads/<?= urlencode($currentAlbum['slug']) ?>/<?= urlencode($photo) ?>"
              alt="<?= htmlspecialchars($photo) ?>"
              loading="lazy"
            >
          </div>
        <?php endforeach; ?>
      </div>

      <!-- LIGHTBOX -->
      <div class="lightbox" id="lightbox">
        <button class="lb-close" onclick="closeLightbox()">✕</button>
        <button class="lb-prev" onclick="changePhoto(-1)">‹</button>
        <img id="lbImg" src="" alt="">
        <button class="lb-next" onclick="changePhoto(1)">›</button>
      </div>
      <script>
        const photos = <?= json_encode(array_map(fn($p) => 'uploads/' . urlencode($currentAlbum['slug']) . '/' . urlencode($p), $currentAlbum['photos'])) ?>;
        let current = 0;
        function openLightbox(i) {
          current = i;
          document.getElementById('lbImg').src = photos[i];
          document.getElementById('lightbox').classList.add('active');
          document.body.style.overflow = 'hidden';
        }
        function closeLightbox() {
          document.getElementById('lightbox').classList.remove('active');
          document.body.style.overflow = '';
        }
        function changePhoto(dir) {
          current = (current + dir + photos.length) % photos.length;
          document.getElementById('lbImg').src = photos[current];
        }
        document.getElementById('lightbox').addEventListener('click', function(e) {
          if (e.target === this) closeLightbox();
        });
        document.addEventListener('keydown', function(e) {
          if (e.key === 'Escape') closeLightbox();
          if (e.key === 'ArrowLeft') changePhoto(-1);
          if (e.key === 'ArrowRight') changePhoto(1);
        });
      </script>

    <?php elseif (empty($albums)): ?>
      <!-- SIN ÁLBUMES -->
      <div class="empty-state">
        <span class="icon">📸</span>
        <h3>Próximamente</h3>
        <p>Estamos preparando los álbumes de eventos. ¡Vuelve pronto!</p>
      </div>

    <?php else: ?>
      <!-- LISTA DE ÁLBUMES -->
      <div class="albums-grid">
        <?php foreach ($albums as $album): ?>
          <a href="eventos.php?album=<?= urlencode($album['slug']) ?>" class="album-card">
            <?php
              $coverPath = 'uploads/' . urlencode($album['slug']) . '/' . urlencode($album['cover']);
            ?>
            <img class="album-cover" src="<?= $coverPath ?>" alt="<?= htmlspecialchars($album['name']) ?>" loading="lazy">
            <div class="album-info">
              <div class="album-name"><?= htmlspecialchars($album['name']) ?></div>
              <div class="album-count"><?= $album['count'] ?> foto<?= $album['count'] !== 1 ? 's' : '' ?></div>
            </div>
          </a>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

  </div>
</div>

<!-- FOOTER -->
<footer class="footer">
  <div class="container footer-inner">
    <div class="footer-logo">✝ 1ra Iglesia Apostólica</div>
    <p class="footer-address">Calle 82 entre 25 y 30, Col. Colosio, Playa del Carmen, Q.R.</p>
    <p class="footer-copy">© <?= date('Y') ?> 1ra Iglesia Apostólica de Playa del Carmen.</p>
  </div>
</footer>

<script src="main.js"></script>
</body>
</html>
