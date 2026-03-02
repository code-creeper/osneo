<?php

namespace App\Rules;

use Closure;
use Exception;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Contracts\Validation\ValidatorAwareRule;
use Illuminate\Validation\Validator;

class RequiredForCurrentLocaleRule implements ValidationRule, ValidatorAwareRule
{
    public bool $implicit = true;

    public ?string $locale;
    protected Validator $validator;

    public function __construct(string $locale = null)
    {
        $this->locale = $locale;
    }

    public function setValidator(Validator $validator): static
    {
        $this->validator = $validator;

        return $this;
    }

    /**
     * @throws Exception
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $locale = $this->getLocale($attribute);

        if ($locale !== config('app.locale')){
            return;
        }

        if (! $this->validator->validateRequired($attribute, $value)){
            $fail('validation.required')->translate([
                'attribute' => $attribute
            ]);
        }
    }

    /**
     * @throws Exception
     */
    private function getLocale($attribute): string
    {
        $locale = $this->locale ?? str($attribute)->afterLast('.')->value();

        if (! $this->isValidLocale($locale)){
            throw new Exception("Locale '$locale' not supported or is Invalid");
        }

        return $locale;
    }

    private function isValidLocale($locale): bool
    {
        $locales = array_keys(getLocales());
        return in_array($locale, $locales);
    }
}
