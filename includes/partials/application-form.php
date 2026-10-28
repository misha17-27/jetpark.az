<?php
/**
 * Müraciət formu.
 * $type: null — istifadəçi "Növ" seçir; 'Sahibkar' və ya 'Sürücü' — sabit dəyər (seçim göstərilmir).
 * $source: müraciətin hansı səhifədən gəldiyi (e-poçtda göstərilir).
 */
$type ??= null;
$source ??= 'Ana səhifə';
$formId = 'form-' . substr(md5($source), 0, 6);
$notice = flash($formId);
$old = $notice['old'] ?? [];
?>
<form class="apply-form" id="<?= e($formId) ?>" action="<?= url('send.php') ?>" method="post" novalidate>
    <?php partial('section-label', ['text' => content('form.label'), 'tag' => 'h2']) ?>

    <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
    <input type="hidden" name="form_id" value="<?= e($formId) ?>">
    <input type="hidden" name="source" value="<?= e($source) ?>">
    <input type="hidden" name="back" value="<?= e($_SERVER['REQUEST_URI'] ?? url()) ?>">
    <input type="hidden" name="ts" value="<?= time() ?>">
    <div class="hp" aria-hidden="true">
        <label>Vebsayt <input type="text" name="website" tabindex="-1" autocomplete="off"></label>
    </div>

    <div class="apply-form__grid">
        <div class="field">
            <label class="sr-only" for="<?= $formId ?>-name">Ad</label>
            <input id="<?= $formId ?>-name" type="text" name="name" placeholder="Ad" required maxlength="60" autocomplete="given-name" value="<?= e($old['name'] ?? '') ?>">
        </div>
        <div class="field">
            <label class="sr-only" for="<?= $formId ?>-surname">Soyad</label>
            <input id="<?= $formId ?>-surname" type="text" name="surname" placeholder="Soyad" required maxlength="60" autocomplete="family-name" value="<?= e($old['surname'] ?? '') ?>">
        </div>
        <div class="field<?= $type ? ' field--full' : '' ?>">
            <label class="sr-only" for="<?= $formId ?>-phone">Telefon</label>
            <input id="<?= $formId ?>-phone" type="tel" name="phone" placeholder="Telefon" required maxlength="20" autocomplete="tel" inputmode="tel" value="<?= e($old['phone'] ?? '') ?>">
        </div>
        <?php if ($type): ?>
            <input type="hidden" name="type" value="<?= e($type) ?>">
        <?php else: ?>
            <div class="field field--select">
                <label class="sr-only" for="<?= $formId ?>-type">Növ</label>
                <select id="<?= $formId ?>-type" name="type">
                    <?php foreach (['Sahibkar', 'Sürücü'] as $option): ?>
                        <option value="<?= e($option) ?>"<?= ($old['type'] ?? '') === $option ? ' selected' : '' ?>><?= e($option) ?></option>
                    <?php endforeach; ?>
                </select>
                <?= icon('caret-down', 'select-arrow') ?>
            </div>
        <?php endif; ?>
    </div>

    <button class="btn btn--green" type="submit"><?= e(content('form.button')) ?></button>
    <p class="form-message<?= $notice ? ' is-' . e($notice['status']) : '' ?>" role="status" aria-live="polite"><?= $notice ? e($notice['message']) : '' ?></p>
</form>
