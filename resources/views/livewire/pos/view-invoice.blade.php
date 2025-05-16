<div>
    @if($showModal)
    <div 
        x-data="{
            show: @entangle('showModal').live,
            isLoading: true,
            init() {
                $watch('show', value => {
                    if (!value) {
                        setTimeout(() => @this.closeModal(), 300);
                    } else {
                        this.isLoading = true;
                    }
                });
            }
        }"
        x-show="show"
        x-cloak
        class="fixed inset-0 z-[190]"
        x-transition:enter="ease-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
    >
        <div class="fixed inset-0 transition-opacity bg-gray-500/75 dark:bg-gray-900/80" @click="show = false"></div>
        
        <div class="flex items-center justify-center min-h-screen p-4">
            <div 
                x-show="show"
                class="bg-white dark:bg-zinc-800 rounded-lg shadow-xl w-full max-w-6xl h-[90vh] flex flex-col relative"
                x-transition:enter="ease-out duration-300"
                x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave="ease-in duration-200"
                x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
            >
                <!-- Modal Header -->
                <div class="flex items-center justify-between p-4 border-b border-zinc-200 dark:border-zinc-700 flex-shrink-0">
                    <h3 class="text-lg font-semibold dark:text-white">Invoice</h3>
                    <button @click="show = false" class="p-2 text-zinc-600 hover:text-zinc-800 dark:text-zinc-300 dark:hover:text-white focus:outline-none">
                        <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                
                <!-- Loading Indicator -->
                <div x-show="isLoading" class="absolute inset-0 flex items-center justify-center bg-white/50 dark:bg-zinc-800/50 z-10 rounded-lg">
                    <div class="inline-flex items-center px-4 py-2 font-semibold leading-6 text-sm shadow rounded-md text-white bg-zinc-800 dark:bg-zinc-200 dark:text-zinc-800 transition ease-in-out duration-150">
                        <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white dark:text-zinc-800" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        Loading invoice...
                    </div>
                </div>
                
                <!-- Modal Body -->
                <div class="flex-grow overflow-hidden">
                    <iframe 
                        src="{{ !empty($orderId) ? route('invoice.generate', ['orderId' => $orderId, 'output' => 'stream']) : '' }}" 
                        class="w-full h-full border-0"
                        @load="isLoading = false"
                        loading="lazy"
                    ></iframe>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>

<script>
// Preload the PDF viewer when page loads to reduce time when opening modal
document.addEventListener('DOMContentLoaded', function() {
    const preloadLink = document.createElement('link');
    preloadLink.rel = 'preload';
    preloadLink.as = 'document';
    preloadLink.href = 'about:blank';
    document.head.appendChild(preloadLink);
});
</script> 