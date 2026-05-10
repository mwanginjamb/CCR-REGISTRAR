document.addEventListener('DOMContentLoaded', function () {

    const stepButtons = document.querySelectorAll('.form-step');
    const sections = document.querySelectorAll('.form-section');

    if (!stepButtons.length || !sections.length) {
        console.warn('Stepper or sections not found.');
        return;
    }

    /**
     * ACTIVATE STEP
     */
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

        const activeButtons = document.querySelectorAll(
            `.form-step[data-target="${targetId}"]`
        );

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


    /**
     * CLICK STEP -> SCROLL
     */
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


    /**
     * SCROLL OBSERVER
     */
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


    /**
     * INITIAL ACTIVE STEP
     */
    activateStep(sections[0].id);


    /**
     * ADD TREATMENT
     */

    const wrapper = document.getElementById('treatment-wrapper');
    const template = document.getElementById('treatment-template').innerHTML;
    let treatmentIndex = wrapper.querySelectorAll('.treatment-item').length;

    function addTreatmentRow() {
        const html = template.replace(/__index__/g, treatmentIndex);
        const div = document.createElement('div');
        div.innerHTML = html;
        wrapper.appendChild(div.firstElementChild);
        treatmentIndex++;
        updateRemoveVisibility();
    }

    // conditional update of remove button visibility

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

    // Seed first row if wrapper is empty (JS-owned first row)
    if (!wrapper.querySelector('.treatment-item')) {
        addTreatmentRow();
    }



});