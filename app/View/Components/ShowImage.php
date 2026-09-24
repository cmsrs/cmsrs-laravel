<?php

namespace App\View\Components;

use App\Models\Cmsrs\Image;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class ShowImage extends Component
{
    /**
     * @var Image
     */
    public $image;

    /**
     * @var string
     */
    public $lang;

    /**
     * @var string|null
     */
    public $class;

    /**
     * Create a new component instance.
     */
    public function __construct(Image $image, string $lang, ?string $class = null)
    {
        $this->image = $image;
        $this->lang = $lang;
        $this->class = $class;
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.show-image', [
            'image' => $this->image,
            'lang' => $this->lang,
            'class' => $this->class,
        ]);
    }
}
