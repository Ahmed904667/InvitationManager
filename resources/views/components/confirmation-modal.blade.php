@props([
    'id' => 'confirmationModal',
    'title' => 'Confirm Action',
    'message' => 'Are you sure you want to proceed?',
    'itemName' => '',
    'warningMessage' => '',
    'confirmText' => 'Confirm',
    'cancelText' => 'Cancel',
    'confirmClass' => 'modal-btn-primary',
    'danger' => false,
    'warning' => false,
    'info' => false,
    'success' => false,
    'icon' => null,
    'showWarningBox' => false,
    'warningBoxText' => 'This action will permanently delete the selected item and cannot be undone.'
])

<div id="{{ $id }}" class="modal hidden">
    <div class="modal-content">
        <div class="modal-header">
            <h3 class="modal-title" style="color: var(--text-primary)">{{ $title }}</h3>
            <button type="button" class="modal-close" onclick="hideConfirmationModal('{{ $id }}')"></button>
        </div>
        <div class="modal-body">
            <div class="text-center">
                @if($icon)
                    <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full mb-4" style="background: {{ $danger ? 'var(--danger-100)' : ($warning ? 'var(--warning-100)' : ($info ? 'var(--info-100)' : ($success ? 'var(--success-100)' : 'var(--gray-100)'))) }}">
                        {!! $icon !!}
                    </div>
                @elseif($danger)
                    <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full mb-4" style="background: var(--danger-100);">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color: var(--danger-600);">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                        </svg>
                    </div>
                @elseif($warning)
                    <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full mb-4" style="background: var(--warning-100);">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color: var(--warning-600);">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                        </svg>
                    </div>
                @elseif($info)
                    <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full mb-4" style="background: var(--info-100);">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color: var(--info-600);">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                @elseif($success)
                    <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full mb-4" style="background: var(--success-100);">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color: var(--success-600);">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                @else
                    <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full mb-4" style="background: var(--gray-100);">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color: var(--gray-600);">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                @endif
                
                <h3 class="text-lg font-medium mb-2" style="color: var(--text-primary);">Are you sure?</h3>
                <p class="text-sm mb-4" style="color: var(--text-secondary);">
                    {{ $message }}
                    @if($itemName)
                        <span class="font-medium">"{{ $itemName }}"</span>
                    @endif
                    @if($warningMessage)
                        <br>{{ $warningMessage }}
                    @endif
                </p>
                
                @if($showWarningBox)
                    <div class="rounded-md p-4" style="background: {{ $danger ? 'var(--danger-50)' : ($warning ? 'var(--warning-50)' : ($info ? 'var(--info-50)' : ($success ? 'var(--success-50)' : 'var(--gray-50)'))) }}; border: 1px solid {{ $danger ? 'var(--danger-200)' : ($warning ? 'var(--warning-200)' : ($info ? 'var(--info-200)' : ($success ? 'var(--success-200)' : 'var(--gray-200)'))) }};">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                @if($danger)
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color: var(--danger-400);">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                                    </svg>
                                @elseif($warning)
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color: var(--warning-400);">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                                    </svg>
                                @elseif($info)
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color: var(--info-400);">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                @elseif($success)
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color: var(--success-400);">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                @else
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color: var(--gray-400);">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                @endif
                            </div>
                            <div class="ml-3">
                                <h3 class="text-sm font-medium" style="color: {{ $danger ? 'var(--danger-800)' : ($warning ? 'var(--warning-800)' : ($info ? 'var(--info-800)' : ($success ? 'var(--success-800)' : 'var(--gray-800)'))) }};">
                                    {{ $danger ? 'Warning' : ($warning ? 'Warning' : ($info ? 'Information' : ($success ? 'Success' : 'Note'))) }}
                                </h3>
                                <div class="mt-2 text-sm" style="color: {{ $danger ? 'var(--danger-700)' : ($warning ? 'var(--warning-700)' : ($info ? 'var(--info-700)' : ($success ? 'var(--success-700)' : 'var(--gray-700)'))) }};">
                                    <p>{{ $warningBoxText }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="modal-btn modal-btn-secondary" onclick="hideConfirmationModal('{{ $id }}')">
                {{ $cancelText }}
            </button>
            <button type="button" class="modal-btn {{ $confirmClass }}" onclick="executeConfirmedAction('{{ $id }}')">
                {{ $confirmText }}
            </button>
        </div>
    </div>
</div>

<script>
// Global confirmation modal state
window.confirmationModalState = {
    currentModalId: null,
    onConfirm: null,
    onCancel: null
};

function showConfirmationModal(modalId, onConfirm, onCancel = null) {
    window.confirmationModalState.currentModalId = modalId;
    window.confirmationModalState.onConfirm = onConfirm;
    window.confirmationModalState.onCancel = onCancel;
    
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.remove('hidden');
        modal.classList.add('show');
    }
}

function hideConfirmationModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.remove('show');
        modal.classList.add('hidden');
    }
    
    // Call cancel callback if provided
    if (window.confirmationModalState.onCancel && window.confirmationModalState.currentModalId === modalId) {
        window.confirmationModalState.onCancel();
    }
    
    // Reset state
    window.confirmationModalState.currentModalId = null;
    window.confirmationModalState.onConfirm = null;
    window.confirmationModalState.onCancel = null;
}

function executeConfirmedAction(modalId) {
    if (window.confirmationModalState.onConfirm && window.confirmationModalState.currentModalId === modalId) {
        window.confirmationModalState.onConfirm();
    }
    hideConfirmationModal(modalId);
}

// Helper function for delete confirmations
function confirmDelete(itemId, itemName, deleteFunction, modalId = 'confirmationModal') {
    const onConfirm = () => {
        deleteFunction(itemId);
    };
    
    showConfirmationModal(modalId, onConfirm);
}

// Helper function for any confirmation with custom data
function confirmAction(actionFunction, modalId = 'confirmationModal', data = null) {
    const onConfirm = () => {
        actionFunction(data);
    };
    
    showConfirmationModal(modalId, onConfirm);
}

// Helper function for form submissions
function confirmFormSubmission(formId, modalId = 'confirmationModal') {
    const onConfirm = () => {
        document.getElementById(formId).submit();
    };
    
    showConfirmationModal(modalId, onConfirm);
}

// Helper function for navigation confirmations
function confirmNavigation(url, modalId = 'confirmationModal') {
    const onConfirm = () => {
        window.location.href = url;
    };
    
    showConfirmationModal(modalId, onConfirm);
}
</script> 