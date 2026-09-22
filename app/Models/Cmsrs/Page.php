<?php

declare(strict_types=1);

namespace App\Models\Cmsrs;

use App\Models\Cmsrs\Interfaces\ContentTranslatableInterface;
use App\Models\Cmsrs\Tags\Tag;
use App\Models\Cmsrs\Traits\HasTranslationsTrait;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $published
 * @property int $commented
 * @property int $after_login
 * @property int|null $position
 * @property string $type
 * @property int|null $page_id
 * @property int|null $menu_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Collection<int, Content> $contents
 * @property-read int|null $contents_count
 * @property-read Collection<int, Image> $images
 * @property-read int|null $images_count
 * @property-read Menu|null $menu
 * @property-read Collection<int, Translate> $translates
 * @property-read int|null $translates_count
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Page newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Page newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Page query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Page whereAfterLogin($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Page whereCommented($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Page whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Page whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Page whereMenuId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Page wherePageId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Page wherePosition($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Page wherePublished($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Page whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Page whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class Page extends Model implements ContentTranslatableInterface
{
    use HasTranslationsTrait;

    const PREFIX_CMS_URL = 'cms';

    const PREFIX_CMS_ONE_PAGE_IN_MENU_URL = 'o';

    // const PREFIX_SHOP_URL = 'shop';
    const PREFIX_IN_URL = 'in'; // (in) independent

    protected $fillable = [
        'published',
        'commented',
        'after_login',
        'position',
        'type',
        'menu_id',
        'page_id',
    ];

    /** @var list<string> */
    public array $requiredColumn = [
        'title',
        'short_title',
    ];

    protected $casts = [
        'published' => 'integer',
        'commented' => 'integer',
        'after_login' => 'integer',
        'position' => 'integer',
        'menu_id' => 'integer',
        'page_id' => 'integer',
    ];

    /**
     * cmsrs page fields that are used in the list of pages in the admin panel and in the page tree
     * old name: $pageFields
     *
     * @var array<int, string>
     */
    public const FIELDS = [
        'id',
        'published',
        'commented',
        'after_login',
        'position',
        'type',
        'menu_id',
        'page_id',
    ];

    /**
     * @return BelongsTo<Menu, $this>
     */
    public function menu(): BelongsTo
    {
        return $this->belongsTo('App\Models\Cmsrs\Menu', 'menu_id', 'id');
    }

    /**
     * @return HasMany<Translate, $this>
     */
    public function translates(): HasMany
    {
        return $this->hasMany('App\Models\Cmsrs\Translate');
    }

    /**
     * @return HasMany<Content, $this>
     */
    public function contents(): HasMany
    {
        return $this->hasMany('App\Models\Cmsrs\Content');
    }

    /**
     * @return HasMany<Image, $this>
     */
    public function images(): HasMany
    {
        return $this->hasMany('App\Models\Cmsrs\Image')->orderBy('position');
    }

    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return MorphToMany<Tag, $this>
     */
    public function tags(): MorphToMany
    {
        return $this->morphToMany(Tag::class, 'taggable')->withPivot('lang');
    }
}
