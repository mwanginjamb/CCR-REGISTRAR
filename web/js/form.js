document.addEventListener('DOMContentLoaded', async function () {

    const stepButtons = document.querySelectorAll('.form-step');
    const sections = document.querySelectorAll('.form-section');

    if (!stepButtons.length || !sections.length) {
        console.warn('Stepper or sections not found.');
        return;
    }

    function activateStep(targetId) {
        stepButtons.forEach(button => {
            button.classList.remove('active-step');
            button.classList.add('inactive-step');
            const number = button.querySelector('.step-number');
            if (number) {
                number.classList.remove('bg-primary', 'text-white');
                number.classList.add('bg-surface-container-high', 'text-outline');
            }
        });
        const activeButtons = document.querySelectorAll(`.form-step[data-target="${targetId}"]`);
        activeButtons.forEach(button => {
            button.classList.remove('inactive-step');
            button.classList.add('active-step');
            const number = button.querySelector('.step-number');
            if (number) {
                number.classList.remove('bg-surface-container-high', 'text-outline');
                number.classList.add('bg-primary', 'text-white');
            }
        });
    }

    stepButtons.forEach(button => {
        button.addEventListener('click', function () {
            const targetId = this.dataset.target;
            const targetSection = document.getElementById(targetId);
            if (!targetSection) return;
            window.scrollTo({
                top: targetSection.offsetTop - 120,
                behavior: 'smooth'
            });
            activateStep(targetId);
        });
    });

    const observer = new IntersectionObserver(
        (entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    activateStep(entry.target.id);
                }
            });
        },
        {
            root: null,
            threshold: 0.15,
            rootMargin: '-120px 0px -55% 0px'
        }
    );
    sections.forEach(section => observer.observe(section));
    activateStep(sections[0].id);

    // ------------------------------------------------------------------
    // TREATMENT ROWS
    // ------------------------------------------------------------------
    const wrapper = document.getElementById('treatment-wrapper');
    const template = document.getElementById('treatment-template').innerHTML;

    // Helper: get the highest index currently used in the wrapper
    function getMaxTreatmentIndex() {
        let max = -1;
        wrapper.querySelectorAll('.treatment-item').forEach(item => {
            const idxAttr = item.getAttribute('data-index');
            if (idxAttr !== null) {
                max = Math.max(max, parseInt(idxAttr, 10));
            } else {
                // fallback: try to extract from input names
                const input = item.querySelector('input[name*="[treatment]"]');
                if (input && input.name) {
                    const match = input.name.match(/Treatment\[(\d+)\]/);
                    if (match) max = Math.max(max, parseInt(match[1], 10));
                }
            }
        });
        return max;
    }

    // Get the next available index
    function getNextTreatmentIndex() {
        return getMaxTreatmentIndex() + 1;
    }

    function addTreatmentRow() {
        const newIndex = getNextTreatmentIndex();
        const html = template.replace(/__index__/g, newIndex);
        const div = document.createElement('div');
        div.innerHTML = html;
        const newRow = div.firstElementChild;
        // Enable all inputs inside the new row
        newRow.querySelectorAll('input, select, textarea').forEach(el => el.removeAttribute('disabled'));
        // Store the index as a data attribute for easier future indexing
        newRow.setAttribute('data-index', newIndex);
        wrapper.appendChild(newRow);
        updateRemoveVisibility();
    }

    function updateRemoveVisibility() {
        const rows = wrapper.querySelectorAll('.treatment-item');
        const alone = rows.length === 1;
        rows.forEach(row => {
            const btn = row.querySelector('.remove-treatment');
            if (btn) btn.style.visibility = alone ? 'hidden' : 'visible';
        });
    }

    document.getElementById('add-treatment').addEventListener('click', addTreatmentRow);

    wrapper.addEventListener('click', function (e) {
        if (e.target.classList.contains('remove-treatment')) {
            if (wrapper.querySelectorAll('.treatment-item').length > 1) {
                e.target.closest('.treatment-item').remove();
                updateRemoveVisibility();
            }
        }
    });

    // Only seed a new row if there are NO existing rows (e.g., on create page)
    if (!wrapper.querySelector('.treatment-item')) {
        addTreatmentRow();
    } else {
        // Existing rows are already present (update scenario) – just update remove visibility
        updateRemoveVisibility();
    }

    // ------------------------------------------------------------------
    // SOURCE ROWS
    // ------------------------------------------------------------------
    const sourceWrapper = document.getElementById('source-wrapper');
    const sourceTemplate = document.getElementById('source-template').innerHTML;

    function getMaxSourceIndex() {
        let max = -1;
        sourceWrapper.querySelectorAll('.source-item').forEach(item => {
            const idxAttr = item.getAttribute('data-index');
            if (idxAttr !== null) {
                max = Math.max(max, parseInt(idxAttr, 10));
            } else {
                const input = item.querySelector('input[name*="[source_type]"]');
                if (input && input.name) {
                    const match = input.name.match(/Sources\[(\d+)\]/);
                    if (match) max = Math.max(max, parseInt(match[1], 10));
                }
            }
        });
        return max;
    }

    function getNextSourceIndex() {
        return getMaxSourceIndex() + 1;
    }

    function addSourceRow() {
        const newIndex = getNextSourceIndex();
        const html = sourceTemplate.replace(/__index__/g, newIndex);
        const div = document.createElement('div');
        div.innerHTML = html;
        const newRow = div.firstElementChild;
        // Enable all inputs inside the new row
        newRow.querySelectorAll('input, select, textarea').forEach(el => el.removeAttribute('disabled'));
        newRow.setAttribute('data-index', newIndex);
        sourceWrapper.appendChild(newRow);
        updateSourceRemoveVisibility();
    }

    function updateSourceRemoveVisibility() {
        const rows = sourceWrapper.querySelectorAll('.source-item');
        const alone = rows.length === 1;
        rows.forEach(row => {
            const btn = row.querySelector('.remove-source');
            if (btn) btn.style.visibility = alone ? 'hidden' : 'visible';
        });
    }

    document.getElementById('add-source').addEventListener('click', addSourceRow);

    sourceWrapper.addEventListener('click', function (e) {
        if (e.target.classList.contains('remove-source')) {
            if (sourceWrapper.querySelectorAll('.source-item').length > 1) {
                e.target.closest('.source-item').remove();
                updateSourceRemoveVisibility();
            }
        }
    });

    if (!sourceWrapper.querySelector('.source-item')) {
        addSourceRow();
    } else {
        updateSourceRemoveVisibility();
    }

    // Geolocation
    const _coords = await GeoTag.capture();
    GeoTag.fillDisplay(_coords);
});