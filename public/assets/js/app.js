(function () {
    'use strict';

    function debounce(fn, wait) {
        let timeout;
        return function (...args) {
            clearTimeout(timeout);
            timeout = setTimeout(() => fn.apply(this, args), wait);
        };
    }

    document.addEventListener('DOMContentLoaded', function () {
        const form = document.getElementById('qr-form');
        if (!form) return;

        const typeSelect = document.getElementById('qr-type');
        const preview = document.getElementById('qr-preview');
        const sizeInput = document.getElementById('qr-size');
        const sizeValue = document.getElementById('size-value');

        function toggleFields() {
            const selected = typeSelect.value;
            document.querySelectorAll('[data-fields]').forEach((el) => {
                const isActive = el.dataset.fields === selected;
                el.classList.toggle('d-none', !isActive);
                // Vários tipos reutilizam o mesmo name (ex: "phone" em Telefone/SMS/WhatsApp/vCard).
                // Sem desabilitar os campos ocultos, o navegador envia todos e o PHP fica
                // com o valor do último campo do DOM (geralmente vazio), não o preenchido.
                el.querySelectorAll('.qr-field').forEach((field) => {
                    field.disabled = !isActive;
                });
            });
        }

        function renderPreview() {
            const formData = new FormData(form);
            formData.set('_csrf', window.PRISMA_CSRF);

            fetch(window.PRISMA_PREVIEW_URL, {
                method: 'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                body: formData,
            })
                .then((res) => res.json())
                .then((data) => {
                    if (data.svg) {
                        preview.innerHTML = data.svg;
                        const svg = preview.querySelector('svg');
                        if (svg) {
                            svg.style.maxWidth = '100%';
                            svg.style.height = 'auto';
                        }
                    } else {
                        preview.innerHTML = '<span style="color:var(--color-text-muted);">Preencha os campos para ver a prévia</span>';
                    }
                })
                .catch(() => {
                    preview.innerHTML = '<span style="color:var(--color-danger);">Erro ao gerar prévia</span>';
                });
        }

        const debouncedPreview = debounce(renderPreview, 400);

        typeSelect.addEventListener('change', function () {
            toggleFields();
            debouncedPreview();
        });

        form.querySelectorAll('.qr-field').forEach((field) => {
            field.addEventListener('input', debouncedPreview);
            field.addEventListener('change', debouncedPreview);
        });

        if (sizeInput && sizeValue) {
            sizeInput.addEventListener('input', function () {
                sizeValue.textContent = sizeInput.value;
            });
        }

        setupPhoneFields(form);

        toggleFields();
    });

    // ── Máscara e validação de telefone ─────────────────────────────────────
    function formatBRLocalPhone(digits) {
        digits = digits.slice(0, 11);
        const ddd = digits.slice(0, 2);
        const rest = digits.slice(2);
        let out = ddd.length ? '(' + ddd + (ddd.length === 2 ? ') ' : '') : '';
        if (rest.length > 4) {
            out += rest.length > 8 ? rest.slice(0, 5) + '-' + rest.slice(5, 9) : rest.slice(0, 4) + '-' + rest.slice(4, 8);
        } else {
            out += rest;
        }
        return out;
    }

    function setupPhoneFields(form) {
        form.querySelectorAll('.qr-field[name="phone"]').forEach((field) => {
            const isWhatsapp = field.closest('[data-fields]')?.dataset.fields === 'whatsapp';

            const feedback = document.createElement('div');
            feedback.className = 'small mt-1';
            field.insertAdjacentElement('afterend', feedback);

            const setError = (msg) => {
                feedback.textContent = msg;
                feedback.style.color = msg ? 'var(--color-danger)' : '';
                field.classList.toggle('is-invalid', !!msg);
            };

            field.addEventListener('input', function () {
                setError('');
                const digits = field.value.replace(/\D/g, '');
                if (isWhatsapp) {
                    // DDI (Brasil = 55) muda por país — não reformata automaticamente
                    // pra não confundir DDI com DDD, só limita a dígitos.
                    field.value = digits.slice(0, 15);
                } else {
                    field.value = formatBRLocalPhone(digits);
                }
            });

            field.addEventListener('blur', function () {
                const digits = field.value.replace(/\D/g, '');
                if (digits === '') { setError(''); return; }

                if (isWhatsapp) {
                    if (digits.length < 12 || digits.length > 13) {
                        setError('Inclua o código do país (ex: 55 para o Brasil) + DDD + número. Ex: 5569999990000');
                    }
                } else if (digits.length < 10 || digits.length > 11) {
                    setError('Telefone incompleto — informe DDD + número. Ex: (69) 99999-0000');
                }
            });
        });
    }
})();
