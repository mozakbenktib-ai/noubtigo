<?php

namespace App\Modules\Core\Traits;

use App\Models\Translation;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Facades\App;

trait HasTranslations
{
    /**
     * Get all of the resource's translations.
     */
    public function translations(): MorphMany
    {
        return $this->morphMany(Translation::class, 'translatable');
    }

    /**
     * Get a translation for a specific key.
     */
    public function getTranslation(string $key, ?string $locale = null): ?string
    {
        $locale = $locale ?: App::getLocale();

        $translation = $this->translations()
            ->where('locale', $locale)
            ->where('key', $key)
            ->first();

        return $translation ? $translation->value : $this->{$key};
    }

    /**
     * Set a translation for a specific key.
     */
    public function setTranslation(string $key, string $value, string $locale): void
    {
        $this->translations()->updateOrCreate(
            ['locale' => $locale, 'key' => $key],
            ['value' => $value]
        );
    }
}
