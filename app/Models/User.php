<?php

namespace App\Models;

use App\ActivityFormatters\UserActivityFormatter;
use App\Contracts\ActivityFormatter;
use App\Traits\Employee\HasVehicle;
use App\Traits\HasPreferences;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use App\Traits\Notifiable;
use App\Traits\Employee\HasAttendance;
use App\Traits\Employee\HasLeave;
use App\Traits\LogsActivity;
use App\Traits\Sortable;
use Illuminate\Support\Facades\Auth;
use Kirschbaum\PowerJoins\PowerJoins;
use Spatie\Permission\Traits\HasRoles;
use Spatie\SchemalessAttributes\SchemalessAttributesTrait;

class User extends Authenticatable implements ActivityFormatter
{
    use HasFactory, Notifiable, HasRoles, SchemalessAttributesTrait, PowerJoins;
    use Sortable, LogsActivity;
    use HasLeave, HasAttendance, HasVehicle, HasPreferences;
    use UserActivityFormatter;

    protected $guarded = ['id'];

    protected $hidden = ['password', 'remember_token',];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'dob' => 'date',
        'activate_on' => 'date',
        'deactivate_on' => 'date',
    ];

    protected $schemalessAttributes = ['config'];

    protected array $nonLoggable = ['password'];

    public static function boot(): void
    {
        parent::boot();

        self::deleting(function (self $user) {
            $user->employments()->delete();
        });
    }

    protected static function booted(): void
    {
        static::addGlobalScope('active', fn(Builder $builder) => $builder->active());
    }

    /*
    |--------------------------------------------------------------------------
    | Relations
    |--------------------------------------------------------------------------
    */

    public function announcements(): BelongsToMany
    {
        return $this->belongsToMany(Announcement::class);
    }

    public function unreadAnnouncements(): BelongsToMany
    {
        return $this->announcements()->wherePivotNull('read_at');
    }

    public function primaryRole(): BelongsTo
    {
        return $this->belongsTo(Role::class, 'role_id');
    }

    public function documents(): BelongsToMany
    {
        return $this->belongsToMany(Document::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */

    public function scopeWithConfig(): Builder
    {
        return $this->config->modelScope();
    }

    public function scopeRelevant(Builder $query): Builder
    {
        return $query->whereKeyNot(config('app.system_user_id'));
    }

    public function scopeActive($query): Builder
    {
        return $query->where('active', 1);
    }

    /*
    |--------------------------------------------------------------------------
    | Attributes
    |--------------------------------------------------------------------------
    */

    public function getAvatarUrlAttribute(): string
    {
        /*$path = asset('images/defaults/avatar.jpg');*/
        $path = asset('assets/images/users/avatar-ww.png');
        if ($this->avatar && file_exists($this->avatar)) {
            $path = asset($this->avatar);
        }

        return $path;
    }

    public function getNameAttribute(): string
    {
        return "$this->first_name $this->last_name";
    }

    /*public function getRoleAttribute()
    {
        return $this->role?->display_name;
    }*/

    /*
    |--------------------------------------------------------------------------
    | Mutators
    |--------------------------------------------------------------------------
    */

    public function setPasswordAttribute($value): void
    {
        // well, there are chances that some password really starts with
        // $2y$, but having password of length 60 is practically impossible.
        // so let's assume that it's an encrypted string!
        // let's not encrypt it!

        $isEncrypted = strlen($value) == 60 && strpos($value, '$2y$') == 0;
        $this->attributes['password'] = $isEncrypted ? $value : bcrypt($value);
    }

    /*
    |--------------------------------------------------------------------------
    | Methods
    |--------------------------------------------------------------------------
    */

    public static function forceFind($id): User
    {
        return User::withoutGlobalScopes()->find($id);
    }

    public function isAdmin(): bool
    {
        return $this->hasRole(['super_admin', 'admin']) || $this->isSuperAdmin();
    }

    public function isSuperAdmin(): bool
    {
        //todo:: commented this for tests, as it creates user with id 1 which becomes superadmin
        // and fail tests

        return in_array($this->email, [
            'admin@system.com',
            'admin@workatww.onmicrosoft.com',
        ]);
    }

    public function isSystem(): bool
    {
        return $this->id == config('app.system_user_id');
    }

    public function hasUnreadAnnouncements(): bool
    {
        return (bool) $this->unreadAnnouncements->count();
    }

    public function loginAs(): void
    {
        abort_if(user()->cannot('switch to other users'), 403);

        session()->put('admin_id', auth()->id());
        Auth::login($this);
        /*return redirect()->back()->with('success',
            __('You are now logged in as :user', ['user' => $this->name])
        );*/
    }

}
