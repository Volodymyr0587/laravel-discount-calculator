<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Order Calculator</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 py-8">
    <div class="max-w-4xl mx-auto px-4">
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <h1 class="text-3xl font-bold text-gray-800 mb-6">Order Calculator</h1>

            @if(session('calculation'))
                <div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-6">
                    <h2 class="text-xl font-semibold text-green-800 mb-3">Calculation Results</h2>
                    <div class="grid grid-cols-2 gap-3 text-sm">
                        <div class="flex justify-between">
                            <span class="text-gray-600">Base Total:</span>
                            <span class="font-semibold">${{ number_format(session('calculation')['base_total'], 2) }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Discount:</span>
                            <span class="font-semibold text-red-600">-${{ number_format(session('calculation')['discount'], 2) }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Tax:</span>
                            <span class="font-semibold">${{ number_format(session('calculation')['tax'], 2) }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Delivery:</span>
                            <span class="font-semibold">${{ number_format(session('calculation')['delivery'], 2) }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Cashback:</span>
                            <span class="font-semibold text-green-600">{{ number_format(session('calculation')['cashback'], 0) }} points</span>
                        </div>
                        <div class="flex justify-between pt-2 border-t border-green-200">
                            <span class="text-gray-800 font-bold">Final Total:</span>
                            <span class="font-bold text-green-800 text-lg">${{ number_format(session('calculation')['final_total'], 2) }}</span>
                        </div>
                    </div>
                </div>
            @endif

            @if ($errors->any())
                <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-6">
                    <h3 class="text-red-800 font-semibold mb-2">Please fix the following errors:</h3>
                    <ul class="list-disc list-inside text-red-600 text-sm space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('order.calculate') }}" method="POST" id="orderForm">
                @csrf

                <!-- Order Items Section -->
                <div class="mb-6">
                    <div class="flex justify-between items-center mb-3">
                        <label class="block text-sm font-semibold text-gray-700">Order Items</label>
                        <button type="button" onclick="addItem()" class="bg-blue-500 hover:bg-blue-600 text-white text-sm px-4 py-2 rounded-lg transition">
                            + Add Item
                        </button>
                    </div>

                    <div id="itemsContainer" class="space-y-3">
                        @if(old('items'))
                            @foreach(old('items') as $index => $item)
                                <div class="item-row flex gap-3 items-start">
                                    <div class="flex-1">
                                        <input type="number" name="items[{{ $index }}][price]"
                                               value="{{ $item['price'] ?? '' }}"
                                               placeholder="Price" step="0.01" min="0"
                                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                    </div>
                                    <div class="flex-1">
                                        <input type="number" name="items[{{ $index }}][qty]"
                                               value="{{ $item['qty'] ?? '' }}"
                                               placeholder="Quantity" min="1"
                                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                    </div>
                                    <button type="button" onclick="removeItem(this)" class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-lg transition">
                                        Remove
                                    </button>
                                </div>
                            @endforeach
                        @else
                            <div class="item-row flex gap-3 items-start">
                                <div class="flex-1">
                                    <input type="number" name="items[0][price]" placeholder="Price" step="0.01" min="0"
                                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                </div>
                                <div class="flex-1">
                                    <input type="number" name="items[0][qty]" placeholder="Quantity" min="1"
                                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                </div>
                                <button type="button" onclick="removeItem(this)" class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-lg transition">
                                    Remove
                                </button>
                            </div>
                        @endif
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <!-- User Type -->
                    <div>
                        <label for="user_type" class="block text-sm font-semibold text-gray-700 mb-2">User Type</label>
                        <select name="user_type" id="user_type" required
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            <option value="">Select User Type</option>
                            @foreach($userTypes as $type)
                                <option value="{{ $type->value }}" {{ old('user_type') == $type->value ? 'selected' : '' }}>
                                    {{ ucfirst(str_replace('_', ' ', $type->value)) }}
                                </option>
                            @endforeach
                        </select>
                        @error('user_type')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Delivery Type -->
                    <div>
                        <label for="delivery_type" class="block text-sm font-semibold text-gray-700 mb-2">Delivery Type</label>
                        <select name="delivery_type" id="delivery_type" required
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            <option value="">Select Delivery Type</option>
                            @foreach($deliveryTypes as $type)
                                <option value="{{ $type->value }}" {{ old('delivery_type') == $type->value ? 'selected' : '' }}>
                                    {{ ucfirst(str_replace('_', ' ', $type->value)) }}
                                </option>
                            @endforeach
                        </select>
                        @error('delivery_type')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Coupon Code -->
                <div class="mb-6">
                    <label for="coupon" class="block text-sm font-semibold text-gray-700 mb-2">Coupon Code (Optional)</label>
                    <select name="coupon" id="coupon"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <option value="">No Coupon</option>
                        @foreach($couponCodes as $coupon)
                            <option value="{{ $coupon->value }}" {{ old('coupon') == $coupon->value ? 'selected' : '' }}>
                                {{ strtoupper($coupon->value) }}
                            </option>
                        @endforeach
                    </select>
                    @error('coupon')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Submit Button -->
                <div>
                    <button type="submit"
                            class="w-full bg-green-600 hover:bg-green-700 text-white font-semibold py-3 px-6 rounded-lg transition shadow-md hover:shadow-lg">
                        Calculate Order Total
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        let itemIndex = {{ old('items') ? count(old('items')) : 1 }};

        function addItem() {
            const container = document.getElementById('itemsContainer');
            const newItem = document.createElement('div');
            newItem.className = 'item-row flex gap-3 items-start';
            newItem.innerHTML = `
                <div class="flex-1">
                    <input type="number" name="items[${itemIndex}][price]" placeholder="Price" step="0.01" min="0"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>
                <div class="flex-1">
                    <input type="number" name="items[${itemIndex}][qty]" placeholder="Quantity" min="1"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>
                <button type="button" onclick="removeItem(this)" class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-lg transition">
                    Remove
                </button>
            `;
            container.appendChild(newItem);
            itemIndex++;
        }

        function removeItem(button) {
            const container = document.getElementById('itemsContainer');
            if (container.children.length > 1) {
                button.closest('.item-row').remove();
            } else {
                alert('You must have at least one item in your order.');
            }
        }
    </script>
</body>
</html>
