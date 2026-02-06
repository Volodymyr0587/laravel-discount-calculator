<?php

namespace App\Http\Requests;

use App\Enums\UserType;
use App\Enums\CouponCode;
use App\Enums\DeliveryType;
use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class OrderCalculatorRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'items' => ['required', 'array', 'min:1', 'max:50'],
            'items.*.price' => ['required', 'numeric', 'min:0.01', 'max:999999.99'],
            'items.*.qty' => ['required', 'integer', 'min:1', 'max:1000'],
            'user_type' => ['required', 'string', Rule::enum(UserType::class)],
            'delivery_type' => ['required', 'string', Rule::enum(DeliveryType::class)],
            'coupon' => ['nullable', 'string', Rule::enum(CouponCode::class)],
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'items' => 'order items',
            'items.*.price' => 'item price',
            'items.*.qty' => 'item quantity',
            'user_type' => 'user type',
            'delivery_type' => 'delivery type',
            'coupon' => 'coupon code',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'items.required' => 'Please add at least one item to your order.',
            'items.min' => 'Your order must contain at least one item.',
            'items.*.price.required' => 'Each item must have a price.',
            'items.*.price.min' => 'Item price must be greater than 0.',
            'items.*.qty.required' => 'Each item must have a quantity.',
            'items.*.qty.min' => 'Item quantity must be at least 1.',
        ];
    }
}
