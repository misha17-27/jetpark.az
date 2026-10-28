<?php
// Yaşıl xətli kiçik başlıq: "— HAQQIMIZDA —". $center = true olduqda hər iki tərəfdə xətt olur.
$center ??= false;
$tag ??= 'h2';
?>
<<?= $tag ?> class="section-label<?= $center ? ' section-label--center' : '' ?>"><?= e($text) ?></<?= $tag ?>>
