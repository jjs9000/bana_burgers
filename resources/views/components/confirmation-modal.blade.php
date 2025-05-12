<!-- resources/views/components/confirmation-modal.blade.php -->
<div 
    x-data="{ show: false, message: '', onConfirm: null, title: 'Confirm Action', confirmText: 'Confirm', cancelText: 'Cancel' }"
    @confirmation-modal.window="
        console.log('confirmation-modal.window event received:', event.detail);
        show = true;
        message = event.detail.message || 'Are you sure you want to proceed?';
        title = event.detail.title || 'Confirm Action';
        confirmText = event.detail.confirmText || 'Confirm';
        cancelText = event.detail.cancelText || 'Cancel';
        onConfirm = event.detail.onConfirm;
    "
    x-cloak
>
    <div 
        x-show="show" 
        class="fixed inset-0 z-[999] overflow-y-auto"
        x-transition:enter="ease-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
    >
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <div 
                class="fixed inset-0 transition-opacity bg-gray-500/75 dark:bg-gray-900/80"
                x-on:click="show = false"
            ></div>

            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <div 
                x-show="show" 
                class="inline-block px-4 pt-5 pb-4 overflow-hidden text-left align-bottom transition-all transform bg-white rounded-lg shadow-xl dark:bg-zinc-800 dark:text-white sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6"
                x-transition:enter="ease-out duration-300"
                x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave="ease-in duration-200"
                x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
            >
                <div>
                    <div class="flex items-center justify-center w-12 h-12 mx-auto bg-yellow-100 rounded-full dark:bg-yellow-900">
                        <svg class="w-6 h-6 text-yellow-600 dark:text-yellow-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
                        </svg>
                    </div>
                    <div class="mt-3 text-center sm:mt-5">
                        <h3 class="text-lg font-medium leading-6 text-gray-900 dark:text-white" x-text="title"></h3>
                        <div class="mt-2">
                            <p class="text-sm text-gray-500 dark:text-gray-300" x-text="message"></p>
                        </div>
                    </div>
                </div>
                <div class="mt-5 sm:mt-6 sm:grid sm:grid-cols-2 sm:gap-3 sm:grid-flow-row-dense">
                    <button 
                        type="button" 
                        class="inline-flex justify-center w-full px-4 py-2 text-base font-medium text-white bg-red-600 border border-transparent rounded-md shadow-sm hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:col-start-2 sm:text-sm"
                        @click="if(onConfirm) { console.log('Confirm button clicked, executing onConfirm function'); onConfirm(); } show = false"
                        x-text="confirmText"
                    >
                    </button>
                    <button 
                        type="button" 
                        class="inline-flex justify-center w-full px-4 py-2 mt-3 text-base font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm dark:bg-zinc-700 dark:text-white dark:border-zinc-600 hover:bg-gray-50 dark:hover:bg-zinc-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-zinc-500 sm:mt-0 sm:col-start-1 sm:text-sm"
                        @click="show = false"
                        x-text="cancelText"
                    >
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('alpine:init', function() {
        window.confirmAction = function(options = {}) {
            console.log('confirmAction called with options:', options);
            const message = options.message || 'Are you sure you want to proceed?';
            const title = options.title || 'Confirm Action';
            const confirmText = options.confirmText || 'Confirm';
            const cancelText = options.cancelText || 'Cancel';
            const onConfirm = options.onConfirm || function() {};
            
            window.dispatchEvent(new CustomEvent('confirmation-modal', { 
                detail: { 
                    message: message,
                    title: title,
                    confirmText: confirmText,
                    cancelText: cancelText,
                    onConfirm: onConfirm
                }
            }));
            console.log('confirmation-modal event dispatched');
        }
    });
</script> 