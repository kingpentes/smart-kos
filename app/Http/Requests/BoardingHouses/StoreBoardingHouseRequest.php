<?php

namespace App\Http\Requests\BoardingHouses;

use App\Enums\BoardingHouseType;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreBoardingHouseRequest extends FormRequest
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
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string', 'max:5000'],
            'address' => ['required', 'string', 'max:1000'],
            'city' => ['required', 'string', 'max:100'],
            'district' => ['nullable', 'string', 'max:100'],
            'type' => ['required', Rule::enum(BoardingHouseType::class)],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'price_monthly' => ['required', 'integer', 'min:1'],
            'deposit_amount' => ['nullable', 'integer', 'min:0'],
            'room_count' => ['required', 'integer', 'min:1', 'max:200'],
            'facilities' => ['nullable', 'array'],
            'facilities.*' => ['integer', 'exists:facilities,id'],
            'rules' => ['nullable', 'array'],
            'rules.*.key' => ['nullable', 'required_with:rules.*.value', 'string', 'max:100'],
            'rules.*.value' => ['nullable', 'required_with:rules.*.key', 'string', 'max:1000'],
            'photos' => ['nullable', 'array', 'max:10'],
            'photos.*' => ['image', 'max:2048'],
        ];
    }
}
