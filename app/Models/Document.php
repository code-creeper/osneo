<?php

namespace App\Models;

use App\MediaLibrary\InteractsWithMedia;
use App\Observers\DocumentObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;
use Spatie\MediaLibrary\HasMedia;
use Spatie\SchemalessAttributes\Casts\SchemalessAttributes;
use Log;
use DB;

#[ObservedBy(DocumentObserver::class)]
class Document extends Model implements HasMedia
{
    use HasFactory;

    use InteractsWithMedia;
    use SoftDeletes;

    protected $guarded = ['id'];

    public static string $inboxDir = 'Inbox';
    public static string $sortedDir = 'Ablage';
    public static string $trashDir = 'Papierkorb'; //Previously Trash, has been changed to German.

    public static array $allowedExtensions = ['pdf', 'txt', 'png', 'jpg', 'jpeg', 'gif', 'svg'];

    public $casts = [
        'properties' => SchemalessAttributes::class,
        'sorted_on' => 'date',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relations
    |--------------------------------------------------------------------------
    */

    public function documentType(): BelongsTo
    {
        return $this->belongsTo(DocumentType::class);
    }

    public function invoice(): HasOne
    {
        return $this->hasOne(Invoice::class);
    }

    public function documentable(): MorphTo
    {
        return $this->morphTo();
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by')->withDefault([
            'first_name' => __('Direct Upload'),
        ]);
    }

    public function sorter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sorted_by')->withDefault([
            'first_name' => __('Unknown'),
        ]);
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */

    public function scopeWithProperties(): Builder
    {
        return $this->properties->modelScope();
    }

    public function scopeRelevant(Builder $query): Builder
    {
        if (!auth()->check()){
            return $query;
        }

        if (user()->can('view all documents')){
            return $query;
        }

        $query->where(function (Builder $query){
            if (user()->can('view own documents')){
                $query->orWhere('uploaded_by', user()->id);
            }

            if (user()->can('view assigned documents')){
                $query->orWhereIn('id', DB::table('document_user')
                    ->where('user_id', auth()->id())
                    ->pluck('document_id')
                    ->toArray()
                );
            }

        });

        return $query;
    }

    public function scopeInbox(Builder $query, $fileNames): Builder
    {
        return $query
            ->whereIn('status', [0, 1])
            ->where('name', '!=', '#NICHT LÖSCHEN!!!.md')
            ->whereIn('name', $fileNames);
    }

    public function scopeSorted(Builder $query): Builder
    {
        return $query->whereNotNull('sorted_path');
    }

    public function scopeAllowed(Builder $query, int|array $allowedDocumentTypes): Builder
    {
        if (auth()->check() && user()->can('view all documents')){
            return $query;
        }

        if (!is_array($allowedDocumentTypes)){
            $allowedDocumentTypes = array($allowedDocumentTypes);
        }

        return $query->whereIn('document_type_id', $allowedDocumentTypes);
    }

    /*
    |--------------------------------------------------------------------------
    | Attributes
    |--------------------------------------------------------------------------
    */

    public function getPdfPathAttribute()
    {
        if ($this->trashed()) {
            return $this->trashPath();
        }

        return $this->sorted ? $this->sorted_path : $this->inboxPath();
    }

    public function sorted(): Attribute
    {
        return Attribute::make(
            get: fn() => (bool)$this->sorted_path,
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Methods
    |--------------------------------------------------------------------------
    */

    public function name(): string
    {
        return $this->sorted ? str($this->sorted_path)->afterLast('/') : $this->name;
    }

    public function isLexOfficeFile(): bool
    {
        return (bool)$this->lexoffice_id;
    }

    public function inboxPath(): string
    {
        return self::$inboxDir."/$this->name";
    }

    public function trashPath(): string
    {
        return self::$trashDir.'/'. $this->name();
    }

    public function makeNameUnique($name, $movingTo = "inbox"): string
    {
        $checkDuplicateQuery = fn($name) => Document::whereIn('status', [0, 1])->where('name', $name);

        // check duplicates in sorted dir
        if ($movingTo == "sort") {
            $checkDuplicateQuery = fn($name) => Document::where('status', 2)
                ->where('sorted_on', $this->sorted_on)->where('sorted_path', $name);
        }

        $documentExists = $checkDuplicateQuery($name)->count();

        if ( ! $documentExists) {
            return $name;
        }

        $ext = str($name)->afterLast('.');

        $i = 1;

        do {
            $uniqueName = str($name)->beforeLast('.')->append("($i)")->append(".$ext");
            $i++;
        } while ($checkDuplicateQuery($uniqueName)->count());

        return $uniqueName;
    }

    public function getUrl(): ?string
    {
        if (!Storage::exists($this->pdf_path)){
            Log::error("File doest not exist at '$this->pdf_path' \n Document ID: $this->id");
            return null;
        }

        $extension = strtolower(\Arr::last(explode('.', $this->pdf_path)));

        if ( ! in_array($extension, self::$allowedExtensions)) {
            Log::error("The file with $extension extension is not allowed");
            return null;
        }


        return Storage::disk('s3')->temporaryUrl($this->pdf_path, now()->addMinute());
    }

    /**
     * @param $withDirName: if true, return file names including dir name
     *
     * @return array: array of file names in inbox directory
     */
    public static function getInboxFiles(bool $withDirName = false): array
    {
        $files = Storage::files(self::$inboxDir);

        if ($withDirName){
            return $files;
        }

        return Arr::map($files, function ($value){
           return str($value)->after(self::$inboxDir."/")->value();
        });
    }

    public function generateName(): void
    {
        $dateStr = $this->sorted_on->format('dmY');
        $doc_type = $this->documentType->key;
        $year = $this->sorted_on->format('Y');
        $month = $this->sorted_on->format('m');
        $ext = str($this->name)->afterLast(".");

        $filename = str("{$dateStr}_{$this->source}_{$doc_type}");
        $properties = DocumentProperty::forNaming($this->document_type_id)->get();

        foreach ($properties as $property){
            $propertyValue = $this->properties->get($property->id)['value'] ?? '';

            if ($propertyValue){
                $filename = $filename->append("_$propertyValue");
            }
        }

        $filename = $filename
            ->replace(['/', '\\', ':', '*', '?', '"', '<','>', '|'], '')
            ->append(".$ext")
            ->prepend(self::$sortedDir."/$this->source/$doc_type/$year/$month/");

        $filename = $this->makeNameUnique($filename, 'sort');

        $this->update([
            'sorted_path' => $filename,
        ]);
    }

    public function createInvoice(): void
    {
        if ((int)$this->document_type_id !== 1) {
            return;
        }

        $this->invoice()->create();
    }
}
