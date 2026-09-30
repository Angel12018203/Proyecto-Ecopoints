<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title><?= h($titulo ?? 'EcoPoints — Plataforma para Recicladores') ?></title>
  
  <!-- Tipografía oficial Poppins de Google Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  
  <!-- Hoja de estilos del Sistema de Diseño EcoPoints -->
  <link rel="stylesheet" href="<?= asset('css/styles.css') ?>" />
  
  <!-- Favicon e Isotipo Oficial EcoPoints -->
  <link rel="icon" type="image/jpeg" href="<?= asset('img/logo-isotipo.jpg') ?>" />
  <link rel="apple-touch-icon" href="<?= asset('img/logo-isotipo.jpg') ?>" />
</head>
<body>
<div id="app">
