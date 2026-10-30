/**
 * sourceNo.js — "Source No" suggestions on the patient form's Sources section.
 *
 * Each source type has a <datalist id="source-no-options-{type}"> rendered from the
 * source_reference setup table. Choosing a source type points that row's Source No
 * input at the matching list; picking or typing a known number shows the source's
 * name under the input. Free text stays allowed for sources not yet in the setup.
 *
 * Uses event delegation, so rows added later via "+ Add Source" work too.
 */
(function () {
    const wrapper = document.getElementById('source-wrapper');

    if (!wrapper) {
        return;
    }

    function rowParts(el) {
        const row = el.closest('.source-item');
        if (!row) return {};
        return {
            type: row.querySelector('select[name$="[source_type]"]'),
            number: row.querySelector('.source-no-input'),
        };
    }

    // The name shown under the Source No input, created on first use
    function nameHint(input) {
        let hint = input.parentElement.querySelector('.source-no-name');
        if (!hint) {
            hint = document.createElement('div');
            hint.className = 'source-no-name text-xs text-on-surface-variant mt-1';
            input.insertAdjacentElement('afterend', hint);
        }
        return hint;
    }

    function showName(input) {
        const list = input.list;
        const value = input.value.trim();
        let name = '';

        if (list && value) {
            const match = Array.from(list.options).find(function (o) { return o.value === value; });
            name = match ? match.textContent : '';
        }

        nameHint(input).textContent = name;
    }

    function applyType(typeSelect, numberInput) {
        if (!typeSelect || !numberInput) return;

        const listId = 'source-no-options-' + typeSelect.value;

        if (typeSelect.value && document.getElementById(listId)) {
            numberInput.setAttribute('list', listId);
            numberInput.placeholder = 'Type number or name';
        } else {
            numberInput.removeAttribute('list');
            numberInput.placeholder = 'Source No';
        }

        showName(numberInput);
    }

    wrapper.addEventListener('change', function (e) {
        const parts = rowParts(e.target);
        if (e.target === parts.type) applyType(parts.type, parts.number);
    });

    wrapper.addEventListener('input', function (e) {
        if (e.target.classList.contains('source-no-input')) showName(e.target);
    });

    // Rows rendered by the server (update form, or create form after a validation error)
    wrapper.querySelectorAll('.source-item').forEach(function (row) {
        const parts = rowParts(row);
        applyType(parts.type, parts.number);
    });
})();
