<?php
// Bloq yazıları: siyahı, yeni yazı, redaktə, silmə.

const RESERVED_SLUGS = ['haqqimizda', 'sahibkarlar-ucun', 'suruculer', 'faq', 'elaqe', 'blog', 'admin', 'assets', 'storage', 'includes', 'pages', 'data', 'sitemap', 'category', 'author', 'feed', 'send'];

function post_state(array $p): string
{
    if (($p['status'] ?? 'published') === 'draft') {
        return 'draft';
    }
    return $p['date'] > date('Y-m-d H:i:s') ? 'scheduled' : 'published';
}

$id = (string) ($_GET['id'] ?? '');

// ---------- Silmə ----------
if ($action === 'delete' && is_post()) {
    verify_csrf();
    $removed = null;
    store_update('posts', function (array $list) use ($id, &$removed) {
        return array_values(array_filter($list, function ($p) use ($id, &$removed) {
            if ($p['id'] === $id) {
                $removed = $p;
                return false;
            }
            return true;
        }));
    });
    if ($removed) {
        delete_upload($removed['image']);
        if ($removed['thumb'] !== $removed['image']) {
            delete_upload($removed['thumb']);
        }
    }
    admin_flash('success', t('common.deleted'));
    admin_redirect('posts');
}

// ---------- Yeni / redaktə ----------
if ($action === 'edit') {
    $post = null;
    foreach (store_get('posts') as $p) {
        if ($p['id'] === $id) {
            $post = $p;
        }
    }
    if ($id !== '' && !$post) {
        admin_flash('error', t('common.not_found'));
        admin_redirect('posts');
    }
    $isNew = !$post;
    $post ??= ['id' => '', 'status' => 'published', 'slug' => '', 'title' => '', 'date' => date('Y-m-d H:i:s'), 'image' => '', 'thumb' => '', 'description' => '', 'content' => ''];
    $errors = [];

    if (is_post()) {
        verify_csrf();
        $in = [
            'title'       => post_str('title', 200),
            'slug'        => slugify(post_str('slug', 120)),
            'status'      => ($_POST['status'] ?? '') === 'draft' ? 'draft' : 'published',
            'date'        => post_str('date', 20),
            'description' => post_str('description', 300),
            'content'     => sanitize_html((string) ($_POST['content'] ?? '')),
        ];
        if ($in['title'] === '') {
            $errors['title'] = t('posts.err_title');
        }
        if ($in['slug'] === '') {
            $in['slug'] = slugify($in['title']);
        }
        $taken = in_array($in['slug'], RESERVED_SLUGS, true);
        foreach (store_get('posts') as $p) {
            if ($p['slug'] === $in['slug'] && $p['id'] !== $post['id']) {
                $taken = true;
            }
        }
        if ($taken) {
            $errors['slug'] = t('posts.err_slug');
        }
        $time = strtotime($in['date']);
        $in['date'] = date('Y-m-d H:i:s', $time ?: time());

        $oldImage = $post['image'];
        $oldThumb = $post['thumb'];
        $image = (string) handle_image_field('image', $oldImage, $errors, 'blog', 1600);
        if ($image === '') {
            $errors['image'] ??= t('posts.err_image');
        }

        $post = array_merge($post, $in, ['image' => $image]);
        if (!$errors) {
            if ($image !== $oldImage) {
                // Yeni şəkil: kart üçün kiçik versiya yaradılır, köhnə fayllar silinir.
                $post['thumb'] = make_thumb($image);
                delete_upload($oldImage);
                if ($oldThumb !== $oldImage) {
                    delete_upload($oldThumb);
                }
            }
            if ($post['thumb'] === '') {
                $post['thumb'] = $image;
            }
            if ($post['description'] === '') {
                $post['description'] = excerpt($post['content']);
            }
            $post['updated_at'] = date('Y-m-d H:i:s');
            if ($isNew) {
                $post['id'] = new_id();
            }
            $saved = $post;
            store_update('posts', function (array $list) use ($saved, $isNew) {
                if ($isNew) {
                    array_unshift($list, $saved);
                    return $list;
                }
                foreach ($list as &$p) {
                    if ($p['id'] === $saved['id']) {
                        $p = $saved;
                    }
                }
                return $list;
            });
            admin_flash('success', t('common.saved'));
            admin_redirect('posts', ['a' => 'edit', 'id' => $saved['id']]);
        }
    }

    $state = post_state($post);
    $actions = [];
    if (!$isNew && $state === 'published') {
        $actions[] = ['href' => url($post['slug'] . '/'), 'label' => t('common.view'), 'icon' => 'eye', 'blank' => true];
    }

    admin_page($isNew ? t('posts.add') : t('posts.edit'), 'posts', function () use ($post, $errors, $isNew) {
        ?>
        <p style="margin-bottom:18px"><a class="btn btn-ghost btn-sm" href="<?= e(admin_url('posts')) ?>">← <?= e(t('common.back')) ?></a></p>
        <form method="post" enctype="multipart/form-data" action="<?= e(admin_url('posts', ['a' => 'edit', 'id' => $post['id']])) ?>">
            <?= csrf_field() ?>
            <div class="form-layout">
                <div class="card">
                    <div class="field<?= isset($errors['title']) ? ' has-error' : '' ?>">
                        <label for="f-title"><?= e(t('posts.title')) ?></label>
                        <input id="f-title" type="text" name="title" value="<?= e($post['title']) ?>" maxlength="200" required data-slug-source>
                        <?php if (isset($errors['title'])): ?><p class="field-error"><?= e($errors['title']) ?></p><?php endif; ?>
                    </div>
                    <div class="field<?= isset($errors['slug']) ? ' has-error' : '' ?>">
                        <label for="f-slug"><?= e(t('posts.slug')) ?></label>
                        <input id="f-slug" type="text" name="slug" value="<?= e($post['slug']) ?>" maxlength="120" data-slug-target>
                        <?php if (isset($errors['slug'])): ?><p class="field-error"><?= e($errors['slug']) ?></p><?php endif; ?>
                        <p class="field-hint"><?= e(absolute_url('')) ?><b><?= e($post['slug'] ?: '…') ?></b>/ — <?= e(t('posts.slug_hint')) ?></p>
                    </div>
                    <div class="field">
                        <label for="f-content"><?= e(t('posts.content')) ?></label>
                        <textarea id="f-content" name="content" data-editor><?= e($post['content']) ?></textarea>
                    </div>
                    <?= field_textarea('description', t('posts.description'), $post['description'], ['rows' => 3, 'maxlength' => 300, 'counter' => 160, 'hint' => t('posts.description_hint')]) ?>
                </div>
                <aside>
                    <div class="card">
                        <?= field_select('status', t('posts.status'), $post['status'] === 'draft' ? 'draft' : 'published', ['published' => t('posts.published'), 'draft' => t('posts.draft')]) ?>
                        <?= field_text('date', t('posts.date'), date('Y-m-d\TH:i', strtotime($post['date'])), ['type' => 'datetime-local']) ?>
                        <?= field_image('image', t('posts.image'), $post['image'], ['error' => $errors['image'] ?? null]) ?>
                        <div class="form-actions">
                            <button class="btn btn-primary btn-block" type="submit"><?= e(t('common.save')) ?></button>
                        </div>
                    </div>
                </aside>
            </div>
        </form>
        <?php
    }, $actions);
    return;
}

// ---------- Siyahı ----------
$q = trim(mb_substr((string) ($_GET['q'] ?? ''), 0, 80));
$list = all_posts();
if ($q !== '') {
    $list = array_values(array_filter($list, fn ($p) => str_contains(mb_strtolower($p['title']), mb_strtolower($q))));
}
$perPage = 20;
$pages = max(1, (int) ceil(count($list) / $perPage));
$page = min($pages, max(1, (int) ($_GET['p'] ?? 1)));
$list = array_slice($list, ($page - 1) * $perPage, $perPage);

admin_page(t('nav.posts'), 'posts', function () use ($list, $q, $page, $pages) {
    ?>
    <div class="card">
        <div class="toolbar">
            <form class="toolbar" method="get" action="<?= e(ADMIN_URL) ?>" style="margin:0">
                <input type="hidden" name="r" value="posts">
                <input type="search" name="q" value="<?= e($q) ?>" placeholder="<?= e(t('posts.search_ph')) ?>">
                <button class="btn btn-outline" type="submit"><?= e(t('common.search')) ?></button>
            </form>
            <span class="spacer"></span>
            <a class="btn btn-primary" href="<?= e(admin_url('posts', ['a' => 'edit'])) ?>"><?= admin_icon('plus') ?><?= e(t('posts.add')) ?></a>
        </div>
        <?php if (!$list): ?>
            <p class="empty"><?= e(t('common.empty')) ?></p>
        <?php else: ?>
            <div class="table-wrap">
                <table class="table">
                    <thead><tr><th></th><th><?= e(t('posts.title')) ?></th><th><?= e(t('posts.status')) ?></th><th><?= e(t('posts.date')) ?></th><th class="col-actions"></th></tr></thead>
                    <tbody>
                    <?php foreach ($list as $p): $state = post_state($p); ?>
                        <tr>
                            <td style="width:80px"><img class="thumb" src="<?= e(asset($p['thumb'] ?: $p['image'])) ?>" alt="" loading="lazy"></td>
                            <td>
                                <a class="row-title" href="<?= e(admin_url('posts', ['a' => 'edit', 'id' => $p['id']])) ?>"><?= e($p['title']) ?></a>
                                <span class="row-sub">/<?= e($p['slug']) ?>/</span>
                            </td>
                            <td><span class="badge badge--<?= $state ?>"><?= e(t('posts.' . $state)) ?></span></td>
                            <td class="nowrap"><?= e(format_dt($p['date'])) ?></td>
                            <td class="col-actions">
                                <?php if ($state === 'published'): ?>
                                    <a class="icon-btn" href="<?= e(url($p['slug'] . '/')) ?>" target="_blank" rel="noopener" title="<?= e(t('common.view')) ?>"><?= admin_icon('eye') ?></a>
                                <?php endif; ?>
                                <a class="icon-btn" href="<?= e(admin_url('posts', ['a' => 'edit', 'id' => $p['id']])) ?>" title="<?= e(t('common.edit')) ?>"><?= admin_icon('edit') ?></a>
                                <form class="inline-form" method="post" action="<?= e(admin_url('posts', ['a' => 'delete', 'id' => $p['id']])) ?>" data-confirm>
                                    <?= csrf_field() ?>
                                    <button class="icon-btn is-danger" type="submit" title="<?= e(t('common.delete')) ?>"><?= admin_icon('trash') ?></button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?= pager($page, $pages, 'posts', $q !== '' ? ['q' => $q] : []) ?>
        <?php endif; ?>
    </div>
    <?php
});
