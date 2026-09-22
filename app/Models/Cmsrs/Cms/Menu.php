<?php

declare(strict_types=1);

namespace App\Models\Cmsrs\Cms;

use App\Models\Cmsrs\Interfaces\TranslatableInterface;
use App\Models\Cmsrs\Traits\HasTranslationsTrait;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int|null $position
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Collection<int, Page> $pages
 * @property-read int|null $pages_count
 * @property-read Collection<int, Translate> $translates
 * @property-read int|null $translates_count
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Menu newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Menu newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Menu query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Menu whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Menu whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Menu wherePosition($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Menu whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class Menu extends Model implements TranslatableInterface
{
    use HasTranslationsTrait;

    protected $fillable = [
        'position',
    ];

    protected $casts = [
        'position' => 'integer',
    ];

    /**
     * @var list<string>
     */
    public $requiredColumn = [
        'name',
    ];

    /**
     * @return HasMany<Page, $this>
     */
    public function pages()
    {
        return $this->hasMany(Page::class);
    }

    /**
     * @return HasMany<Translate, $this>
     */
    public function translates(): HasMany
    {
        return $this->hasMany(Translate::class, 'menu_id', 'id'); // it should be work without params - phpstan
    }

    public function getId(): int
    {
        return $this->id;
    }
}
