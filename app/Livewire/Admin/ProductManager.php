<?php

namespace App\Livewire\Admin;

use App\Models\Product;
use App\Models\Category;
use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\On;

class ProductManager extends Component
{
    use WithFileUploads;

    public $products;
    public $categories;

    // Form properties
    public $productId;
    public $name;
    public $price;
    public $description;
    public $categoryId;
    public $hasVariations = false;
    public $image;

    // UI states
    public $isEditing = false;
    public $showForm = false;

    public function mount()
    {
        $this->refreshData();
    }

    public function refreshData()
    {
        $this->products = Product::with('category')->get();
        $this->categories = Category::all();
    }

    public function create()
    {
        $this->resetForm();
        $this->showForm = true;
        $this->isEditing = false;
    }

    public function edit(Product $product)
    {
        $this->resetForm();

        $this->productId = $product->id;
        $this->name = $product->name;
        $this->price = $product->price;
        $this->description = $product->description;
        $this->categoryId = $product->category_id;
        $this->hasVariations = $product->has_variations;

        $this->showForm = true;
        $this->isEditing = true;
    }

    public function resetForm()
    {
        $this->productId = null;
        $this->name = '';
        $this->price = '';
        $this->description = '';
        $this->categoryId = null;
        $this->hasVariations = false;
        $this->image = null;
        $this->resetValidation();
    }

    public function cancel()
    {
        $this->showForm = false;
        $this->resetForm();
    }

    public function confirmSave()
    {
        $this->validate([
            'name' => 'required|min:3',
            'price' => 'required|numeric|min:0',
            'description' => 'nullable',
            'categoryId' => 'required|exists:categories,id',
            'image' => 'nullable|image|max:1024', // max 1MB
        ]);

        // After successful validation, show confirmation dialog
        $actionText = $this->isEditing ? 'update' : 'create';
        $productName = $this->name;

        $this->dispatch('showConfirmation', [
            'title' => ($this->isEditing ? 'Update' : 'Create') . ' Product',
            'message' => "Are you sure you want to $actionText \"$productName\"?",
            'confirmText' => $this->isEditing ? 'Update' : 'Create',
            'cancelText' => 'Cancel',
            'action' => 'saveConfirmed'
        ]);
    }

    #[On('saveConfirmed')]
    public function save()
    {
        try {
            $data = [
                'name' => $this->name,
                'price' => $this->price,
                'description' => $this->description,
                'category_id' => $this->categoryId,
                'has_variations' => $this->hasVariations,
            ];

            // Handle image upload
            if ($this->image) {
                // Delete old image if exists
                if ($this->isEditing) {
                    $product = Product::find($this->productId);
                    if ($product && $product->image_path) {
                        Storage::disk('public')->delete($product->image_path);
                    }
                }

                // Store new image
                $imagePath = $this->image->store('products', 'public');
                $data['image_path'] = $imagePath;
            }

            if ($this->isEditing) {
                Product::find($this->productId)->update($data);
                $this->dispatch('showToast', [
                    'type' => 'success',
                    'message' => 'Product Updated',
                    'description' => 'Product has been updated successfully'
                ]);
            } else {
                Product::create($data);
                $this->dispatch('showToast', [
                    'type' => 'success',
                    'message' => 'Product Created',
                    'description' => 'Product has been created successfully'
                ]);
            }

            $this->refreshData();
            $this->dispatch('productSaved');
            $this->resetForm();
        } catch (\Exception $e) {
            $this->dispatch('showToast', [
                'type' => 'danger',
                'message' => 'Error Saving Product',
                'description' => 'There was a problem saving the product: ' . $e->getMessage()
            ]);
        }
    }

    public function delete(Product $product)
    {
        try {
            // Delete product image if exists
            if ($product->image_path) {
                Storage::disk('public')->delete($product->image_path);
            }

            $product->delete();

            $this->dispatch('showToast', [
                'type' => 'info',
                'message' => 'Product Deleted',
                'description' => 'Product has been deleted successfully'
            ]);

            $this->refreshData();
        } catch (\Exception $e) {
            $this->dispatch('showToast', [
                'type' => 'danger',
                'message' => 'Error Deleting Product',
                'description' => 'There was a problem deleting the product: ' . $e->getMessage()
            ]);
        }
    }

    public function render()
    {
        return view('livewire.admin.product-manager');
    }
}
