<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Models\Event;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class EventRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return Auth::check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'tree_id' => ['required', 'integer', 'exists:trees,id'],
            'individual_id' => ['nullable', 'integer', 'exists:individuals,id'],
            'family_id' => ['nullable', 'integer', 'exists:families,id'],
            'type' => ['required', 'string', Rule::in(array_keys(Event::getEventTypes()))],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'event_date' => ['nullable', 'date', 'before_or_equal:today'],
            'event_place' => ['nullable', 'string', 'max:255'],
            'event_city' => ['nullable', 'string', 'max:255'],
            'event_state' => ['nullable', 'string', 'max:255'],
            'event_country' => ['nullable', 'string', 'max:255'],
            'event_latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'event_longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'additional_data' => ['nullable', 'array'],
            'gedcom_xref' => ['nullable', 'string', 'max:255'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'tree_id.required' => 'A tree must be selected.',
            'tree_id.exists' => 'The selected tree does not exist.',
            'individual_id.exists' => 'The selected individual does not exist.',
            'family_id.exists' => 'The selected family does not exist.',
            'type.required' => 'Event type is required.',
            'type.in' => 'The selected event type is invalid.',
            'title.required' => 'Event title is required.',
            'title.max' => 'Event title cannot exceed 255 characters.',
            'description.max' => 'Event description cannot exceed 1000 characters.',
            'event_date.date' => 'Event date must be a valid date.',
            'event_date.before_or_equal' => 'Event date cannot be in the future.',
            'event_latitude.between' => 'Latitude must be between -90 and 90.',
            'event_longitude.between' => 'Longitude must be between -180 and 180.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'tree_id' => 'tree',
            'individual_id' => 'individual',
            'family_id' => 'family',
            'type' => 'event type',
            'title' => 'event title',
            'description' => 'event description',
            'event_date' => 'event date',
            'event_place' => 'event place',
            'event_city' => 'event city',
            'event_state' => 'event state',
            'event_country' => 'event country',
            'event_latitude' => 'latitude',
            'event_longitude' => 'longitude',
            'additional_data' => 'additional data',
            'gedcom_xref' => 'GEDCOM reference',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Ensure at least one of individual_id or family_id is provided
        if (empty($this->individual_id) && empty($this->family_id)) {
            $this->merge([
                'individual_id' => null,
                'family_id' => null,
            ]);
        }

        // Clean up coordinates if provided
        if ($this->has('event_latitude') && $this->has('event_longitude')) {
            $this->merge([
                'event_latitude' => (float) $this->event_latitude,
                'event_longitude' => (float) $this->event_longitude,
            ]);
        }
    }
}
