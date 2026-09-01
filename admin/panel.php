<?php
session_start();
if (empty($_SESSION['admin_logged'])) {
    header('Location: index.php');
    exit;
}

$uploadsDir = dirname(__DIR__) . '/uploads/';
$msg = '';
$msgType = '';

// ── CREAR ÁLBUM ──
if (isset($_POST['action']) && $_POST['action'] === 'create_album') {
    $name = trim($_POST['album_name'] ?? '');
    if ($name === '') {
        $msg = 'El nombre del álbum no puede estar vacío.';
        $msgType = 'error';
    } else {
        $slug = strtolower(preg_replace('/[^a-zA-Z0-9]+/', '-', $name));
        $slug = trim($slug, '-');
        $path = $uploadsDir . $slug;
        if (is_dir($path)) {
            $msg = 'Ya existe un álbum con ese nombre.';
            $msgType = 'error';
        } else {
            mkdir($path, 0755, true);
            $msg = 'Álbum "' . htmlspecialchars($name) . '" creado correctamente.';
            $msgType = 'ok';
        }
    }
}

// ── SUBIR FOTOS ──
if (isset($_POST['action']) && $_POST['action'] === 'upload_photos') {
    $album = $_POST['album_slug'] ?? '';
    $albumPath = $uploadsDir . $album . '/';
    if (!is_dir($albumPath)) {
        $msg = 'Álbum no encontrado.';
        $msgType = 'error';
    } elseif (empty($_FILES['photos']['name'][0])) {
        $msg = 'No seleccionaste ninguna foto.';
        $msgType = 'error';
    } else {
        $allowed = ['jpg','jpeg','png','webp','gif'];
        $uploaded = 0; $errors = 0;
        foreach ($_FILES['photos']['tmp_name'] as $i => $tmp) {
            $origName = $_FILES['photos']['name'][$i];
            $ext = strtolower(pathinfo($origName, PATHINFO_EXTENSION));
            if (!in_array($ext, $allowed)) { $errors++; continue; }
            $newName = uniqid('img_') . '.' . $ext;
            if (move_uploaded_file($tmp, $albumPath . $newName)) $uploaded++;
            else $errors++;
        }
        $msg = "$uploaded foto(s) subida(s) correctamente." . ($errors ? " $errors archivo(s) ignorado(s)." : '');
        $msgType = $errors && !$uploaded ? 'error' : 'ok';
    }
}

// ── ELIMINAR FOTO ──
if (isset($_POST['action']) && $_POST['action'] === 'delete_photo') {
    $album = $_POST['album_slug'] ?? '';
    $photo = basename($_POST['photo'] ?? '');
    $filePath = $uploadsDir . $album . '/' . $photo;
    if (file_exists($filePath) && is_file($filePath)) {
        unlink($filePath);
        $msg = 'Foto eliminada.';
        $msgType = 'ok';
    }
}

// ── ELIMINAR ÁLBUM ──
if (isset($_POST['action']) && $_POST['action'] === 'delete_album') {
    $album = $_POST['album_slug'] ?? '';
    $albumPath = $uploadsDir . $album . '/';
    if (is_dir($albumPath)) {
        foreach (scandir($albumPath) as $f) {
            if ($f !== '.' && $f !== '..') unlink($albumPath . $f);
        }
        rmdir($albumPath);
        $msg = 'Álbum eliminado.';
        $msgType = 'ok';
    }
}

// ── CARGAR ÁLBUMES ──
$albums = [];
if (is_dir($uploadsDir)) {
    foreach (scandir($uploadsDir) as $item) {
        if ($item === '.' || $item === '..') continue;
        $albumPath = $uploadsDir . $item;
        if (is_dir($albumPath)) {
            $photos = [];
            foreach (scandir($albumPath) as $file) {
                $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
                if (in_array($ext, ['jpg','jpeg','png','webp','gif'])) $photos[] = $file;
            }
            $albums[] = [
                'slug'   => $item,
                'name'   => ucwords(str_replace(['-','_'], ' ', $item)),
                'photos' => $photos,
            ];
        }
    }
}

$openAlbum = $_GET['album'] ?? null;
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Panel Admin — Eventos</title>
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Lato:wght@400;700&display=swap" rel="stylesheet">
  <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
    body { font-family: 'Lato', sans-serif; background: #f3f4f6; color: #1a1a2e; }

    /* TOP BAR */
    .topbar {
      background: #0d1f3c;
      padding: .9rem 1.5rem;
      display: flex; align-items: center; justify-content: space-between;
      position: sticky; top: 0; z-index: 100;
    }
    .topbar-logo { color: #c9a84c; font-family: 'Playfair Display',serif; font-size: 1.05rem; }
    .topbar-links { display: flex; gap: 1rem; align-items: center; }
    .topbar-links a {
      color: rgba(255,255,255,.7); text-decoration: none; font-size: .85rem;
      transition: color .2s;
    }
    .topbar-links a:hover { color: #fff; }
    .btn-logout {
      background: rgba(255,255,255,.1); color: #fff !important;
      padding: .35rem .9rem; border-radius: 4px;
    }

    /* LAYOUT */
    .admin-wrap { max-width: 1100px; margin: 2rem auto; padding: 0 1.5rem 4rem; }

    h1 { font-family: 'Playfair Display',serif; font-size: 2rem; color: #0d1f3c; margin-bottom: .3rem; }
    .page-sub { color: #6b7280; font-size: .9rem; margin-bottom: 2rem; }

    /* ALERT */
    .alert {
      padding: .85rem 1.2rem; border-radius: 6px; margin-bottom: 1.5rem; font-size: .9rem;
    }
    .alert.ok  { background: #f0fdf4; color: #166534; border: 1px solid #bbf7d0; }
    .alert.error { background: #fef2f2; color: #dc2626; border: 1px solid #fecaca; }

    /* CARD */
    .card {
      background: #fff; border-radius: 10px;
      padding: 1.75rem; margin-bottom: 1.5rem;
      box-shadow: 0 2px 10px rgba(13,31,60,.06);
    }
    .card h2 { font-size: 1.1rem; color: #0d1f3c; margin-bottom: 1.2rem;
               padding-bottom: .7rem; border-bottom: 1px solid #e5e7eb; }

    /* FORM */
    label { display: block; font-size: .8rem; font-weight: 700;
            text-transform: uppercase; letter-spacing: .07em;
            color: #374151; margin-bottom: .4rem; }
    input[type=text], select {
      width: 100%; padding: .7rem 1rem; border: 1.5px solid #d1d5db;
      border-radius: 6px; font-size: .95rem; outline: none;
      transition: border-color .2s; font-family: inherit;
    }
    input[type=text]:focus, select:focus { border-color: #1a3a6e; }
    .form-row { display: flex; gap: 1rem; align-items: flex-end; flex-wrap: wrap; }
    .form-row > * { flex: 1; min-width: 180px; }

    .btn {
      display: inline-block; padding: .7rem 1.4rem;
      border: none; border-radius: 6px; font-size: .875rem;
      font-weight: 700; cursor: pointer; font-family: inherit;
      transition: background .2s, transform .15s; text-decoration: none;
    }
    .btn:hover { transform: translateY(-1px); }
    .btn-primary { background: #0d1f3c; color: #fff; }
    .btn-primary:hover { background: #1a3a6e; }
    .btn-gold { background: #c9a84c; color: #0d1f3c; }
    .btn-gold:hover { background: #e8c97a; }
    .btn-danger { background: #dc2626; color: #fff; font-size: .78rem; padding: .4rem .85rem; }
    .btn-danger:hover { background: #b91c1c; }
    .btn-sm { padding: .4rem .9rem; font-size: .8rem; }

    /* FILE INPUT */
    .file-label {
      display: block; padding: 2rem; text-align: center;
      border: 2px dashed #c9a84c; border-radius: 8px;
      cursor: pointer; color: #6b7280; transition: background .2s;
      margin-bottom: 1rem;
    }
    .file-label:hover { background: #fffdf4; }
    .file-label input { display: none; }

    /* ALBUMS TABLE */
    .albums-table { width: 100%; border-collapse: collapse; }
    .albums-table th {
      text-align: left; font-size: .75rem; text-transform: uppercase;
      letter-spacing: .08em; color: #6b7280; padding: .6rem .75rem;
      border-bottom: 1px solid #e5e7eb;
    }
    .albums-table td {
      padding: .85rem .75rem; border-bottom: 1px solid #f3f4f6;
      font-size: .9rem; vertical-align: middle;
    }
    .albums-table tr:last-child td { border-bottom: none; }
    .albums-table tr:hover td { background: #fafafa; }
    .badge {
      display: inline-block; background: #f0f2f5; color: #374151;
      padding: .2rem .6rem; border-radius: 20px; font-size: .78rem; font-weight: 700;
    }

    /* PHOTO GRID */
    .photo-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(150px,1fr)); gap: .75rem; }
    .photo-thumb {
      position: relative; aspect-ratio: 4/3;
      border-radius: 6px; overflow: hidden; background: #e5e7eb;
    }
    .photo-thumb img { width: 100%; height: 100%; object-fit: cover; display: block; }
    .photo-thumb .del-btn {
      position: absolute; top: 5px; right: 5px;
      background: rgba(220,38,38,.85); color: #fff;
      border: none; border-radius: 50%; width: 26px; height: 26px;
      font-size: .85rem; cursor: pointer; display: flex;
      align-items: center; justify-content: center;
      opacity: 0; transition: opacity .2s;
    }
    .photo-thumb:hover .del-btn { opacity: 1; }

    /* BACK LINK */
    .back-link { display: inline-block; margin-bottom: 1.5rem; color: #1a3a6e;
                 text-decoration: none; font-size: .875rem; }
    .back-link:hover { text-decoration: underline; }

    @media(max-width:600px) {
      .form-row { flex-direction: column; }
    }
  </style>
</head>
<body>

<!-- TOP BAR -->
<div class="topbar">
  <span class="topbar-logo">✝ Admin — Iglesia Apostólica</span>
  <div class="topbar-links">
    <a href="../eventos.php" target="_blank">Ver galería →</a>
    <a href="logout.php" class="btn-logout">Cerrar sesión</a>
  </div>
</div>

<div class="admin-wrap">

  <?php if ($msg): ?>
    <div class="alert <?= $msgType ?>"><?= $msg ?></div>
  <?php endif; ?>

  <?php if ($openAlbum && ($albumObj = current(array_filter($albums, fn($a) => $a['slug'] === $openAlbum)))): ?>
    <!-- ──────────── VISTA DE ÁLBUM ──────────── -->
    <a href="panel.php" class="back-link">← Volver a álbumes</a>
    <h1><?= htmlspecialchars($albumObj['name']) ?></h1>
    <p class="page-sub"><?= count($albumObj['photos']) ?> foto(s) en este álbum</p>

    <!-- Subir fotos -->
    <div class="card">
      <h2>📤 Subir fotos a este álbum</h2>
      <form method="POST" enctype="multipart/form-data">
        <input type="hidden" name="action" value="upload_photos">
        <input type="hidden" name="album_slug" value="<?= htmlspecialchars($openAlbum) ?>">
        <label class="file-label">
          <input type="file" name="photos[]" accept="image/*" multiple onchange="updateLabel(this)">
          <span id="fileLabel">📷 Haz clic aquí o arrastra las fotos<br><small>JPG, PNG, WEBP, GIF</small></span>
        </label>
        <button type="submit" class="btn btn-gold">Subir fotos</button>
      </form>
    </div>

    <!-- Fotos existentes -->
    <div class="card">
      <h2>🖼️ Fotos del álbum</h2>
      <?php if (empty($albumObj['photos'])): ?>
        <p style="color:#6b7280;font-size:.9rem;">Este álbum aún no tiene fotos.</p>
      <?php else: ?>
        <div class="photo-grid">
          <?php foreach ($albumObj['photos'] as $photo): ?>
            <div class="photo-thumb">
              <img src="../uploads/<?= urlencode($openAlbum) ?>/<?= urlencode($photo) ?>" alt="">
              <form method="POST" style="margin:0;" onsubmit="return confirm('¿Eliminar esta foto?')">
                <input type="hidden" name="action" value="delete_photo">
                <input type="hidden" name="album_slug" value="<?= htmlspecialchars($openAlbum) ?>">
                <input type="hidden" name="photo" value="<?= htmlspecialchars($photo) ?>">
                <button type="submit" class="del-btn" title="Eliminar">✕</button>
              </form>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>

  <?php else: ?>
    <!-- ──────────── PANEL PRINCIPAL ──────────── -->
    <h1>Panel de Eventos</h1>
    <p class="page-sub">Gestiona los álbumes y fotos de la galería pública</p>

    <!-- Crear álbum -->
    <div class="card">
      <h2>➕ Crear nuevo álbum</h2>
      <form method="POST">
        <input type="hidden" name="action" value="create_album">
        <div class="form-row">
          <div>
            <label for="album_name">Nombre del álbum</label>
            <input type="text" id="album_name" name="album_name" placeholder="Ej: Bautismos Junio 2025" required>
          </div>
          <div style="display:flex;align-items:flex-end;">
            <button type="submit" class="btn btn-primary" style="width:100%;">Crear álbum</button>
          </div>
        </div>
      </form>
    </div>

    <!-- Lista de álbumes -->
    <div class="card">
      <h2>📁 Álbumes existentes (<?= count($albums) ?>)</h2>
      <?php if (empty($albums)): ?>
        <p style="color:#6b7280;font-size:.9rem;">No hay álbumes todavía. ¡Crea el primero!</p>
      <?php else: ?>
        <table class="albums-table">
          <thead>
            <tr>
              <th>Álbum</th>
              <th>Fotos</th>
              <th>Acciones</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($albums as $album): ?>
              <tr>
                <td><?= htmlspecialchars($album['name']) ?></td>
                <td><span class="badge"><?= count($album['photos']) ?></span></td>
                <td style="display:flex;gap:.5rem;flex-wrap:wrap;">
                  <a href="panel.php?album=<?= urlencode($album['slug']) ?>" class="btn btn-gold btn-sm">Ver / Subir</a>
                  <form method="POST" style="margin:0;" onsubmit="return confirm('¿Eliminar el álbum «<?= htmlspecialchars($album['name']) ?>» y todas sus fotos?')">
                    <input type="hidden" name="action" value="delete_album">
                    <input type="hidden" name="album_slug" value="<?= htmlspecialchars($album['slug']) ?>">
                    <button type="submit" class="btn btn-danger">Eliminar</button>
                  </form>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      <?php endif; ?>
    </div>

  <?php endif; ?>
</div>

<script>
function updateLabel(input) {
  const label = document.getElementById('fileLabel');
  const count = input.files.length;
  label.innerHTML = count > 0
    ? '✅ ' + count + ' archivo(s) seleccionado(s)'
    : '📷 Haz clic aquí o arrastra las fotos<br><small>JPG, PNG, WEBP, GIF</small>';
}
</script>
</body>
</html>
