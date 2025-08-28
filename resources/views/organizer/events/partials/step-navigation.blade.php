{{-- Step Navigation Component --}}
<div class="card p-6 mb-6">
    {{-- Progress Indicator --}}
    <div class="mb-6">
        <div class="h-1 bg-gray-200 rounded-full overflow-hidden mb-4">
            <div class="h-full bg-gradient-to-r from-primary-500 to-primary-700 rounded-full transition-all duration-300" style="width: {{ ($currentStep / 4) * 100 }}%"></div>
        </div>
        <div class="flex justify-between relative">
            @for($i = 1; $i <= 4; $i++)
                <div class="w-8 h-8 rounded-full flex items-center justify-center font-semibold text-sm transition-all duration-300 {{ $i <= $currentStep ? 'bg-primary-500 text-white' : 'bg-gray-200 text-gray-500' }} {{ $i == $currentStep ? 'ring-4 ring-primary-200' : '' }}">
                    @if($i < $currentStep)
                        <i class="fas fa-check text-xs"></i>
                    @else
                        {{ $i }}
                    @endif
                </div>
            @endfor
        </div>
    </div>

    {{-- Step Labels --}}
    <div class="grid grid-cols-4 gap-4 mb-6">
        <div class="text-center {{ $currentStep == 1 ? 'opacity-100' : 'opacity-60' }} transition-opacity duration-300">
            <h4 class="text-base font-semibold text-primary mb-1">Event Details</h4>
            <p class="text-xs text-secondary">Basic information</p>
        </div>
        <div class="text-center {{ $currentStep == 2 ? 'opacity-100' : 'opacity-60' }} transition-opacity duration-300">
            <h4 class="text-base font-semibold text-primary mb-1">Event Settings</h4>
            <p class="text-xs text-secondary">QR, RSVP & Guest Lists</p>
        </div>
        <div class="text-center {{ $currentStep == 3 ? 'opacity-100' : 'opacity-60' }} transition-opacity duration-300">
            <h4 class="text-base font-semibold text-primary mb-1">Message Customization</h4>
            <p class="text-xs text-secondary">AI-powered messaging</p>
        </div>
        <div class="text-center {{ $currentStep == 4 ? 'opacity-100' : 'opacity-60' }} transition-opacity duration-300">
            <h4 class="text-base font-semibold text-primary mb-1">Send Invitations</h4>
            <p class="text-xs text-secondary">Schedule or send now</p>
        </div>
    </div>

    {{-- Navigation Buttons --}}
    <div class="flex justify-between items-center">
        @if($currentStep > 1)
            @php
                $prevStep = $currentStep - 1;
                $prevRoute = 'organizer.events.create.step' . $prevStep;
            @endphp
            <button type="button" onclick="goToPreviousStep({{ $currentStep }})" class="btn-secondary">
                <i class="fas fa-arrow-left"></i> Previous
            </button>
        @else
            <div></div>
        @endif

        @if($currentStep < 4)
            <button type="submit" form="event-form-{{ $currentStep }}" class="btn-primary">
                Next <i class="fas fa-arrow-right"></i>
            </button>
        @else
            <button type="submit" form="event-form-{{ $currentStep }}" class="btn-success">
                <i class="fas fa-paper-plane"></i> 
                @if(request()->has('mode') && request()->get('mode') === 'update')
                    Update & Send
                @else
                    Complete & Send
                @endif
            </button>
        @endif
    </div>
</div>

<style>
@media (max-width: 768px) {
    .grid-cols-4 {
        grid-template-columns: repeat(2, 1fr);
    }
    
    .text-base {
        font-size: 14px;
    }
    
    .text-xs {
        font-size: 11px;
    }
    
    .flex.justify-between {
        flex-direction: column;
        gap: 1rem;
    }
    
    .btn-primary, .btn-secondary, .btn-success {
        width: 100%;
        justify-content: center;
    }
}
</style>

<script>
function goToPreviousStep(currentStep) {
    // First, trigger auto-save to preserve current form data
    const form = document.getElementById('event-form-' + currentStep);
    if (form) {
        // Create a temporary form data object
        const formData = new FormData(form);
        
        // Convert FormData to proper object with arrays
        const formDataObj = {};
        for (let [key, value] of formData.entries()) {
            if (key.endsWith('[]')) {
                // Handle array fields
                const arrayKey = key.slice(0, -2);
                if (!formDataObj[arrayKey]) {
                    formDataObj[arrayKey] = [];
                }
                formDataObj[arrayKey].push(value);
            } else {
                // Handle regular fields
                formDataObj[key] = value;
            }
        }
        
        // Show saving indicator
        const indicator = document.getElementById('auto-save-indicator') || createAutoSaveIndicator();
        indicator.textContent = 'Saving and going back...';
        indicator.style.backgroundColor = '#3b82f6';
        indicator.style.opacity = '1';
        
        // Save current form data
        fetch('{{ route("organizer.events.create.auto-save") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(formDataObj)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Navigate to previous step
                const prevStep = currentStep - 1;
                const baseUrl = '{{ route("organizer.events.create.step1") }}';
                const targetUrl = baseUrl.replace('step1', 'step' + prevStep);
                
                // Preserve mode parameter if it exists
                const urlParams = new URLSearchParams(window.location.search);
                const mode = urlParams.get('mode');
                const finalUrl = mode ? `${targetUrl}?mode=${mode}` : targetUrl;
                
                console.log('Navigating to previous step:', finalUrl);
                window.location.href = finalUrl;
            } else {
                console.error('Failed to save before navigation:', data);
                indicator.textContent = 'Save failed - please try again';
                indicator.style.backgroundColor = '#ef4444';
            }
        })
        .catch(error => {
            console.error('Error saving before navigation:', error);
            indicator.textContent = 'Error - please try again';
            indicator.style.backgroundColor = '#ef4444';
        });
    } else {
        // If no form found, just navigate
        const prevStep = currentStep - 1;
        const baseUrl = '{{ route("organizer.events.create.step1") }}';
        const targetUrl = baseUrl.replace('step1', 'step' + prevStep);
        
        // Preserve mode parameter if it exists
        const urlParams = new URLSearchParams(window.location.search);
        const mode = urlParams.get('mode');
        const finalUrl = mode ? `${targetUrl}?mode=${mode}` : targetUrl;
        
        console.log('No form found, navigating directly to:', finalUrl);
        window.location.href = finalUrl;
    }
}

function createAutoSaveIndicator() {
    const indicator = document.createElement('div');
    indicator.id = 'auto-save-indicator';
    indicator.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        padding: 8px 16px;
        border-radius: 4px;
        font-size: 12px;
        font-weight: 500;
        z-index: 1000;
        transition: opacity 0.3s ease;
        opacity: 0;
        color: white;
    `;
    document.body.appendChild(indicator);
    return indicator;
}
</script>