(function () {
    'use strict';
    var I18N = window.ADMIN_I18N || {};

    // ---------- Mobil menyu ----------
    document.addEventListener('click', function (e) {
        if (e.target.closest('[data-sidebar-open]')) document.body.classList.add('sidebar-open');
        if (e.target.closest('[data-sidebar-close]')) document.body.classList.remove('sidebar-open');
    });

    // ---------- Silmə təsdiqi ----------
    document.addEventListener('submit', function (e) {
        var form = e.target;
        if (form.hasAttribute('data-confirm') && !window.confirm(form.getAttribute('data-confirm') || I18N.confirm)) {
            e.preventDefault();
        }
    }, true);

    // ---------- Simvol sayğacı ----------
    document.querySelectorAll('[data-counter]').forEach(function (input) {
        var limit = parseInt(input.getAttribute('data-counter'), 10);
        var label = input.closest('.field').querySelector('label');
        var counter = document.createElement('span');
        counter.className = 'counter';
        label.appendChild(counter);
        function update() {
            counter.textContent = input.value.length + ' / ' + limit + ' ' + (I18N.chars || '');
            counter.classList.toggle('is-over', input.value.length > limit);
        }
        input.addEventListener('input', update);
        update();
    });

    // ---------- Şəkil önizləməsi ----------
    document.addEventListener('change', function (e) {
        var input = e.target;
        if (!input.matches('input[type="file"][data-preview]') || !input.files || !input.files[0]) return;
        var box = input.closest('.image-picker, .rep-row');
        var preview = box && (box.querySelector('.image-picker__preview') || box.querySelector('.icon-select img'));
        if (!preview) return;
        var url = URL.createObjectURL(input.files[0]);
        if (preview.tagName === 'IMG') {
            preview.src = url;
        } else {
            preview.innerHTML = '<img src="' + url + '" alt="">';
        }
    });

    // ---------- İkon seçimi önizləməsi ----------
    document.addEventListener('change', function (e) {
        if (!e.target.matches('.icon-select select')) return;
        var img = e.target.closest('.icon-select').querySelector('img');
        img.src = e.target.selectedOptions[0].getAttribute('data-src');
    });

    // ---------- Kart redaktoru: əlavə et / sil / yerini dəyiş ----------
    document.querySelectorAll('[data-repeater]').forEach(function (rep) {
        var list = rep.querySelector('.repeater');
        var tpl = rep.querySelector('template');

        function refresh() {
            var rows = list.querySelectorAll('.rep-row');
            rows.forEach(function (row, i) {
                row.querySelector('[data-move="up"]').disabled = i === 0;
                row.querySelector('[data-move="down"]').disabled = i === rows.length - 1;
            });
        }

        rep.addEventListener('click', function (e) {
            var btn = e.target.closest('button');
            if (!btn) return;
            var row = btn.closest('.rep-row');
            if (btn.hasAttribute('data-add')) {
                var key = 'n' + Date.now().toString(36);
                var html = tpl.innerHTML.replace(/__KEY__/g, key);
                list.insertAdjacentHTML('beforeend', html);
                refresh();
                var last = list.lastElementChild.querySelector('input[type="text"]');
                if (last) last.focus();
            } else if (btn.getAttribute('data-move') === 'up' && row.previousElementSibling) {
                list.insertBefore(row, row.previousElementSibling);
                refresh();
            } else if (btn.getAttribute('data-move') === 'down' && row.nextElementSibling) {
                list.insertBefore(row.nextElementSibling, row);
                refresh();
            } else if (btn.hasAttribute('data-remove')) {
                if (window.confirm(I18N.confirm)) {
                    row.remove();
                    refresh();
                }
            }
        });
        refresh();
    });

    // ---------- Slug avtomatik ----------
    var titleInput = document.querySelector('[data-slug-source]');
    var slugInput = document.querySelector('[data-slug-target]');
    if (titleInput && slugInput) {
        var manual = slugInput.value !== '';
        slugInput.addEventListener('input', function () { manual = slugInput.value !== ''; });
        titleInput.addEventListener('input', function () {
            if (!manual) slugInput.placeholder = slugify(titleInput.value);
        });
        slugInput.placeholder = slugInput.placeholder || slugify(titleInput.value);
    }
    function slugify(s) {
        var map = { 'ə': 'e', 'ı': 'i', 'ö': 'o', 'ü': 'u', 'ğ': 'g', 'ş': 's', 'ç': 'c', 'İ': 'i', 'I': 'i', 'Ə': 'e', 'Ö': 'o', 'Ü': 'u', 'Ğ': 'g', 'Ş': 's', 'Ç': 'c' };
        return s.replace(/[əıöüğşçİIƏÖÜĞŞÇ]/g, function (c) { return map[c]; })
            .toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/^-+|-+$/g, '').slice(0, 90);
    }

    // ---------- SEO önizləməsi ----------
    document.querySelectorAll('[data-seo-preview]').forEach(function (box) {
        var card = box.closest('.seo-card');
        var t = card.querySelector('[data-seo="title"]');
        var d = card.querySelector('[data-seo="description"]');
        function update() {
            box.querySelector('.seo-preview__title').textContent = t.value || t.placeholder;
            box.querySelector('.seo-preview__desc').textContent = d.value || d.placeholder;
        }
        t.addEventListener('input', update);
        d.addEventListener('input', update);
        update();
    });

    // ---------- Mətn redaktoru (sadə WYSIWYG) ----------
    document.querySelectorAll('textarea[data-editor]').forEach(function (textarea) {
        var wrap = document.createElement('div');
        wrap.className = 'editor' + (textarea.hasAttribute('data-editor-small') ? ' editor--small' : '');
        var buttons = [
            ['bold', '<b>B</b>', 'Bold'],
            ['italic', '<i>I</i>', 'Italic'],
            ['underline', '<u>U</u>', 'Underline'],
            ['|'],
            ['h2', 'H2', 'Heading 2'],
            ['h3', 'H3', 'Heading 3'],
            ['p', '¶', 'Paragraph'],
            ['|'],
            ['insertUnorderedList', '• —', 'List'],
            ['insertOrderedList', '1.', 'Numbered list'],
            ['|'],
            ['link', '🔗', 'Link'],
            ['unlink', '⛓̸', 'Unlink'],
            ['removeFormat', '⌫', 'Clear formatting'],
            ['|'],
            ['source', '&lt;/&gt;', 'HTML']
        ];
        var bar = document.createElement('div');
        bar.className = 'editor__bar';
        buttons.forEach(function (b) {
            if (b[0] === '|') {
                var s = document.createElement('span');
                s.className = 'sep';
                bar.appendChild(s);
                return;
            }
            var btn = document.createElement('button');
            btn.type = 'button';
            btn.innerHTML = b[1];
            btn.title = b[2];
            btn.setAttribute('data-cmd', b[0]);
            bar.appendChild(btn);
        });
        var area = document.createElement('div');
        area.className = 'editor__area';
        area.contentEditable = 'true';
        area.innerHTML = textarea.value;
        area.setAttribute('data-placeholder', textarea.getAttribute('placeholder') || '');
        textarea.classList.add('editor__source');
        textarea.removeAttribute('required');
        textarea.parentNode.insertBefore(wrap, textarea);
        wrap.appendChild(bar);
        wrap.appendChild(area);
        wrap.appendChild(textarea);

        try { document.execCommand('defaultParagraphSeparator', false, 'p'); } catch (err) { /* köhnə brauzerlər */ }

        function sync() {
            if (!wrap.classList.contains('is-source')) textarea.value = area.innerHTML;
        }
        area.addEventListener('input', sync);
        area.addEventListener('blur', sync);

        // Başqa saytdan kopyalanan mətndə üslubları təmizləyirik.
        area.addEventListener('paste', function (e) {
            var html = e.clipboardData && e.clipboardData.getData('text/html');
            var text = e.clipboardData && e.clipboardData.getData('text/plain');
            if (!html && !text) return;
            e.preventDefault();
            if (html) {
                var tmp = document.createElement('div');
                tmp.innerHTML = html;
                tmp.querySelectorAll('script,style,meta,link,img,iframe').forEach(function (n) { n.remove(); });
                tmp.querySelectorAll('*').forEach(function (n) {
                    Array.prototype.slice.call(n.attributes).forEach(function (a) {
                        if (a.name !== 'href') n.removeAttribute(a.name);
                    });
                });
                document.execCommand('insertHTML', false, tmp.innerHTML);
            } else {
                document.execCommand('insertText', false, text);
            }
        });

        bar.addEventListener('mousedown', function (e) {
            if (e.target.closest('button')) e.preventDefault(); // seçim itməsin
        });
        bar.addEventListener('click', function (e) {
            var btn = e.target.closest('button');
            if (!btn) return;
            var cmd = btn.getAttribute('data-cmd');
            if (cmd === 'source') {
                if (wrap.classList.contains('is-source')) {
                    area.innerHTML = textarea.value;
                    wrap.classList.remove('is-source');
                } else {
                    textarea.value = area.innerHTML;
                    wrap.classList.add('is-source');
                }
                btn.classList.toggle('is-on', wrap.classList.contains('is-source'));
                return;
            }
            area.focus();
            if (cmd === 'h2' || cmd === 'h3' || cmd === 'p') {
                document.execCommand('formatBlock', false, '<' + cmd + '>');
            } else if (cmd === 'link') {
                var url = window.prompt('URL (https://…)', 'https://');
                if (url && url !== 'https://') document.execCommand('createLink', false, url);
            } else {
                document.execCommand(cmd, false, null);
            }
            sync();
        });

        var form = textarea.closest('form');
        if (form) {
            form.addEventListener('submit', function () {
                if (!wrap.classList.contains('is-source')) textarea.value = area.innerHTML;
            });
        }
    });
})();
