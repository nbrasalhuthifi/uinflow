(() => {
    const storageKey = 'uniflow-theme';

    function applyTheme() {
        const theme = localStorage.getItem(storageKey) || 'dark';
        document.documentElement.dataset.theme = theme;
    }

    window.toggleTheme = function toggleTheme() {
        const current = document.documentElement.dataset.theme || 'dark';
        const next = current === 'dark' ? 'light' : 'dark';
        localStorage.setItem(storageKey, next);
        applyTheme();
    };

    window.toggleSidebar = function toggleSidebar() {
        document.getElementById('sidebar')?.classList.toggle('open');
    };

    window.openModal = function openModal(id) {
        const modal = document.getElementById(id);
        if (!modal) {
            return;
        }
        modal.classList.add('show');
        document.body.classList.add('modal-open');
        const firstFocusable = modal.querySelector('input:not([type=hidden]), select, textarea, button, a');
        window.setTimeout(() => firstFocusable?.focus(), 0);
    };

    window.closeModal = function closeModal(id) {
        const modal = document.getElementById(id);
        if (!modal) {
            return;
        }
        modal.classList.remove('show');
        document.body.classList.remove('modal-open');
    };

    window.fillForm = function fillForm(formId, data) {
        const form = document.getElementById(formId);
        if (!form) {
            return;
        }

        Object.entries(data).forEach(([key, value]) => {
            const field = form.querySelector(`[name="${key}"]`);
            if (field) {
                field.value = value ?? '';
            }
        });
    };

    function buildLookupUrl(select) {
        const params = new URLSearchParams();
        params.set('page', 'lookup');
        params.set('action', 'search');
        params.set('entity', select.dataset.entity || '');
        params.set('q', select.querySelector('.lookup-input')?.value.trim() || '');
        params.set('limit', '10');

        const dependent = select.dataset.dependsOn;
        if (dependent) {
            const field = document.querySelector(
                `[data-lookup-field="${dependent}"]`
            );
            if (field?.value) {
                params.set(dependent, field.value);
            }
        }

        return `/?${params.toString()}`;
    }

    async function searchLookup(select) {
        const results = select.querySelector('.lookup-results');
        if (!results) {
            return;
        }

        const input = select.querySelector('.lookup-input');
        if (!input || input.value.trim().length < 1) {
            results.innerHTML = '<div class="lookup-empty">اكتب للبحث...</div>';
            select.classList.add('open');
            return;
        }

        results.innerHTML = '<div class="lookup-empty">جاري البحث...</div>';
        select.classList.add('open');

        try {
            const response = await fetch(buildLookupUrl(select), {
                headers: {
                    Accept: 'application/json'
                },
                credentials: 'same-origin'
            });
            const raw = await response.text();
            let data;

            try {
                data = JSON.parse(raw);
            } catch (parseError) {
                throw new Error('استجابة غير صالحة من الخادم.');
            }

            if (!response.ok || !data.ok) {
                throw new Error(data.message || 'تعذر البحث');
            }

            results.innerHTML = '';

            if (!data.items.length) {
                results.innerHTML = '<div class="lookup-empty">لا توجد نتائج.</div>';
                return;
            }

            data.items.forEach((item) => {
                const button = document.createElement('button');
                button.type = 'button';
                button.className = 'lookup-option';
                button.textContent = item.label;
                button.dataset.id = item.id;
                button.dataset.label = item.label;
                button.dataset.code = item.code || '';
                button.dataset.name = item.name || '';
                button.dataset.credits = item.credits || '';
                button.dataset.required = item.is_required || '';
                button.dataset.yearName = item.year_name || '';
                button.dataset.status = item.status || '';
                results.appendChild(button);
            });
        } catch (error) {
            results.innerHTML = '<div class="lookup-empty error">تعذر البحث. تأكد من اتصال قاعدة البيانات.</div>';
        }
    }

    let lookupTimer = null;

    document.addEventListener('input', (event) => {
        const input = event.target.closest('.lookup-input');
        if (!input) {
            return;
        }

        const select = input.closest('.lookup');
        const hidden = select.querySelector('.lookup-value');
        if (hidden) {
            hidden.value = '';
        }

        clearTimeout(lookupTimer);
        lookupTimer = setTimeout(() => searchLookup(select), 250);
    });

    document.addEventListener('focusin', (event) => {
        const input = event.target.closest('.lookup-input');
        if (!input) {
            return;
        }
        input.closest('.lookup')?.classList.add('open');
    });

    document.addEventListener('click', (event) => {
        const option = event.target.closest('.lookup-option');
        if (option) {
            const select = option.closest('.lookup');
            select.querySelector('.lookup-input').value = option.dataset.label;
            select.querySelector('.lookup-value').value = option.dataset.id;
            select.classList.remove('open');

            const fieldName = select.dataset.field;
            if (fieldName) {
                document.dispatchEvent(
                    new CustomEvent('lookup:selected', {
                        detail: {
                            field: fieldName,
                            id: option.dataset.id,
                            label: option.dataset.label,
                            code: option.dataset.code || '',
                            name: option.dataset.name || '',
                            credits: option.dataset.credits || '',
                            required: option.dataset.required || '',
                            yearName: option.dataset.yearName || '',
                            status: option.dataset.status || ''
                        }
                    })
                );
            }
            return;
        }

        if (!event.target.closest('.lookup')) {
            document.querySelectorAll('.lookup.open').forEach((item) => {
                item.classList.remove('open');
            });
        }

        if (event.target.classList.contains('modal')) {
            event.target.classList.remove('show');
            document.body.classList.remove('modal-open');
        }
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            document.querySelectorAll('.modal.show').forEach((modal) => {
                modal.classList.remove('show');
            });
            document.body.classList.remove('modal-open');
        }
    });

    applyTheme();
})();

// Accessibility and navigation enhancements
(() => {
    let lastFocused = null;
    const observer = new MutationObserver(() => {
        document.querySelectorAll('.modal.show').forEach((modal) => {
            modal.setAttribute('role', 'dialog');
            modal.setAttribute('aria-modal', 'true');
        });
    });
    observer.observe(document.body, {subtree:true, attributes:true, attributeFilter:['class']});
    document.addEventListener('click', (event) => {
        const opener = event.target.closest('[onclick*="openModal"]');
        if (opener) lastFocused = opener;
        // Do not steal focus on every click inside a modal. The old behavior
        // focused the first field whenever the user clicked any other field,
        // which made inputs/selects feel like they were "running away".
    });
    document.addEventListener('keydown', (event) => {
        if ((event.ctrlKey || event.metaKey) && event.key.toLowerCase() === 'k') {
            event.preventDefault(); window.location.href='/?page=search';
        }
        if (event.key === 'Escape') { setTimeout(() => lastFocused?.focus(), 0); }
    });
})();
