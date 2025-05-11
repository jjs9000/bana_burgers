<div class="p-4">
    <div class="flex justify-between items-center mb-4">
        <h1 class="text-2xl font-bold dark:text-white">Manage Products</h1>
        <button 
            wire:click="create"
            class="px-4 py-2 bg-black text-white dark:bg-white dark:text-black rounded-md hover:bg-zinc-800 dark:hover:bg-zinc-200"
        >
            Add New Product
        </button>
    </div>
    
    <!-- Product Form -->
    @if($showForm)
    <div class="bg-white dark:bg-zinc-800 rounded-lg shadow p-4 mb-6">
        <h2 class="text-lg font-bold mb-4 dark:text-white">{{ $isEditing ? 'Edit' : 'Add' }} Product</h2>
        
        <form wire:submit.prevent="save" class="space-y-4">
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
            
            <!-- Buttons -->
            <div class="flex justify-end space-x-2">
                <button 
                    type="button"
                    wire:click="cancel" 
                    class="px-4 py-2 border border-zinc-200 dark:border-zinc-700 rounded-md hover:bg-zinc-100 dark:hover:bg-zinc-700 dark:text-white"
                >
                    Cancel
                </button>
                <button 
                    type="submit"
                    class="px-4 py-2 bg-black text-white dark:bg-white dark:text-black rounded-md hover:bg-zinc-800 dark:hover:bg-zinc-200"
                >
                    {{ $isEditing ? 'Update' : 'Save' }} Product
                </button>
            </div>
        </form>
    </div>
    @endif
    
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
                                wire:click="edit({{ $product->id }})" 
                                class="text-zinc-600 hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-white mr-3"
                            >
                                Edit
                            </button>
                            <button 
                                wire:click="delete({{ $product->id }})"
                                wire:confirm="Are you sure you want to delete this product?" 
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
</div>
