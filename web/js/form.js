

const stepButtons = document.querySelectorAll('.form-step');
const sections = document.querySelectorAll('.form-section');

//
// CLICK STEP -> SCROLL
//
stepButtons.forEach(button => {

    button.addEventListener('click', () => {

        const targetId = button.dataset.target;
        const targetSection = document.getElementById(targetId);

        if (targetSection) {

            window.scrollTo({
                top: targetSection.offsetTop - 100,
                behavior: 'smooth'
            });

        }

    });

});

//
// ACTIVATE STEP
//
function activateStep(targetId) {

    stepButtons.forEach(button => {

        button.classList.remove('active-step');
        button.classList.add('inactive-step');

        const number = button.querySelector('.step-number');

        number.classList.remove('bg-primary', 'text-white');
        number.classList.add('bg-surface-container-high', 'text-outline');

    });

    const activeButton = document.querySelector(
        '.form-step[data-target="' + targetId + '"]'
    );

    if (activeButton) {

        activeButton.classList.remove('inactive-step');
        activeButton.classList.add('active-step');

        const number = activeButton.querySelector('.step-number');

        number.classList.remove('bg-surface-container-high', 'text-outline');
        number.classList.add('bg-primary', 'text-white');

    }

}

//
// SCROLL DETECTION
//
const observer = new IntersectionObserver(

    (entries) => {

        entries.forEach(entry => {

            if (entry.isIntersecting) {

                activateStep(entry.target.id);

            }

        });

    },

    {
        threshold: 0.4
    }

);

sections.forEach(section => observer.observe(section));

