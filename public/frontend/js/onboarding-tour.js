document.addEventListener('DOMContentLoaded', function () {
    // Shepherd Onboarding Tour logic
    const startTourBtn = document.getElementById('startOnboardingBtn');
    
    function initOnboardingTour() {
        if (typeof Shepherd === 'undefined') {
            console.log('Shepherd tour library loading...');
            return;
        }

        const tour = new Shepherd.Tour({
            useModalOverlay: true,
            defaultStepOptions: {
                classes: 'shadow-lg rounded-4 p-3 border-0',
                scrollTo: { behavior: 'smooth', block: 'center' },
                cancelIcon: { enabled: true }
            }
        });

        tour.addStep({
            id: 'welcome',
            title: 'Welcome to Noubtigo! 👋',
            text: 'Let us take a quick 1-minute tour to help you get familiar with key features.',
            buttons: [
                { text: 'Skip', action: tour.cancel, classes: 'btn btn-sm btn-light rounded-pill' },
                { text: 'Next', action: tour.next, classes: 'btn btn-sm btn-primary text-white rounded-pill ms-2' }
            ]
        });

        if (document.querySelector('.search-bar')) {
            tour.addStep({
                id: 'global-search',
                attachTo: { element: '#searchBar', on: 'bottom' },
                title: 'Global Search 🔍',
                text: 'Quickly search customers, queue tickets, pages, or commands anywhere using Ctrl+K.',
                buttons: [
                    { text: 'Back', action: tour.back, classes: 'btn btn-sm btn-light rounded-pill' },
                    { text: 'Next', action: tour.next, classes: 'btn btn-sm btn-primary text-white rounded-pill ms-2' }
                ]
            });
        }

        if (document.querySelector('a[href*="queue"]')) {
            tour.addStep({
                id: 'queue-menu',
                attachTo: { element: 'a[href*="queue"]', on: 'right' },
                title: 'Queue Operations 🎫',
                text: 'Issue new tickets, call waiting customers, put on hold, and manage real-time queues.',
                buttons: [
                    { text: 'Back', action: tour.back, classes: 'btn btn-sm btn-light rounded-pill' },
                    { text: 'Next', action: tour.next, classes: 'btn btn-sm btn-primary text-white rounded-pill ms-2' }
                ]
            });
        }

        tour.addStep({
            id: 'help-button',
            attachTo: { element: '#helpCenterBtn', on: 'bottom' },
            title: 'Contextual Help Center ❓',
            text: 'Need help on any page? Click this Help button anytime to open instant page guides without leaving your workflow!',
            buttons: [
                { text: 'Finish', action: tour.complete, classes: 'btn btn-sm btn-success text-white rounded-pill' }
            ]
        });

        tour.start();
    }

    if (startTourBtn) {
        startTourBtn.addEventListener('click', function (e) {
            e.preventDefault();
            initOnboardingTour();
        });
    }
});
