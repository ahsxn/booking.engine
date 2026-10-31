<?php

namespace App\Http\Requests\Events;

use Illuminate\Foundation\Http\FormRequest;

class ListEventsRequest extends FormRequest
{
    protected $redirectRoute = 'home';

    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function validationData(): array
    {
        return $this->query->all();
    }

    /** @return array<string, list<string>> */
    public function rules(): array
    {
        return ['view' => ['sometimes', 'required', 'string', 'in:upcoming,past']];
    }

    /** @return 'upcoming'|'past' */
    public function selectedView(): string
    {
        /** @var array{view?: 'upcoming'|'past'} $validated */
        $validated = $this->validated();

        return $validated['view'] ?? 'upcoming';
    }
}
