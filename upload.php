<?php
ob_start();
ini_set('session.cookie_path', '/');
session_start();
require_once 'conexion.php';
if (empty($_SESSION['kkmart_user'])) { header('Location: login.php'); exit(); }

if ($_SERVER["REQUEST_METHOD"] !== "POST") { header("Location: admin.php"); exit(); }

$codigo      = trim($_POST['codigo'] ?? '');
$nombre      = mysqli_real_escape_string($conn, trim($_POST['nombre']));
$precio      = floatval($_POST['precio']);
$categoria   = trim($_POST['categoria'] ?? '');
$descripcion = mysqli_real_escape_string($conn, trim($_POST['descripcion']));
$tags_cache  = trim($_POST['tags_cache'] ?? '');
$cat_nueva   = trim($_POST['categoria_nueva'] ?? '');

if (empty($codigo)) { header("Location: admin.php?status=error_db"); exit(); }

$codigo_safe = mysqli_real_escape_string($conn, $codigo);

// create a new category if was written
if ($cat_nueva !== '') {
    $cn = mysqli_real_escape_string($conn, $cat_nueva);
    mysqli_query($conn, "INSERT IGNORE INTO categorias (nombre) VALUES ('$cn')");
    $categoria = $cat_nueva;
}
$cat_safe = mysqli_real_escape_string($conn, $categoria);

// verifies the category exists
$catRow = mysqli_fetch_assoc(mysqli_query($conn, "SELECT id FROM categorias WHERE nombre='$cat_safe'"));
if (!$catRow) { header("Location: admin.php?status=error_db"); exit(); }

//creates a new tag if its written on
if ($tag_nuevo !== '') {
    $tn = mysqli_real_escape_string($conn, $tag_nuevo);
    mysqli_query($conn, "INSERT IGNORE INTO tags (nombre) VALUES ('$tn')");
    $newTag = mysqli_fetch_assoc(mysqli_query($conn, "SELECT id FROM tags WHERE nombre='$tn'"));
    if ($newTag) $tags_raw[] = $newTag['id'];
}

// verifies if the product code already exists
$check = mysqli_query($conn, "SELECT id FROM productos WHERE id = '$codigo_safe'");
if (mysqli_num_rows($check) > 0) {
    header("Location: admin.php?status=error_dup"); exit();
}

$sql = "INSERT INTO productos (id, nombre, precio, categoria, imagen, descripcion, ventas)
        VALUES ('$codigo_safe', '$nombre', $precio, '$cat_safe', 0, '$descripcion', 0)";

if (!mysqli_query($conn, $sql)) {
    header("Location: admin.php?status=error_db"); mysqli_close($conn); exit();
}

// Save the tags name
$cache = mysqli_real_escape_string($conn, $tags_cache);
mysqli_query($conn, "UPDATE productos SET tags_cache='$cache' WHERE id='$codigo_safe'");

// if the product has sizes, then saves it
if (!empty($_POST['tiene_medidas']) && !empty($_POST['medida_nombre'])) {
    $nombres  = $_POST['medida_nombre'];
    $precios  = $_POST['medida_precio'];
    $codigos  = $_POST['medida_codigo'];
    for ($m = 0; $m < count($nombres); $m++) {
        $mn = mysqli_real_escape_string($conn, trim($nombres[$m]  ?? ''));
        $mp = floatval($precios[$m] ?? 0);
        $mc = mysqli_real_escape_string($conn, trim($codigos[$m]  ?? ''));
        if ($mn === '') continue;
        mysqli_query($conn, "INSERT INTO producto_medidas (producto_id, nombre, precio, codigo) VALUES ('$codigo_safe', '$mn', $mp, '$mc')");
    }
}


//save sizes
$medida_nombres = $_POST['medida_nombre'] ?? [];
$medida_precios = $_POST['medida_precio'] ?? [];
$medida_codigos = $_POST['medida_codigo'] ?? [];
for ($mi = 0; $mi < count($medida_nombres); $mi++) {
    $mn = mysqli_real_escape_string($conn, trim($medida_nombres[$mi] ?? ''));
    $mp = floatval($medida_precios[$mi] ?? 0);
    $mc = mysqli_real_escape_string($conn, trim($medida_codigos[$mi] ?? ''));
    if ($mn !== '') {
        mysqli_query($conn, "INSERT INTO producto_medidas (producto_id, nombre, precio, codigo) VALUES ('$codigo_safe', '$mn', $mp, '$mc')");
    }
}

// ─ save the images
$dir   = "uploads/{$codigo}/";
$usaDir = is_dir($dir) ? true : @mkdir($dir, 0755, true);
$fotos = $_FILES['foto'] ?? [];
$ok    = true;
$count = 0;

if (!empty($fotos['name'])) {
    for ($i = 0; $i < count($fotos['name']); $i++) {
        if ($fotos['error'][$i] !== UPLOAD_ERR_OK || empty($fotos['name'][$i])) continue;
        $dest = $usaDir ? $dir.($count+1).".jpg" : "uploads/{$codigo}_".($count+1).".jpg";
        if (move_uploaded_file($fotos['tmp_name'][$i], $dest)) $count++;
        else $ok = false;
    }
}

mysqli_query($conn, "UPDATE productos SET imagen=$count WHERE id='$codigo_safe'");
mysqli_close($conn);

header("Location: admin.php?status=".($count===0||!$ok?'error_upload':'success'));
exit();
