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

    // ---------- Müraciət formu (səhifə yenilənmədən göndərilir) ----------
    document.querySelectorAll('.apply-form').forEach(function (form) {
        var message = form.querySelector('.form-message');
        var button = form.querySelector('button[type="submit"]');

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

        form.addEventListener('submit', function (e) {
            e.preventDefault();
            if (!validate()) {
                show('Zəhmət olmasa, bütün sahələri düzgün doldurun.', 'error');
                var invalid = form.querySelector('.is-invalid');
                if (invalid) invalid.focus();
                return;
            }

            button.disabled = true;
            show('Göndərilir…', 'pending');

            fetch(form.action, {
                method: 'POST',
                body: new FormData(form),
                headers: { 'X-Requested-With': 'fetch', 'Accept': 'application/json' },
                credentials: 'same-origin'
            })
                .then(function (res) {
                    return res.json().catch(function () {
                        throw new Error('bad-json');
                    });
                })
                .then(function (data) {
                    show(data.message, data.ok ? 'success' : 'error');
                    if (data.ok) {
                        form.querySelectorAll('input[type="text"], input[type="tel"]').forEach(function (input) {
                            if (input.name !== 'website') input.value = '';
                        });
                    }
                })
                .catch(function () {
                    show('Xəta baş verdi. Zəhmət olmasa, bir az sonra yenidən cəhd edin.', 'error');
                })
                .then(function () {
                    button.disabled = false;
                });
        });
    });
})();
