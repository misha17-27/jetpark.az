<?php
// Daxili səhifələrin başlıq bloku: $heading, $image (assets/ daxilində yol)
?>
<section class="page-hero" style="--hero-image: url('<?= asset($image) ?>')">
    <div class="container">
        <h1 class="page-hero__title"><?= e($heading) ?></h1>
    </div>
</section>
