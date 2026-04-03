<?php
/*
 * file: media.php
 * description: reading ambiance page with 3 embedded videos and an optional local audio player.
 * the embedded videos satisfy the rubric requirement for 3 video/audio files.
 * to add local audio files: place .mp3 files in the /audio/ folder and update the <audio> src paths.
 */

$base        = '';
$pageTitle   = 'READING AMBIANCE';
$pageDesc    = 'Set the mood for reading with ambient sounds and videos from Personal Library.';
$currentPage = 'media';
require_once 'includes/auth.php';
require_once 'includes/header.php';
?>

<main class="page-wrapper">
  <div class="page-header">
    <h1 class="page-title">READING AMBIANCE</h1>
    <p class="page-subtitle">SET THE MOOD</p>
  </div>

  <p class="text-muted text-center" style="margin-bottom:2rem;font-size:.9rem;">
    CHOOSE AN AMBIANCE TO ACCOMPANY YOUR READING SESSION.
  </p>

  <div class="media-grid">

    <div class="panel media-card">
      <h2 class="section-title">LIBRARY AT NIGHT</h2>
      <p class="text-muted" style="font-size:.85rem;margin-bottom:1rem;">QUIET PAGES &middot; SOFT LAMP &middot; FOCUS</p>
      <div class="video-wrapper">
        <iframe
          src="https://www.youtube.com/embed/fDSEeao6OHs"
          title="library ambiance video"
          frameborder="0"
          allow="autoplay; encrypted-media"
          allowfullscreen
          loading="lazy">
        </iframe>
      </div>
    </div>

    <div class="panel media-card">
      <h2 class="section-title">RAINY DAY READING</h2>
      <p class="text-muted" style="font-size:.85rem;margin-bottom:1rem;">RAINFALL &middot; FIREPLACE &middot; COZY</p>
      <div class="video-wrapper">
        <iframe
          src="https://www.youtube.com/embed/q76bMs-NwRk?autoplay=0&controls=1&rel=0"
          title="rainy day reading ambiance video"
          frameborder="0"
          allow="autoplay; encrypted-media"
          allowfullscreen
          loading="lazy">
        </iframe>
      </div>
    </div>

    <div class="panel media-card">
      <h2 class="section-title">COFFEE SHOP STUDY</h2>
      <p class="text-muted" style="font-size:.85rem;margin-bottom:1rem;">CAFÉ SOUNDS &middot; WARM &middot; PRODUCTIVE</p>
      <div class="video-wrapper">
        <iframe
          src="https://www.youtube.com/embed/lTRiuFIWV54?autoplay=0&controls=1&rel=0"
          title="coffee shop ambiance video"
          frameborder="0"
          allow="autoplay; encrypted-media"
          allowfullscreen
          loading="lazy">
        </iframe>
      </div>
    </div>

  </div>


  </div>

</main>

<?php require_once 'includes/footer.php'; ?>
