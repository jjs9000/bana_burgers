<div class="p-4">
    <!-- Include Toast Component -->
    <x-toast />
    
    <!-- Include Confirmation Modal Component -->
    <x-confirmation-modal />
    
    <div class="flex justify-between items-center mb-4">
        <h1 class="text-2xl font-bold dark:text-white">Manage Products</h1>
        <button 
            x-data
            @click="window.dispatchEvent(new CustomEvent('open-product-modal', { detail: { isEditing: false } }))"
            class="px-4 py-2 bg-black text-white dark:bg-white dark:text-black rounded-md hover:bg-zinc-800 dark:hover:bg-zinc-200"
        >
            Add New Product
        </button>
    </div>
    
    <!-- Products List -->
    <div class="bg-white dark:bg-zinc-800 rounded-lg shadow overflow-hidden">
        <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-700">
            <thead class="bg-zinc-50 dark:bg-zinc-700">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-300 uppercase tracking-wider">Image</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-300 uppercase tracking-wider">Name</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-300 uppercase tracking-wider">Category</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-300 uppercase tracking-wider">Price</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-300 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white dark:bg-zinc-800 divide-y divide-zinc-200 dark:divide-zinc-700">
                @forelse($products as $product)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($product->image_path)
                                <img src="{{ asset('storage/' . $product->image_path) }}" alt="{{ $product->name }}" class="h-10 w-10 rounded-full object-cover">
                            @else
                                <div class="h-10 w-10 rounded-full bg-zinc-200 dark:bg-zinc-600 flex items-center justify-center text-zinc-500 dark:text-zinc-400">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                </div>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-zinc-900 dark:text-white">{{ $product->name }}</div>
                            <div class="text-xs text-zinc-500 dark:text-zinc-400">{{ Str::limit($product->description, 30) }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-zinc-500 dark:text-zinc-400">
                            {{ $product->category->name ?? 'No Category' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-zinc-500 dark:text-zinc-400">
                            RM{{ number_format($product->price, 2) }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <button 
                                x-data
                                @click="window.dispatchEvent(new CustomEvent('open-product-modal', { detail: { isEditing: true, productId: '{{ $product->id }}' } }))"
                                class="text-zinc-600 hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-white mr-3"
                            >
                                Edit
                            </button>
                            <button 
                                x-data
                                @click="window.confirmAction({
                                    title: 'Delete Product',
                                    message: 'Are you sure you want to delete {{ $product->name }}? This action cannot be undone.',
                                    confirmText: 'Delete',
                                    cancelText: 'Cancel',
                                    onConfirm: () => { $wire.delete('{{ $product->id }}') }
                                })"
                                class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300"
                            >
                                Delete
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-6 py-4 text-center text-zinc-500 dark:text-zinc-400">
                            No products found
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Product Form Modal using Pines UI Pattern -->
    <div x-data="{ 
            modalOpen: false,
            isEditing: false
        }"
        @open-product-modal.window="
            isEditing = $event.detail.isEditing;
            if (isEditing) {
                $wire.edit($event.detail.productId);
            } else {
                $wire.create();
            }
            modalOpen = true;
        "
        @keydown.escape.window="modalOpen = false"
        @productSaved.window="modalOpen = false"
        :class="{ 'z-[140]': modalOpen }" 
        class="relative w-auto h-auto">
        
        <template x-teleport="body">
            <div x-show="modalOpen" 
                class="fixed top-0 left-0 z-[150] flex items-center justify-center w-screen h-screen" 
                x-cloak>
                
                <div x-show="modalOpen"
                    x-transition:enter="ease-out duration-300"
                    x-transition:enter-start="opacity-0"
                    x-transition:enter-end="opacity-100"
                    x-transition:leave="ease-in duration-300"
                    x-transition:leave-start="opacity-100"
                    x-transition:leave-end="opacity-0"
                    @click="modalOpen = false" 
                    class="absolute inset-0 w-full h-full bg-black/50 backdrop-blur-sm">
                </div>
                
                <div x-show="modalOpen"
                    x-trap.noscroll="modalOpen"
                    x-transition:enter="ease-out duration-300"
                    x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                    x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                    x-transition:leave="ease-in duration-200"
                    x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                    x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                    @click.away="modalOpen = false"
                    class="relative w-full max-h-[90vh] overflow-auto py-6 bg-white border shadow-lg px-7 border-neutral-200 dark:bg-zinc-800 dark:border-zinc-700 sm:max-w-3xl sm:rounded-lg">
                    
                    <div class="flex items-center justify-between pb-3">
                        <h3 class="text-lg font-semibold dark:text-white">{{ $isEditing ? 'Edit' : 'Add' }} Product</h3>
                        <button @click="modalOpen = false" 
                            class="absolute top-0 right-0 flex items-center justify-center w-8 h-8 mt-5 mr-5 text-gray-600 rounded-full hover:text-gray-800 hover:bg-gray-50 dark:text-zinc-400 dark:hover:text-zinc-200 dark:hover:bg-zinc-700">
                            <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                    
                    <div class="relative w-auto pb-6">
                        @if($showForm)
                        <form wire:submit.prevent="confirmSave" class="space-y-4">
                            <!-- Name -->
                            <div>
                                <label class="block text-sm text-zinc-500 dark:text-zinc-400 mb-1">Product Name</label>
                                <input 
                                    type="text"
                                    wire:model="name" 
                                    class="w-full p-2 border border-zinc-200 dark:border-zinc-700 rounded-md focus:outline-none focus:ring-2 focus:ring-zinc-500 bg-zinc-50 dark:bg-zinc-700 dark:text-white"
                                    placeholder="Enter product name"
                                >
                                @error('name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>
                            
                            <!-- Price & Category -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm text-zinc-500 dark:text-zinc-400 mb-1">Price (RM)</label>
                                    <input 
                                        type="number"
                                        step="0.01"
                                        wire:model="price" 
                                        class="w-full p-2 border border-zinc-200 dark:border-zinc-700 rounded-md focus:outline-none focus:ring-2 focus:ring-zinc-500 bg-zinc-50 dark:bg-zinc-700 dark:text-white"
                                        placeholder="0.00"
                                    >
                                    @error('price') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>
                                
                                <div>
                                    <label class="block text-sm text-zinc-500 dark:text-zinc-400 mb-1">Category</label>
                                    <select 
                                        wire:model="categoryId" 
                                        class="w-full p-2 border border-zinc-200 dark:border-zinc-700 rounded-md focus:outline-none focus:ring-2 focus:ring-zinc-500 bg-zinc-50 dark:bg-zinc-700 dark:text-white"
                                    >
                                        <option value="">Select Category</option>
                                        @foreach($categories as $category)
                                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('categoryId') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            
                            <!-- Description -->
                            <div>
                                <label class="block text-sm text-zinc-500 dark:text-zinc-400 mb-1">Description</label>
                                <textarea 
                                    wire:model="description" 
                                    class="w-full p-2 border border-zinc-200 dark:border-zinc-700 rounded-md focus:outline-none focus:ring-2 focus:ring-zinc-500 bg-zinc-50 dark:bg-zinc-700 dark:text-white"
                                    placeholder="Enter product description"
                                    rows="3"
                                ></textarea>
                            </div>
                            
                            <!-- Has Variations -->
                            <div class="flex items-center">
                                <input 
                                    type="checkbox"
                                    wire:model="hasVariations" 
                                    class="h-4 w-4 text-black focus:ring-black border-gray-300 rounded"
                                >
                                <label class="ml-2 block text-sm text-zinc-700 dark:text-zinc-300">
                                    Product has variations
                                </label>
                            </div>
                            
                            <!-- Image Upload -->
                            <div>
                                <label class="block text-sm text-zinc-500 dark:text-zinc-400 mb-1">Product Image</label>
                                <input 
                                    type="file"
                                    wire:model="image" 
                                    class="w-full p-2 border border-zinc-200 dark:border-zinc-700 rounded-md focus:outline-none focus:ring-2 focus:ring-zinc-500 bg-zinc-50 dark:bg-zinc-700 dark:text-white"
                                    accept="image/*"
                                >
                                @error('image') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                
                                <div wire:loading wire:target="image" class="mt-2 text-sm text-zinc-500">
                                    Uploading...
                                </div>
                                
                                @if($image)
                                    <div class="mt-2">
                                        <p class="text-sm text-zinc-500 dark:text-zinc-400">Image Preview:</p>
                                        <img src="{{ $image->temporaryUrl() }}" class="mt-1 h-24 w-auto object-cover rounded-md">
                                    </div>
                                @elseif($isEditing && $productId)
                                    @php
                                        $product = \App\Models\Product::find($productId);
                                    @endphp
                                    @if($product && $product->image_path)
                                        <div class="mt-2">
                                            <p class="text-sm text-zinc-500 dark:text-zinc-400">Current Image:</p>
                                            <img src="{{ asset('storage/' . $product->image_path) }}" class="mt-1 h-24 w-auto object-cover rounded-md">
                                        </div>
                                    @endif
                                @endif
                            </div>
                            
                            <!-- Form Buttons -->
                            <div class="flex flex-col-reverse sm:flex-row sm:justify-end sm:space-x-2 pt-4">
                                <button 
                                    type="button"
                                    @click="modalOpen = false"
                                    class="inline-flex items-center justify-center h-10 px-4 py-2 text-sm font-medium transition-colors border border-zinc-200 dark:border-zinc-700 rounded-md hover:bg-zinc-100 dark:hover:bg-zinc-700 dark:text-white focus:outline-none focus:ring-2 focus:ring-zinc-200 focus:ring-offset-2 mt-3 sm:mt-0"
                                >
                                    Cancel
                                </button>
                                <button 
                                    type="submit"
                                    class="inline-flex items-center justify-center h-10 px-4 py-2 text-sm font-medium text-white transition-colors bg-black border border-transparent rounded-md dark:bg-white dark:text-black hover:bg-zinc-800 dark:hover:bg-zinc-200 focus:outline-none focus:ring-2 focus:ring-zinc-500 focus:ring-offset-2"
                                >
                                    {{ $isEditing ? 'Update' : 'Save' }} Product
                                </button>
                            </div>
                        </form>
                        @endif
                    </div>
                </div>
            </div>
        </template>
    </div>

    <!-- JavaScript for Livewire-Alpine integration -->
    <script>
        document.addEventListener('livewire:init', function() {
            // Handle toast notifications
            Livewire.on('showToast', (data) => {
                console.log('Toast notification triggered:', data);
                window.toast(data[0].message, {
                    type: data[0].type,
                    description: data[0].description
                });
            });
            
            // Handle confirmation dialog interaction
            Livewire.on('showConfirmation', (data) => {
                window.confirmAction({
                    title: data[0].title,
                    message: data[0].message,
                    confirmText: data[0].confirmText,
                    cancelText: data[0].cancelText,
                    onConfirm: () => { 
                        Livewire.dispatch(data[0].action);
                    }
                });
            });
        });
    </script>
</div>
