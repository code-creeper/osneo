<?php

namespace App\Traits;

use App\Models\Preference;
use Cache;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

trait HasPreferences
{

    /*
    |--------------------------------------------------------------------------
    | Relations
    |--------------------------------------------------------------------------
    */

    public function preferences()
    {
        return $this->hasMany(Preference::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Methods
    |--------------------------------------------------------------------------
    */

    public function setPreferences(array $preferences): void
    {
        foreach ($preferences as $name => $preference) {
            $this->setPreference($name, $preference);
        }
    }

    public function setPreference($name, $value): void
    {
        if ( ! in_array($name, array_keys(config('preferences')))) {
            throw new \Exception("Preference $name not found");
        }

        $this->preferences()->updateOrCreate(
            ['name' => $name],
            ['value' => $value]
        );
    }

    public function getPreference($name, $default = null): mixed
    {
        if ( ! in_array($name, array_keys(config('preferences')))) {
            throw new \Exception("Preference $name not found");
        }

        $preference = $this->filterPreferences($name)->first();

        $defaultValue = $default ?? config('preferences')[$name]['default'] ?? null;

        // if preference is not set, send default value
        return $preference ? $preference->value : $defaultValue;
    }

    private function filterPreferences($name): Builder
    {
        $requestingRolePreference = class_basename(self::class) == 'Role';

        $preferences = Preference::query()->where('name', $name);

        // if method is called by Role object, return role preferences only
        if ($requestingRolePreference) {
            return $preferences->where('role_id', $this->id);
        }

        // if caller is User object, and it does not have any primary role, we return user preferences only
        if ( $this->role_id == null) {
            return $preferences->where('user_id', $this->id);
        }

        // if user have a primary role, we get preferences of user and role both
        // and sort by user_id, so that user preference get priority over role preference
        return $preferences
            ->orderByRaw('user_id IS NOT NULL DESC')
            ->where(fn($query) => $query
                ->where('role_id', $this->role_id)
                ->orWhere('user_id', $this->id)
            );
    }
}
