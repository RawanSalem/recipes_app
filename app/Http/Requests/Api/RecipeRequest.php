<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class RecipeRequest extends FormRequest
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
        $rules = [
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'ingredients' => 'required|array',
            'ingredients.*.name' => 'required|string',
            'ingredients.*.amount' => 'required|numeric|min:0',
            'ingredients.*.unit' => 'required|string',
            'steps' => 'required|array',
            'steps.*.step' => 'required|integer|min:1',
            'steps.*.instruction' => 'required|string',
            'cuisine' => 'required|string|max:255',
            'diet_tags' => 'nullable|array',
            'diet_tags.*' => 'string',
            'cooking_time' => 'required|integer|min:1',
            'categories' => 'required|array',
            'categories.*' => 'exists:categories,id',
            'image' => 'nullable|image|max:2048'
        ];

        // For updates, make fields optional
        if ($this->isMethod('PUT') || $this->isMethod('PATCH')) {
            $rules = array_map(function ($rule) {
                return str_replace('required|', 'sometimes|required|', $rule);
            }, $rules);
        }

        return $rules;
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'title.required' => 'The recipe title is required.',
            'description.required' => 'The recipe description is required.',
            'ingredients.required' => 'The ingredients list is required.',
            'ingredients.array' => 'Ingredients must be an array.',
            'ingredients.*.name.required' => 'Each ingredient must have a name.',
            'ingredients.*.amount.required' => 'Each ingredient must have an amount.',
            'ingredients.*.amount.numeric' => 'Ingredient amount must be a number.',
            'ingredients.*.amount.min' => 'Ingredient amount must be at least 0.',
            'ingredients.*.unit.required' => 'Each ingredient must have a unit.',
            'steps.required' => 'The cooking steps are required.',
            'steps.array' => 'Steps must be an array.',
            'steps.*.step.required' => 'Each step must have a step number.',
            'steps.*.step.integer' => 'Step number must be a whole number.',
            'steps.*.step.min' => 'Step number must be at least 1.',
            'steps.*.instruction.required' => 'Each step must have instructions.',
            'cuisine.required' => 'The cuisine type is required.',
            'diet_tags.array' => 'Diet tags must be an array.',
            'diet_tags.*.string' => 'Each diet tag must be a string.',
            'cooking_time.required' => 'The cooking time is required.',
            'cooking_time.integer' => 'The cooking time must be a number.',
            'cooking_time.min' => 'The cooking time must be at least 1 minute.',
            'categories.required' => 'At least one category is required.',
            'categories.array' => 'Categories must be an array.',
            'categories.*.exists' => 'One or more selected categories do not exist.',
            'image.image' => 'The file must be an image.',
            'image.max' => 'The image size must not exceed 2MB.',
        ];
    }
} 