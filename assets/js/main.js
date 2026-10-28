(function () {
    'use strict';

    // ---------- Mobil menyu ----------
    var menu = document.getElementById('mobile-menu');
    var toggle = document.querySelector('.menu-toggle');

    function openMenu() {
        menu.hidden = false;
        void menu.offsetWidth; // keçid animasiyasının işləməsi üçün reflow
        menu.classList.add('is-open');
        document.body.classList.add('menu-open');
        toggle.setAttribute('aria-expanded', 'true');
        var first = menu.querySelector('a');
        if (first) first.focus();
    }

    function closeMenu() {
        menu.classList.remove('is-open');
        document.body.classList.remove('menu-open');
        toggle.setAttribute('aria-expanded', 'false');
        setTimeout(function () { menu.hidden = true; }, 350);
        toggle.focus();
    }

    if (menu && toggle) {
        toggle.addEventListener('click', openMenu);
        menu.querySelectorAll('[data-menu-close]').forEach(function (el) {
            el.addEventListener('click', closeMenu);
        });
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && !menu.hidden) closeMenu();
        });
    }

    // ---------- Görünmə animasiyası ----------
    var revealItems = document.querySelectorAll('[data-reveal]');
    if ('IntersectionObserver' in window) {
        var observer = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                if (entry.isIntersecting) {
                    entry.target.classList.add('is-visible');
                    observer.unobserve(entry.target);
                }
            });
        }, { rootMargin: '0px 0px -60px 0px' });
        revealItems.forEach(function (el) { observer.observe(el); });
    } else {
        revealItems.forEach(function (el) { el.classList.add('is-visible'); });
    }

    // ---------- Müraciət formu: WhatsApp-a göndərilir ----------
    // WhatsApp hazır mesajla açılır (mətn şablonu formanın data-whatsapp atributundadır), müraciət isə
    // arxa fonda admin panelə yazılır — ziyarətçi WhatsApp-da "Göndər"i basmasa da itmir.
    document.querySelectorAll('.apply-form').forEach(function (form) {
        var message = form.querySelector('.form-message');

        function show(text, status) {
            message.textContent = text;
            message.className = 'form-message is-' + status;
        }

        function validate() {
            var ok = true;
            form.querySelectorAll('input[required]').forEach(function (input) {
                var value = input.value.trim();
                var valid = value.length >= 2;
                if (input.type === 'tel') {
                    var digits = value.replace(/\D/g, '');
                    valid = /^[+\d\s()\-]+$/.test(value) && digits.length >= 7 && digits.length <= 15;
                }
                input.classList.toggle('is-invalid', !valid);
                input.setAttribute('aria-invalid', valid ? 'false' : 'true');
                if (!valid) ok = false;
            });
            return ok;
        }

        form.addEventListener('input', function (e) {
            if (e.target.classList.contains('is-invalid')) {
                e.target.classList.remove('is-invalid');
                e.target.removeAttribute('aria-invalid');
            }
        });

        function whatsappUrl(data) {
            return form.getAttribute('data-whatsapp').replace(/%7B(name|surname|phone|type)%7D/g, function (_, field) {
                return encodeURIComponent(String(data.get(field) || '').trim());
            });
        }

        form.addEventListener('submit', function (e) {
            e.preventDefault();
            if (!validate()) {
                show('Zəhmət olmasa, bütün sahələri düzgün doldurun.', 'error');
                var invalid = form.querySelector('.is-invalid');
                if (invalid) invalid.focus();
                return;
            }

            var data = new FormData(form);
            var url = whatsappUrl(data);

            // Pəncərə klikin içində açılmalıdır, yoxsa brauzer onu bloklayır.
            var win = window.open(url, '_blank');
            if (win) {
                try { win.opener = null; } catch (err) { /* başqa domen */ }
            }

            // keepalive: ziyarətçi səhifədən çıxsa da sorğu sona çatır.
            fetch(form.action, {
                method: 'POST',
                body: data,
                headers: { 'X-Requested-With': 'fetch', 'Accept': 'application/json' },
                credentials: 'same-origin',
                keepalive: true
            }).catch(function () { /* müraciət WhatsApp-a onsuz da gedir */ });

            show(form.getAttribute('data-success'), 'success');
            var link = document.createElement('a');
            link.href = url;
            link.target = '_blank';
            link.rel = 'noopener';
            link.textContent = 'WhatsApp açılmadısa, buraya klikləyin.';
            message.appendChild(document.createTextNode(' '));
            message.appendChild(link);

            form.querySelectorAll('input[type="text"], input[type="tel"]').forEach(function (input) {
                if (input.name !== 'website') input.value = '';
            });

            if (!win) {
                window.location.href = url;
            }
        });
    });
})();
