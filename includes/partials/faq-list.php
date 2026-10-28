<?php
// Sual-cavab siyahısı. Birinci sual açıq göstərilir. $items: data/faq.php formatında
?>
<div class="faq-list">
    <?php foreach ($items as $i => $item): ?>
        <details class="faq-item"<?= $i === 0 ? ' open' : '' ?>>
            <summary>
                <span><?= ($i + 1) . '. ' . e($item['q']) ?></span>
                <?= icon('plus', 'faq-item__open') ?><?= icon('minus', 'faq-item__close') ?>
            </summary>
            <div class="faq-item__answer"><?= $item['a'] ?></div>
        </details>
    <?php endforeach; ?>
</div>
