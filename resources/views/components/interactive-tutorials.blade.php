@props(['tutorials', 'user'])

<div class="bg-white shadow overflow-hidden sm:rounded-lg">
    <div class="px-4 py-5 sm:px-6">
        <div class="flex items-center justify-between">
            <div>
                <h3 class="text-lg leading-6 font-medium text-gray-900">Interactive Tutorials</h3>
                <p class="mt-1 max-w-2xl text-sm text-gray-500">Learn how to use LEG effectively with step-by-step guides.</p>
            </div>
        </div>
    </div>

    <!-- Tutorial Categories -->
    <div class="border-t border-gray-200">
        <div class="px-4 py-5 sm:px-6">
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                @foreach($tutorials as $tutorial)
                <div class="bg-white overflow-hidden shadow rounded-lg">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="h-12 w-12 rounded-full bg-amber-100 flex items-center justify-center">
                                    <svg class="h-6 w-6 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $tutorial->icon }}"></path>
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-4">
                                <h4 class="text-lg font-medium text-gray-900">{{ $tutorial->title }}</h4>
                                <p class="text-sm text-gray-500">{{ $tutorial->duration }} minutes</p>
                            </div>
                        </div>
                        <div class="mt-4">
                            <p class="text-sm text-gray-500">{{ $tutorial->description }}</p>
                        </div>
                        <div class="mt-4">
                            <div class="flex items-center">
                                <div class="flex-1">
                                    <div class="h-2 bg-gray-200 rounded-full">
                                        <div class="h-2 bg-amber-600 rounded-full" style="width: {{ $tutorial->progress }}%"></div>
                                    </div>
                                </div>
                                <span class="ml-2 text-sm text-gray-500">{{ $tutorial->progress }}%</span>
                            </div>
                        </div>
                        <div class="mt-4">
                            <button type="button" class="w-full inline-flex justify-center items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-amber-600 hover:bg-amber-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-amber-500" onclick="startTutorial('{{ $tutorial->id }}')">
                                {{ $tutorial->progress > 0 ? 'Continue Tutorial' : 'Start Tutorial' }}
                            </button>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Tutorial Modal -->
    <div id="tutorialModal" class="hidden fixed z-10 inset-0 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div class="inline-block align-bottom bg-white rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full sm:p-6">
                <div>
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">Tutorial</h3>
                        <button type="button" class="text-gray-400 hover:text-gray-500" onclick="closeTutorial()">
                            <span class="sr-only">Close</span>
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                    <div class="mt-4">
                        <div id="tutorialContent" class="prose max-w-none">
                            <!-- Tutorial content will be loaded here -->
                        </div>
                    </div>
                    <div class="mt-5 sm:mt-6 sm:grid sm:grid-cols-2 sm:gap-3 sm:grid-flow-row-dense">
                        <button type="button" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-amber-600 text-base font-medium text-white hover:bg-amber-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-amber-500 sm:col-start-2 sm:text-sm" onclick="nextStep()">
                            Next Step
                        </button>
                        <button type="button" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-amber-500 sm:mt-0 sm:col-start-1 sm:text-sm" onclick="previousStep()">
                            Previous Step
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
let currentTutorial = null;
let currentStep = 0;
let tutorialSteps = [];

document.addEventListener('DOMContentLoaded', function() {
    // Initialize tooltips
    const tooltips = document.querySelectorAll('[data-tooltip]');
    tooltips.forEach(tooltip => {
        new Tooltip(tooltip, {
            placement: 'top',
            trigger: 'hover'
        });
    });
});

function startTutorial(tutorialId) {
    currentTutorial = tutorialId;
    currentStep = 0;
    
    // Fetch tutorial content
    fetch(`/tutorials/${tutorialId}`)
        .then(response => response.json())
        .then(data => {
            tutorialSteps = data.steps;
            showStep(0);
            document.getElementById('tutorialModal').classList.remove('hidden');
        });
}

function showStep(stepIndex) {
    const step = tutorialSteps[stepIndex];
    const content = document.getElementById('tutorialContent');
    
    // Update content
    content.innerHTML = `
        <div class="mb-4">
            <h4 class="text-lg font-medium text-gray-900">${step.title}</h4>
            <p class="mt-2 text-sm text-gray-500">${step.description}</p>
        </div>
        ${step.content}
    `;
    
    // Update buttons
    const nextButton = document.querySelector('button:contains("Next Step")');
    const prevButton = document.querySelector('button:contains("Previous Step")');
    
    nextButton.disabled = stepIndex === tutorialSteps.length - 1;
    prevButton.disabled = stepIndex === 0;
    
    // Update progress
    const progress = Math.round((stepIndex + 1) / tutorialSteps.length * 100);
    updateProgress(progress);
}

function nextStep() {
    if (currentStep < tutorialSteps.length - 1) {
        currentStep++;
        showStep(currentStep);
    }
}

function previousStep() {
    if (currentStep > 0) {
        currentStep--;
        showStep(currentStep);
    }
}

function closeTutorial() {
    document.getElementById('tutorialModal').classList.add('hidden');
    currentTutorial = null;
    currentStep = 0;
    tutorialSteps = [];
}

function updateProgress(progress) {
    // Update progress in the database
    fetch(`/tutorials/${currentTutorial}/progress`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({ progress })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Update progress bar in the UI
            const progressBar = document.querySelector(`[data-tutorial-id="${currentTutorial}"] .bg-amber-600`);
            progressBar.style.width = `${progress}%`;
            
            const progressText = document.querySelector(`[data-tutorial-id="${currentTutorial}"] .text-gray-500`);
            progressText.textContent = `${progress}%`;
        }
    });
}

// Tooltip class for interactive elements
class Tooltip {
    constructor(element, options) {
        this.element = element;
        this.options = options;
        this.tooltip = null;
        
        this.init();
    }
    
    init() {
        this.element.addEventListener('mouseenter', () => this.show());
        this.element.addEventListener('mouseleave', () => this.hide());
    }
    
    show() {
        const text = this.element.getAttribute('data-tooltip');
        this.tooltip = document.createElement('div');
        this.tooltip.className = 'absolute z-50 px-2 py-1 text-sm text-white bg-gray-900 rounded shadow-lg';
        this.tooltip.textContent = text;
        
        const rect = this.element.getBoundingClientRect();
        this.tooltip.style.top = `${rect.top - 30}px`;
        this.tooltip.style.left = `${rect.left + (rect.width / 2)}px`;
        
        document.body.appendChild(this.tooltip);
    }
    
    hide() {
        if (this.tooltip) {
            this.tooltip.remove();
            this.tooltip = null;
        }
    }
}
</script>
@endpush 