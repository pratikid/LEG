export default {
    init() {
        this.tutorials = {
            timeline: {
                title: 'Timeline Overview',
                steps: [
                    {
                        target: '[data-tutorial="timeline-header"]',
                        content: 'Welcome to the Timeline! Here you can view and manage all your family events.',
                        placement: 'bottom'
                    },
                    {
                        target: '[data-tutorial="add-event"]',
                        content: 'Click here to add a new event to your timeline.',
                        placement: 'left'
                    },
                    {
                        target: '[data-tutorial="generate-report"]',
                        content: 'Generate PDF reports of your timeline events.',
                        placement: 'left'
                    },
                    {
                        target: '[data-tutorial="event-list"]',
                        content: 'Your events are displayed chronologically here. Click on any event to view more details.',
                        placement: 'top'
                    }
                ]
            },
            event: {
                title: 'Event Details',
                steps: [
                    {
                        target: '[data-tutorial="event-details"]',
                        content: 'View all the details about this event.',
                        placement: 'bottom'
                    },
                    {
                        target: '[data-tutorial="edit-event"]',
                        content: 'Edit this event if you need to make changes.',
                        placement: 'left'
                    },
                    {
                        target: '[data-tutorial="event-report"]',
                        content: 'Generate a detailed report for this specific event.',
                        placement: 'left'
                    }
                ]
            }
        };

        this.currentTutorial = null;
        this.currentStep = 0;
        this.completedTutorials = window.userPreferences?.completed_tutorials || [];
    },

    startTutorial(tutorialName) {
        if (this.completedTutorials.includes(tutorialName)) {
            return;
        }

        this.currentTutorial = tutorialName;
        this.currentStep = 0;
        this.showStep();
    },

    nextStep() {
        if (this.currentStep < this.tutorials[this.currentTutorial].steps.length - 1) {
            this.currentStep++;
            this.showStep();
        } else {
            this.completeTutorial();
        }
    },

    previousStep() {
        if (this.currentStep > 0) {
            this.currentStep--;
            this.showStep();
        }
    },

    showStep() {
        const step = this.tutorials[this.currentTutorial].steps[this.currentStep];
        const target = document.querySelector(step.target);
        
        if (target) {
            // Remove any existing tooltips
            document.querySelectorAll('.tutorial-tooltip').forEach(el => el.remove());
            
            // Create and position the tooltip
            const tooltip = document.createElement('div');
            tooltip.className = 'tutorial-tooltip';
            tooltip.innerHTML = `
                <div class="bg-white p-4 rounded-lg shadow-lg max-w-sm">
                    <h3 class="font-bold mb-2">${this.tutorials[this.currentTutorial].title}</h3>
                    <p class="mb-4">${step.content}</p>
                    <div class="flex justify-between">
                        <button class="text-gray-600 hover:text-gray-800" onclick="tutorial.previousStep()">Previous</button>
                        <button class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600" onclick="tutorial.nextStep()">Next</button>
                    </div>
                </div>
            `;
            
            document.body.appendChild(tooltip);
            
            // Position the tooltip
            const targetRect = target.getBoundingClientRect();
            const tooltipRect = tooltip.getBoundingClientRect();
            
            switch (step.placement) {
                case 'top':
                    tooltip.style.top = `${targetRect.top - tooltipRect.height - 10}px`;
                    tooltip.style.left = `${targetRect.left + (targetRect.width - tooltipRect.width) / 2}px`;
                    break;
                case 'bottom':
                    tooltip.style.top = `${targetRect.bottom + 10}px`;
                    tooltip.style.left = `${targetRect.left + (targetRect.width - tooltipRect.width) / 2}px`;
                    break;
                case 'left':
                    tooltip.style.top = `${targetRect.top + (targetRect.height - tooltipRect.height) / 2}px`;
                    tooltip.style.left = `${targetRect.left - tooltipRect.width - 10}px`;
                    break;
                case 'right':
                    tooltip.style.top = `${targetRect.top + (targetRect.height - tooltipRect.height) / 2}px`;
                    tooltip.style.left = `${targetRect.right + 10}px`;
                    break;
            }
        }
    },

    completeTutorial() {
        if (this.currentTutorial) {
            this.completedTutorials.push(this.currentTutorial);
            
            // Send completion to server
            fetch('/tutorials/mark-completed', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({
                    tutorial: this.currentTutorial
                })
            });
            
            // Remove tooltip
            document.querySelectorAll('.tutorial-tooltip').forEach(el => el.remove());
            
            this.currentTutorial = null;
            this.currentStep = 0;
        }
    },

    resetTutorials() {
        fetch('/tutorials/reset', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        }).then(() => {
            this.completedTutorials = [];
        });
    }
}; 