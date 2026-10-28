<?php
$title = 'Səhifə tapılmadı - ' . config('site_name');
$noindex = true;
?>
<section class="page-hero" style="--hero-image: url('<?= asset(content('blog.hero_image')) ?>')">
    <div class="container">
        <h1 class="page-hero__title">404</h1>
    </div>
</section>
<section class="not-found container">
    <h2 class="section-heading">Axtardığınız səhifə tapılmadı</h2>
    <p>Səhifə silinmiş və ya ünvanı dəyişdirilmiş ola bilər.</p>
    <a class="btn btn--dark-green" href="<?= url() ?>">Ana səhifəyə qayıt</a>
</section>
