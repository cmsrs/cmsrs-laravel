<?php

declare(strict_types=1);

namespace App\Data;

use App\Models\Cmsrs\Cms\Page;
use App\Models\Cmsrs\Shop\Product;
use App\Services\Cmsrs\MenuService;
use App\Services\Cmsrs\Page\PageService;
use App\Services\Cmsrs\Product\ProductService;

class Demo
{
    private function getPageObj(): PageService
    {
        $pageObj = app(PageService::class); // TODO DI

        // $pageObj->setTranslate($this->translate);
        // $pageObj->setContent($this->content);
        return $pageObj;
    }

    private function getMenuObj(): MenuService
    {
        $menuObj = app(MenuService::class); // TODO DI

        // $menuObj->setTranslate($this->translate);
        return $menuObj;
    }

    private function getProductObj(): ProductService
    {
        $productObj = app(ProductService::class); // TODO DI

        // $productObj->setTranslate($this->translate);
        return $productObj;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function imgUnit1(): array
    {
        $images = [
            ['name' => 'phpunittest1.jpg', 'data' => $this->getTestPhoto('test/phpunittest1.jpg'), 'alt' => ['en' => 'phpunittest1', 'pl' => 'phpunittest1']],
        ];

        return $images;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function imgUnit2(): array
    {
        $images = [
            ['name' => 'phpunittest1.jpg', 'data' => $this->getTestPhoto('test/phpunittest1.jpg'), 'alt' => ['en' => 'phpunittest1', 'pl' => 'phpunittest1']],
            ['name' => 'phpunittest2.jpg', 'data' => $this->getTestPhoto('test/phpunittest2.jpg'), 'alt' => ['en' => 'phpunittest2', 'pl' => 'phpunittest2']], // change - mistake, it was: 'pl' => 'phpunittest1'
        ];

        return $images;
    }

    /**
     * @return array<string, array<int, array<string, mixed>>>
     */
    private function getTestImg(bool $unitTest): array
    {
        if (! $unitTest) {
            $img = [
                ['name' => 'me.jpg', 'data' => $this->getTestPhoto('about_me/me.jpg'), 'alt' => ['en' => 'about me', 'pl' => 'o mnie']],
            ];
            $images = [
                ['name' => 'img1.jpg', 'data' => $this->getTestPhoto('gallery/img1.jpg'), 'alt' => ['en' => 'description img1', 'pl' => 'opis img1']],
                ['name' => 'img2.jpg', 'data' => $this->getTestPhoto('gallery/img2.jpg'), 'alt' => ['en' => 'description img2', 'pl' => 'opis img2']],
                ['name' => 'img3.jpg', 'data' => $this->getTestPhoto('gallery/img3.jpg'), 'alt' => ['en' => 'description img3', 'pl' => 'opis img3']],
                ['name' => 'img4.jpg', 'data' => $this->getTestPhoto('gallery/img4.jpg'), 'alt' => ['en' => 'description img4', 'pl' => 'opis img4']],
                ['name' => 'imgb1.jpg', 'data' => $this->getTestPhoto('gallery/imgb1.jpg'), 'alt' => ['en' => 'description imgb1', 'pl' => 'opis imgb1']],
                ['name' => 'imgb2.jpg', 'data' => $this->getTestPhoto('gallery/imgb2.jpg'), 'alt' => ['en' => 'description imgb2', 'pl' => 'opis imgb2']],
                ['name' => 'imgb3.jpg', 'data' => $this->getTestPhoto('gallery/imgb3.jpg'), 'alt' => ['en' => 'description imgb3', 'pl' => 'opis imgb3']],
                ['name' => 'imgb4.jpg', 'data' => $this->getTestPhoto('gallery/imgb4.jpg'), 'alt' => ['en' => 'description imgb4', 'pl' => 'opis imgb4']],
                ['name' => 'imgc1.jpg', 'data' => $this->getTestPhoto('gallery/imgc1.jpg'), 'alt' => ['en' => 'description imgc1', 'pl' => 'opis imgc1']],
                ['name' => 'imgc2.jpg', 'data' => $this->getTestPhoto('gallery/imgc2.jpg'), 'alt' => ['en' => 'description imgc2', 'pl' => 'opis imgc2']],
                ['name' => 'imgc3.jpg', 'data' => $this->getTestPhoto('gallery/imgc3.jpg'), 'alt' => ['en' => 'description imgc3', 'pl' => 'opis imgc3']],
                ['name' => 'imgc4.jpg', 'data' => $this->getTestPhoto('gallery/imgc4.jpg'), 'alt' => ['en' => 'description imgc4', 'pl' => 'opis imgc4']],
                ['name' => 'imgc5.jpg', 'data' => $this->getTestPhoto('gallery/imgc5.jpg'), 'alt' => ['en' => 'description imgc5', 'pl' => 'opis imgc5']],
                ['name' => 'imgc6.jpg', 'data' => $this->getTestPhoto('gallery/imgc6.jpg'), 'alt' => ['en' => 'description imgc6', 'pl' => 'opis imgc6']],
                ['name' => 'imgc7.jpg', 'data' => $this->getTestPhoto('gallery/imgc7.jpg'), 'alt' => ['en' => 'description imgc7', 'pl' => 'opis imgc7']],
                ['name' => 'imgc8.jpg', 'data' => $this->getTestPhoto('gallery/imgc8.jpg'), 'alt' => ['en' => 'description imgc8', 'pl' => 'opis imgc8']],
                ['name' => 'imgc9.jpg', 'data' => $this->getTestPhoto('gallery/imgc9.jpg'), 'alt' => ['en' => 'description imgc9', 'pl' => 'opis imgc9']],
                ['name' => 'imgc10.jpg', 'data' => $this->getTestPhoto('gallery/imgc10.jpg'), 'alt' => ['en' => 'description imgc10', 'pl' => 'opis imgc10']],
                ['name' => 'imgc11.jpg', 'data' => $this->getTestPhoto('gallery/imgc11.jpg'), 'alt' => ['en' => 'description imgc11', 'pl' => 'opis imgc11']],
                ['name' => 'imgc12.jpg', 'data' => $this->getTestPhoto('gallery/imgc12.jpg'), 'alt' => ['en' => 'description imgc12', 'pl' => 'opis imgc12']],
            ];

            $imagesGrec = [
                ['name' => 'imggreece1.jpg', 'data' => $this->getTestPhoto('gallery/greece/imggreece1.jpg'), 'alt' => ['en' => 'description imggreece1', 'pl' => 'opis imggreece1']],
                ['name' => 'imggreece2.jpg', 'data' => $this->getTestPhoto('gallery/greece/imggreece2.jpg'), 'alt' => ['en' => 'description imggreece2', 'pl' => 'opis imggreece2']],
            ];

            /*
            $imagesSlider = [
                ['name' => 's1.png', 'data' => $this->getTestPhoto('slider/s1.png'), 'alt' => [ "en" =>'slider1', "pl" =>'pl_slider1'  ]],
                ['name' => 's2.png', 'data' => $this->getTestPhoto('slider/s2.png'), 'alt' => [ "en" =>'slider2', "pl" =>'pl_slider2'  ]],
                ['name' => 's3.png', 'data' => $this->getTestPhoto('slider/s3.png'), 'alt' => [ "en" =>'slider3', "pl" =>'pl_slider3'  ]],
                ['name' => 's4.png', 'data' => $this->getTestPhoto('slider/s4.png'), 'alt' => [ "en" =>'slider4', "pl" =>'pl_slider4'  ]],
                ['name' => 's5.png', 'data' => $this->getTestPhoto('slider/s5.png'), 'alt' => [ "en" =>'slider5', "pl" =>'pl_slider5'  ]],
                ['name' => 's6.png', 'data' => $this->getTestPhoto('slider/s6.png'), 'alt' => [ "en" =>'slider6', "pl" =>'pl_slider6'  ]],
                ['name' => 's7.png', 'data' => $this->getTestPhoto('slider/s7.png'), 'alt' => [ "en" =>'slider7', "pl" =>'pl_slider7'  ]],
                ['name' => 's8.png', 'data' => $this->getTestPhoto('slider/s8.png'), 'alt' => [ "en" =>'slider8', "pl" =>'pl_slider8'  ]],
            ];
            */

            $imagesSlider = [
                ['name' => 's1.png', 'data' => $this->getTestPhoto('clothes/sox.jpg'), 'alt' => ['en' => 'slider1', 'pl' => 'pl_slider1']],
                // ['name' => 's2.png', 'data' => $this->getTestPhoto('books/php5.jpg'), 'alt' => ['en' => 'slider2', 'pl' => 'pl_slider2']],
                ['name' => 's3.png', 'data' => $this->getTestPhoto('books/java.jpg'), 'alt' => ['en' => 'slider2', 'pl' => 'pl_slider2']],
            ];

        } else {
            $img = $this->imgUnit1();
            $images = $this->imgUnit2();
            $imagesGrec = $images;
            $imagesSlider = $this->imgUnit2();
        }

        return ['about_me' => $img, 'poland' => $images, 'greece' => $imagesGrec, 'slider' => $imagesSlider];
    }

    /**
     * @return array<string, array<int|string, array<string, mixed>|string>>
     */
    private function getProductImg(bool $unitTest): array
    {
        $out = [];
        if (! $unitTest) {
            $out = [
                'product1' => [
                    ['name' => 'php.jpg', 'data' => $this->getTestPhoto('books/php3.jpg'), 'alt' => ['en' => 'php3 front', 'pl' => 'php3 front']],
                ],
                'product2' => [
                    ['name' => 'php5.jpg', 'data' => $this->getTestPhoto('books/php5.jpg'), 'alt' => ['en' => 'php5 front', 'pl' => 'php5 front']],
                    ['name' => 'php5_back.jpg', 'data' => $this->getTestPhoto('books/php5_back.jpg'), 'alt' => ['en' => 'php5 back', 'pl' => 'php5 back']],
                ],
                'product3' => [
                    ['name' => 'java.jpg', 'data' => $this->getTestPhoto('books/java.jpg'), 'alt' => ['en' => 'java front', 'pl' => 'java front']],
                    ['name' => 'java_back.jpg', 'data' => $this->getTestPhoto('books/java_back.jpg'), 'alt' => ['en' => 'java back', 'pl' => 'java back']],
                ],
                'product4' => [
                    ['name' => 'english.jpg', 'data' => $this->getTestPhoto('books/english.jpg'), 'alt' => ['en' => 'english front', 'pl' => 'english front']],
                    ['name' => 'english_back.jpg', 'data' => $this->getTestPhoto('books/english_back.jpg'), 'alt' => ['en' => 'english back', 'pl' => 'english back']],
                ],

                'product5' => [
                    ['name' => 'eng1.jpg', 'data' => $this->getTestPhoto('books/eng1.jpg'), 'alt' => ['en' => 'english1 front', 'pl' => 'english1 front']],
                ],
                'product6' => [
                    ['name' => 'eng2.jpg', 'data' => $this->getTestPhoto('books/eng2.jpg'), 'alt' => ['en' => 'english2 front', 'pl' => 'english2 front']],
                ],
                'product7' => [
                    ['name' => 'eng3.jpg', 'data' => $this->getTestPhoto('books/eng3.jpg'), 'alt' => ['en' => 'english3 front', 'pl' => 'english3 front']],
                ],
                'product8' => [
                    ['name' => 'eng4.jpg', 'data' => $this->getTestPhoto('books/eng4.jpg'), 'alt' => ['en' => 'english4 front', 'pl' => 'english4 front']],
                ],
                'product9' => [
                    ['name' => 'eng5.jpg', 'data' => $this->getTestPhoto('books/eng5.jpg'), 'alt' => ['en' => 'english5 front', 'pl' => 'english5 front']],
                ],

                'product10' => [
                    ['name' => 'eng5.jpg', 'data' => $this->getTestPhoto('books/php5_frameworks.jpg'), 'alt' => ['en' => 'php5 frameworks', 'pl' => 'php5 frameworks']],
                ],

                'product11' => [
                    ['name' => 'pm.jpg', 'data' => $this->getTestPhoto('books/pm.jpg'), 'alt' => ['en' => 'programme manager', 'pl' => 'kierownik programistów']],
                ],

                'product12' => [
                    ['name' => 'sox.jpg', 'data' => $this->getTestPhoto('clothes/sox.jpg'), 'alt' => ['en' => 'sox', 'pl' => 'skarpety']],
                ],

            ];
        } else {
            $out = [
                'product1' => $this->imgUnit1(),
                'product2' => $this->imgUnit2(),
                'product3' => $this->imgUnit2(),
                'product4' => $this->imgUnit2(),
                'product5' => $this->imgUnit2(),
                'product6' => $this->imgUnit2(),
                'product7' => $this->imgUnit2(),
                'product8' => $this->imgUnit2(),
                'product9' => $this->imgUnit2(),
                'product10' => $this->imgUnit2(),
                'product11' => $this->imgUnit2(),
                'product12' => $this->imgUnit2(),
            ];
        }

        return $out;
    }

    /**
     * @return array<string, Page>
     */
    public function pagesAndMenu(bool $unitTest = false): array
    {
        $images = $this->getTestImg($unitTest);

        $p = [];
        // $appUrl = env('APP_URL');
        // $admUrl = (strpos($appUrl, 'demo.cmsrs.pl') !== false) ? $appUrl.'/admin-demo' : $appUrl.'/admin/';
        // $admUrl = env('DEMO_STATUS', false) ? '/admin-demo' : '/admin/';
        $admUrl = config('cmsrs.demo') ? '/admin-demo' : '/admin/';

        $pInner1 = [
            'title' => ['en' => 'Box1 test en', 'pl' => 'Box1 test pl'],
            'short_title' => ['en' => 'main_page_box1', 'pl' => 'inner short_title pl'],
            'content' => ['en' => $this->getDummyTest(),  'pl' => $this->getDummyTestPl()],
            'published' => 1,
            'type' => 'inner',
        ];
        $p17 = $this->getPageObj()->wrapCreate($pInner1);
        $p['p17'] = $p17;

        $pInner2 = [
            'title' => ['en' => 'Box2 test en', 'pl' => 'Box2 test pl'],
            'short_title' => ['en' => 'main_page_box2', 'pl' => 'inner short_title pl'],
            'content' => ['en' => $this->getDummyTest(),  'pl' => $this->getDummyTestPl()],
            'published' => 1,
            'type' => 'inner',
        ];
        $p18 = $this->getPageObj()->wrapCreate($pInner2);
        $p['p18'] = $p18;

        $pInner3 = [
            'title' => ['en' => 'Box3 test en', 'pl' => 'Box3 test pl'],
            'short_title' => ['en' => 'main_page_box3', 'pl' => 'inner short_title pl'],
            'content' => ['en' => $this->getDummyTest(),  'pl' => $this->getDummyTestPl()],
            'published' => 1,
            'type' => 'inner',
        ];
        $p19 = $this->getPageObj()->wrapCreate($pInner3);
        $p['p19'] = $p19;

        $pInner200 = [
            'title' => ['en' => 'Company data', 'pl' => 'Dane firmy'],
            'short_title' => ['en' => 'company_data', 'pl' => 'company_data'],
            'content' => ['en' => '<b>Company Data:</b><br><br> ul. Kolejowa 1/1000<br>00-123 Warsaw<br>Poland',  'pl' => '<b>Siedziba firmy:</b><br><br> ul. Kolejowa 1/1000<br>00-123 Warszawa<br>Polska'],
            'published' => 1,
            'type' => 'inner',
        ];
        $p200 = $this->getPageObj()->wrapCreate($pInner200);
        $p['p200'] = $p200;

        $pSlider = [
            'title' => ['en' => 'Slider', 'pl' => 'Slider pl'],
            'short_title' => ['en' => 'main_page_slider', 'pl' => 'main_page_slider'],
            // 'content' => [ "en" =>,  "pl" =>$this->getDummyTestPl()],
            'published' => 1,
            // 'type' => 'slider_main',
            'type' => 'inner',
            'images' => $images['slider'],
        ];
        $p100 = $this->getPageObj()->wrapCreate($pSlider);
        $p['p100'] = $p100;

        // <div class='row'>
        //     <div class='slidecontainer m-4'></div>
        // </div>
        $mainPage =
        [
            'title' => ['en' => 'cmsRS demo site', 'pl' => 'cmsRS wersja demo'],
            'short_title' => ['en' => 'cmsRS demo', 'pl' => 'cmsRS demo'],
            'description' => ['en' => 'cmsRS demo site - description', 'pl' => 'cmsRS wersja demo'],
            'published' => 1,
            'commented' => 0,
            'after_login' => 0,
            'type' => 'main_page', // !!
            'content' => ['en' => "
            <div class='container pt-5 starter-template  mt-4 mb-4'>
                <h1>cmsRS demo version</h1>

                <form  class='mt-4 mb-4' action='/en/search' method='get' >
                    <div class='mt-4 mb-4 input-group'>
                        <input type='search' name='key' value='' class='form-control rounded' placeholder='Search products' aria-label='Search'
                        aria-describedby='search-addon' />
                        <button type='submit' class='btn btn-outline-primary'>Search</button>
                    </div>                
                </form>
                


                    <div class='alert alert-danger' role='alert'>
                     This demo version was created solely for demonstration purposes. All products in the store are test products, and the store does not sell real products.
                    </div>

                    <br><br>

                <p class='lead'>
                    Admin panel: <a href=\"$admUrl\">$admUrl</a>
                    <br>
                    <br>
                    admin: adm@cmsrs.pl
                    <br>
                    pass: cmsrs123
                    <br>

                    <br>
                    More information: <a title='cmsRS' href='https://www.cmsrs.pl' >https://www.cmsrs.pl</a>
                </p>
            </div> 
            
            <div class='mt-5 container'><!-- container -->      
            
          </div><!-- container -->

            ", 'pl' => "
            <div class='container pt-5 starter-template  mt-4 mb-4'>
                <h1>cmsRS wersja demo</h1>

                <form  class='mt-4 mb-4' action='/pl/search' method='get' >
                    <div class='mt-4 mb-4 input-group'>
                        <input type='search' name='key' value='' class='form-control rounded' placeholder='Wyszukiwarka produktów' aria-label='Search'
                        aria-describedby='search-addon' />
                        <button type='submit' class='btn btn-outline-primary'>Szukaj</button>
                    </div>                
                </form>




                    <div class='alert alert-danger' role='alert'>
                    Ta wersja demo została stworzona wyłącznie w celach demonstracyjnych. Wszystkie produkty w sklepie są produktami testowymi, a sklep nie prowadzi sprzedaży rzeczywistych produktów.
                    </div>

                    <br><br>

                <p class='lead'>
                    Panel administracyjny: <a href=\"$admUrl\">$admUrl</a>
                    <br>
                    <br>
                    admin: adm@cmsrs.pl
                    <br>
                    hasło: cmsrs123
                    <br>

                    <br>
                    Więcej informacji: <a title='cmsRS' href='https://www.cmsrs.pl' >https://www.cmsrs.pl</a>
                </p>
            </div>


            <div class='mt-5 container'><!-- container -->    

            
          </div><!-- container -->


            "],
            'menu_id' => null,
            'page_id' => null,
            // 'images' => []
        ];

        $p['p1'] = $this->getPageObj()->wrapCreate($mainPage);
        // Page::wrapCreate($mainPage);

        $m1 = $this->getMenuObj()->wrapCreate(['name' => ['en' => 'About', 'pl' => 'O mnie']]);

        $data1p = [
            'title' => ['en' => 'About me', 'pl' => 'O mnie'],
            'short_title' => ['en' => 'About me', 'pl' => 'O mnie'],
            'description' => ['en' => 'Description... Needed for google', 'pl' => 'Opis..... Potrzebne dla googla'],
            'published' => 1,
            'commented' => 0,
            'type' => 'cms',
            'content' => ['en' => $this->getDummyTest(), 'pl' => $this->getDummyTestPl()],
            'menu_id' => $m1->id,
            'images' => $images['about_me'],
        ];

        $data2p = [
            'title' => ['en' => 'About page', 'pl' => 'O stronie'],
            'short_title' => ['en' => 'About page', 'pl' => 'O stronie'],
            'description' => ['en' => 'Description... Needed for google', 'pl' => 'Opis..... Potrzebne dla googla'],
            'published' => 1,
            'commented' => 1,
            'type' => 'cms',
            'content' => ['en' => $this->getDummyTest(), 'pl' => $this->getDummyTestPl()],
            'menu_id' => $m1->id,
        ];

        $data2pUnpublished = [
            'title' => ['en' => 'Something page', 'pl' => 'Jakaś strona'],
            'short_title' => ['en' => 'Sth page', 'pl' => 'Strona'],
            'description' => ['en' => 'Description... Needed for google', 'pl' => 'Opis..... Potrzebne dla googla'],
            'published' => 0,
            'commented' => 0,
            'type' => 'cms',
            'content' => ['en' => $this->getDummyTest(), 'pl' => $this->getDummyTestPl()],
            'menu_id' => $m1->id,
        ];

        $data22pSecret = [
            'title' => ['en' => 'Secret info', 'pl' => 'Tajna informacja'],
            'short_title' => ['en' => 'Secret info', 'pl' => 'Tajna informacja'],
            'description' => ['en' => 'Description... Needed for google', 'pl' => 'Opis..... Potrzebne dla googla'],
            'published' => 1,
            'commented' => 0,
            'after_login' => 1,
            'type' => 'cms',
            'content' => ['en' => 'Message available after log in.', 'pl' => 'Widomość dostępna tylko po zalogowaniu.'],
            'menu_id' => $m1->id,
        ];

        $p['p222'] = $this->getPageObj()->wrapCreate($data1p);
        $p['p2'] = $this->getPageObj()->wrapCreate($data2p);
        $p['p2222'] = $this->getPageObj()->wrapCreate($data2pUnpublished);
        $p['p22'] = $this->getPageObj()->wrapCreate($data22pSecret);

        $m2 = $this->getMenuObj()->wrapCreate(['name' => ['en' => 'Gallery', 'pl' => 'Galeria']]);
        $data3p = [
            'title' => ['en' => 'Poland', 'pl' => 'Polska'],
            'short_title' => ['en' => 'Poland', 'pl' => 'Polska'],
            'description' => ['en' => 'Description...  needed for google', 'pl' => 'Opis..... Potrzebne dla googla'],
            'published' => 1,
            'commented' => 0,
            'type' => 'gallery',
            'content' => ['en' => 'Below, I present photos from Poland.', 'pl' => 'Ponizej przedstwaiam zdjęcia z Polski'],
            'menu_id' => $m2->id,
            'images' => $images['poland'],
        ];
        $p['p3'] = $this->getPageObj()->wrapCreate($data3p);

        $data44p = [
            'title' => ['en' => 'Greece', 'pl' => 'Grecja'],
            'short_title' => ['en' => 'Greece', 'pl' => 'Grecja'],
            'description' => ['en' => 'Description...  needed for google', 'pl' => 'Opis..... Potrzebne dla googla'],
            'published' => 1,
            'commented' => 0,
            'type' => 'gallery',
            'content' => '',
            'menu_id' => $m2->id,
            'images' => $images['greece'],
        ];
        $p['p44'] = $this->getPageObj()->wrapCreate($data44p);

        $m3 = $this->getMenuObj()->wrapCreate(['name' => ['en' => 'Books', 'pl' => 'Książki']]);
        $data4p = [
            'title' => ['en' => 'IT books', 'pl' => 'Książki IT'],
            'short_title' => ['en' => 'IT books', 'pl' => 'Książki IT'],
            'description' => ['en' => 'Description... Needed for google', 'pl' => 'Opis..... Potrzebne dla googla'],
            'published' => 1,
            'commented' => 0,
            'type' => 'shop',
            // 'content' => [ "en" =>$this->getDummyTest(),  "pl" =>$this->getDummyTestPl()],
            'menu_id' => $m3->id,
        ];
        $p['p4'] = $this->getPageObj()->wrapCreate($data4p);

        $data5p = [
            'title' => ['en' => 'PHP', 'pl' => 'PHP'],
            'short_title' => ['en' => 'PHP',  'pl' => 'PHP'],
            'description' => ['en' => 'Description... Needed for google', 'pl' => 'Opis..... Potrzebne dla googla'],
            'published' => 1,
            'commented' => 0,
            'type' => 'shop',
            'content' => '',
            'page_id' => $p['p4']->id,
            'menu_id' => $m3->id,
        ];
        $p['p5'] = $this->getPageObj()->wrapCreate($data5p);

        $data6p = [
            'title' => ['en' => 'Java', 'pl' => 'Java'],
            'short_title' => ['en' => 'Java', 'pl' => 'Java'],
            'description' => ['en' => 'Description... Needed for google', 'pl' => 'Opis..... Potrzebne dla googla'],
            'published' => 1,
            'commented' => 0,
            'type' => 'shop',
            'content' => '',
            'page_id' => $p['p4']->id,
            'menu_id' => $m3->id,
        ];
        $p['p6'] = $this->getPageObj()->wrapCreate($data6p);

        $data7p = [
            'title' => ['en' => 'English', 'pl' => 'Angielski'],
            'short_title' => ['en' => 'English', 'pl' => 'Angielski'],
            'description' => ['en' => 'Description... Needed for google', 'pl' => 'Opis..... Potrzebne dla googla'],
            'published' => 1,
            'commented' => 0,
            'type' => 'shop',
            'content' => '',
            'menu_id' => $m3->id,
        ];
        $p['p7'] = $this->getPageObj()->wrapCreate($data7p);

        $m4 = $this->getMenuObj()->wrapCreate(['name' => ['en' => 'Clothes', 'pl' => 'Ubrania']]);
        $dataM4a = [
            'title' => ['en' => 'Man', 'pl' => 'Mężczyzna'],
            'short_title' => ['en' => 'Man', 'pl' => 'Mężczyzna'],
            'description' => ['en' => 'Description... Needed for google', 'pl' => 'Opis..... Potrzebne dla googla'],
            'published' => 1,
            'commented' => 0,
            'type' => 'cms',
            'content' => ['en' => $this->getDummyTest(),  'pl' => $this->getDummyTestPl()],
            'menu_id' => $m4->id,
        ];
        // kobieta
        // woman
        $p['p8'] = $this->getPageObj()->wrapCreate($dataM4a);

        $data8p = [
            'title' => ['en' => 'Sox', 'pl' => 'Skarpery'],
            'short_title' => ['en' => 'Sox', 'pl' => 'Skarpery'],
            'description' => ['en' => 'Description... Needed for google', 'pl' => 'Opis..... Potrzebne dla googla'],
            'published' => 1,
            'commented' => 0,
            'type' => 'shop',
            'content' => '',
            'page_id' => $p['p8']->id,
            'menu_id' => $m4->id,
        ];
        $p['p8b'] = $this->getPageObj()->wrapCreate($data8p);

        $dataM4b = [
            'title' => ['en' => 'Woman', 'pl' => 'Kobieta'],
            'short_title' => ['en' => 'Woman', 'pl' => 'Kobieta'],
            'description' => ['en' => 'Description... Needed for google', 'pl' => 'Opis..... Potrzebne dla googla'],
            'published' => 1,
            'commented' => 0,
            'type' => 'cms',
            'content' => ['en' => $this->getDummyTest(),  'pl' => $this->getDummyTestPl()],
            'menu_id' => $m4->id,
        ];
        $p['p8c'] = $this->getPageObj()->wrapCreate($dataM4b);

        $mContact = $this->getMenuObj()->wrapCreate(['name' => ['en' => 'Contact me', 'pl' => 'Kontakt']]);

        $pContact = [
            'title' => ['en' => 'Contact', 'pl' => 'Kontakt'],
            'short_title' => ['en' => 'Contact', 'pl' => 'Kontakt'],
            'description' => ['en' => 'Description... Needed for google', 'pl' => 'Opis..... Potrzebne dla googla'],
            'published' => 1,
            'commented' => 0,
            'type' => 'contact',
            'content' => '',
            'menu_id' => $mContact->id,
            'images' => [
            ],
        ];
        $p['pContact'] = $this->getPageObj()->wrapCreate($pContact);

        $pPrivacy = [
            'title' => ['en' => 'Privacy policy', 'pl' => 'Polityka prywatności'],
            'short_title' => ['en' => 'Privacy policy', 'pl' => 'Polityka prywatności'],
            'description' => ['en' => 'Description... Needed for google', 'pl' => 'Opis..... Potrzebne dla googla'],
            'published' => 1,
            'commented' => 0,
            'type' => 'privacy_policy',
            'content' => ['en' => $this->getDummyTest(),  'pl' => $this->getDummyTestPl()], // ['en' => $this->getPrivacyPolicy(), 'pl' => $this->getPrivacyPolicy()],
            'images' => [
            ],
        ];
        $p['p9'] = $this->getPageObj()->wrapCreate($pPrivacy);

        // $pLogin = [
        //     'title' => ['en' => 'Login', 'pl' => 'Logowanie'],
        //     'short_title' => ['en' => 'Login', 'pl' => 'Logowanie'],
        //     'description' => ['en' => 'Description... Needed for google', 'pl' => 'Opis..... Potrzebne dla googla'],
        //     'published' => 1,
        //     'commented' => 0,
        //     'type' => 'login',
        //     //'content' => [ "en" => $this->getPrivacyPolicy(), "pl" => $this->getPrivacyPolicy() ],
        //     'images' => [
        //     ],
        // ];
        // $p['p10'] = $this->getPageObj()->wrapCreate($pLogin);

        // $pRegister = [
        //     'title' => ['en' => 'Register', 'pl' => 'Rejestracja'],
        //     'short_title' => ['en' => 'Register', 'pl' => 'Rejestracja'],
        //     'description' => ['en' => 'Description... Needed for google', 'pl' => 'Opis..... Potrzebne dla googla'],
        //     'published' => 1,
        //     'commented' => 0,
        //     'type' => 'register',
        //     //'content' => [ "en" => $this->getPrivacyPolicy(), "pl" => $this->getPrivacyPolicy() ],
        //     'images' => [
        //     ],
        // ];
        // $p['p11'] = $this->getPageObj()->wrapCreate($pRegister);

        // $pCheckout = [
        //     'title' => ['en' => 'Checkout', 'pl' => 'Kasa'],
        //     'short_title' => ['en' => 'Checkout', 'pl' => 'Kasa'],
        //     'description' => ['en' => 'Description... Needed for google', 'pl' => 'Opis..... Potrzebne dla googla'],
        //     'published' => 1,
        //     'commented' => 0,
        //     'type' => 'checkout',
        //     // 'content' => [ "en" => $this->getPrivacyPolicy(), "pl" => $this->getPrivacyPolicy() ],
        //     'images' => [
        //     ],
        // ];
        // $p['p12'] = $this->getPageObj()->wrapCreate($pCheckout);

        // $pHome = [
        //     'title' => ['en' => 'Home', 'pl' => 'Pulpit'],
        //     'short_title' => ['en' => 'Home', 'pl' => 'Pulpit'],
        //     'description' => ['en' => 'Description... Needed for google', 'pl' => 'Opis..... Potrzebne dla googla'],
        //     'published' => 1,
        //     'after_login' => 1, // we don't need this page in sitmap
        //     'commented' => 0,
        //     'type' => 'home',
        //     // 'content' => [ "en" => $this->getPrivacyPolicy(), "pl" => $this->getPrivacyPolicy() ],
        //     'images' => [
        //     ],
        // ];
        // $p['p13'] = $this->getPageObj()->wrapCreate($pHome);

        // $pShoppingSuccess = [
        //     'title' => ['en' => 'Shopping Success', 'pl' => 'Twoje zakupy'],
        //     'short_title' => ['en' => 'Shopping Success', 'pl' => 'Twoje zakupy'],
        //     'description' => ['en' => 'Description... Needed for google', 'pl' => 'Opis..... Potrzebne dla googla'],
        //     'published' => 1,
        //     'after_login' => 1, // we don't need this page in sitmap
        //     'commented' => 0,
        //     'type' => 'shoppingsuccess',
        //     // 'content' => [ "en" => $this->getPrivacyPolicy(), "pl" => $this->getPrivacyPolicy() ],
        //     'images' => [
        //     ],
        // ];
        // $p['p14'] = $this->getPageObj()->wrapCreate($pShoppingSuccess);

        // $pSearch = [
        //     'title' => ['en' => 'Search product', 'pl' => 'Wyszukiwarka produktów'],
        //     'short_title' => ['en' => 'Search product', 'pl' => 'Wyszukiwarka produktów'],
        //     'description' => ['en' => 'Description... Needed for google', 'pl' => 'Opis..... Potrzebne dla googla'],
        //     'published' => 1,
        //     'commented' => 0,
        //     'type' => 'search',
        //     // 'content' => [ "en" => $this->getPrivacyPolicy(), "pl" => $this->getPrivacyPolicy() ],
        //     'images' => [
        //     ],
        // ];
        // $p['p15'] = $this->getPageObj()->wrapCreate($pSearch);

        // $pForgot = [
        //     'title' => ['en' => 'Forgot password', 'pl' => 'Przypomnij hasło'],
        //     'short_title' => ['en' => 'Forgot password', 'pl' => 'Przypomnij hasło'],
        //     'description' => ['en' => 'Description... Needed for google', 'pl' => 'Opis..... Potrzebne dla googla'],
        //     'published' => 1,
        //     'commented' => 0,
        //     'type' => 'forgot',
        //     //'content' => [ "en" => $this->getPrivacyPolicy(), "pl" => $this->getPrivacyPolicy() ],
        //     'images' => [
        //     ],
        // ];
        // $p['p16'] = $this->getPageObj()->wrapCreate($pForgot);

        return $p;
    }

    /**
     * @param  array<string, Page>  $p
     * @return array<int, Product>
     */
    public function product(array $p, bool $unitTest = false): array
    {
        $images = $this->getProductImg($unitTest);

        $products = [];
        $products['product1'] = [
            'product_name' => ['en' => 'PHP3 db app', 'pl' => 'PHP3 aplikacje bazodanowe'],
            'sku' => '1/23/4',
            'price' => 1100,
            'published' => 1,
            'product_description' => ['en' => 'Php3 book description',   'pl' => 'Php3 opis ksiazki'],
            'page_id' => $p['p5']->getId(),
            'images' => $images['product1'],
        ];
        $products['product2'] = [
            'product_name' => ['en' => 'PHP5', 'pl' => 'PHP5'],
            'sku' => '1/234/4',
            'price' => 3000,
            'published' => 0,
            'product_description' => ['en' => 'Php5 book', 'pl' => 'Ksiazka o PHP5'],
            'page_id' => $p['p5']->getId(),
            'images' => $images['product2'],
        ];
        $products['product3'] = [
            'product_name' => ['en' => 'Java db app', 'pl' => 'Java aplikacje bazodanowe'],
            'sku' => '3/13/4',
            'price' => 2300,
            'published' => 1,
            'product_description' => ['en' => 'Java book', 'pl' => 'Książka o Javie'],
            'page_id' => $p['p6']->getId(),
            'images' => $images['product3'],
        ];
        $products['product4'] = [
            'product_name' => ['en' => 'English book',  'pl' => 'Książka angielski'],
            'sku' => 'A/3/3/4',
            'price' => 2800,
            'published' => 1,
            'product_description' => ['en' => 'English book', 'pl' => 'Ksiązka do nauki angielskiego'],
            'page_id' => $p['p7']->getId(),
            'images' => $images['product4'],
        ];

        $products['product5'] = [
            'product_name' => ['en' => 'eng1',  'pl' => 'ang1'],
            'sku' => 'A/1',
            'price' => 1100,
            'published' => 1,
            'product_description' => ['en' => 'English book', 'pl' => 'Ksiązka do nauki angielskiego'],
            'page_id' => $p['p7']->getId(),
            'images' => $images['product5'],
        ];

        $products['product6'] = [
            'product_name' => ['en' => 'eng2',  'pl' => 'ang2'],
            'sku' => 'A/2',
            'price' => 2200,
            'published' => 1,
            'product_description' => ['en' => 'English book', 'pl' => 'Ksiązka do nauki angielskiego'],
            'page_id' => $p['p7']->getId(),
            'images' => $images['product6'],
        ];
        $products['product7'] = [
            'product_name' => ['en' => 'eng3',  'pl' => 'ang3'],
            'sku' => 'A/3',
            'price' => 3300,
            'published' => 1,
            'product_description' => ['en' => 'English book', 'pl' => 'Ksiązka do nauki angielskiego'],
            'page_id' => $p['p7']->getId(),
            'images' => $images['product7'],
        ];
        $products['product8'] = [
            'product_name' => ['en' => 'eng4',  'pl' => 'ang4'],
            'sku' => 'A/4',
            'price' => 4400,
            'published' => 1,
            'product_description' => ['en' => 'English book', 'pl' => 'Ksiązka do nauki angielskiego'],
            'page_id' => $p['p7']->getId(),
            'images' => $images['product8'],
        ];
        $products['product9'] = [
            'product_name' => ['en' => 'eng5',  'pl' => 'ang5'],
            'sku' => 'A/5',
            'price' => 5500,
            'published' => 1,
            'product_description' => ['en' => 'English book', 'pl' => 'Ksiązka do nauki angielskiego'],
            'page_id' => $p['p7']->getId(),
            'images' => $images['product9'],
        ];

        $products['product10'] = [
            'product_name' => ['en' => 'PHP5 frameworks', 'pl' => 'PHP5 frameworks'],
            'sku' => '1/234/778',
            'price' => 56000,
            'published' => 1,
            'product_description' => ['en' => 'Php5 book frameworks', 'pl' => 'Ksiazka o PHP5 frameworks'],
            'page_id' => $p['p5']->getId(),
            'images' => $images['product10'],
        ];

        $products['product11'] = [
            'product_name' => ['en' => 'programme managers', 'pl' => 'kierownik programistów'],
            'sku' => '1/234/18',
            'price' => 5700,
            'published' => 1,
            'product_description' => ['en' => 'programme managers', 'pl' => 'kierownik programistów'],
            'page_id' => $p['p4']->getId(), // $p['p4']
            'images' => $images['product11'],
        ];

        $products['product12'] = [
            'product_name' => ['en' => 'sportline socks', 'pl' => 'skarpety sportowe'],
            'sku' => '1/234/100',
            'price' => 500,
            'published' => 1,
            'product_description' => ['en' => 'socks', 'pl' => 'skarpety'],
            'page_id' => $p['p8b']->getId(), // $p['p4']
            'images' => $images['product12'],
        ];

        $out = [];
        $out[] = $this->getProductObj()->wrapCreate($products['product1']);
        $out[] = $this->getProductObj()->wrapCreate($products['product2']);
        $out[] = $this->getProductObj()->wrapCreate($products['product3']);
        $out[] = $this->getProductObj()->wrapCreate($products['product4']);

        $out[] = $this->getProductObj()->wrapCreate($products['product5']);
        $out[] = $this->getProductObj()->wrapCreate($products['product6']);
        $out[] = $this->getProductObj()->wrapCreate($products['product7']);
        $out[] = $this->getProductObj()->wrapCreate($products['product8']);
        $out[] = $this->getProductObj()->wrapCreate($products['product9']);

        $out[] = $this->getProductObj()->wrapCreate($products['product10']);
        $out[] = $this->getProductObj()->wrapCreate($products['product11']);
        $out[] = $this->getProductObj()->wrapCreate($products['product12']);

        return $out;
        // return $products;
    }

    private function getTestPhoto(string $imgPath): string
    {
        $base = base_path().'/rs/demo_data/img/';
        $img = $base.$imgPath;
        if (! file_exists($img)) {
            exit('no file: '.$img);
        }

        $data = file_get_contents($img);
        if ($data === false) {
            exit('cannot read file: '.$img);
        }

        $type = pathinfo($img, PATHINFO_EXTENSION);

        $base64 = 'data:image/'.$type.';base64,'.base64_encode($data);

        return $base64;
    }

    private function getDummyTest(): string
    {
        return "<b>Lorem Ipsum</b> is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum.";
    }

    private function getDummyTestPl(): string
    {
        return '<b>Lorem Ipsum</b> jest tekstem stosowanym jako przykładowy wypełniacz w przemyśle poligraficznym. Został po raz pierwszy użyty w XV w. przez nieznanego drukarza do wypełnienia tekstem próbnej książki. Pięć wieków później zaczął być używany przemyśle elektronicznym, pozostając praktycznie niezmienionym. Spopularyzował się w latach 60. XX w. wraz z publikacją arkuszy Letrasetu, zawierających fragmenty Lorem Ipsum, a ostatnio z zawierającym różne wersje Lorem Ipsum oprogramowaniem przeznaczonym do realizacji druków na komputerach osobistych, jak Aldus PageMaker';
    }

    // private function getPrivacyPolicy()
    // {
    //     return '<p>POLITYKA COOKIES<br /> <br />Zgodnie z wymaganiami dotyczącymi serwisów internetowych, informuje Państwa, że dla zapewnienia lepszego działania serwisu używam mechanizmu plików cookies.<br /><br />1. Pliki cookies (tzw. "ciasteczka”) stanowią dane informatyczne, w szczególności pliki tekstowe, które są zapisywane i przechowywane w urządzeniu końcowym Użytkownika Serwisu ( na komputerze, smartfonie, tablecie itp.) i przeznaczone są do korzystania ze stron internetowych Serwisu. Cookies zazwyczaj zawierają nazwę strony internetowej, z której pochodzą, czas przechowywania plików cookies na urządzeniu końcowym oraz unikalny numer, służący do identyfikacji przeglądarki, z jakiej następuje połączenie ze stroną internetową.<br /> <br />2.Pliki cookies wykorzystywane są w celu:<br /><br />a) dostosowania zawartości stron internetowych Serwisu do preferencji Użytkownika oraz optymalizacji korzystania ze stron internetowych; w szczególności pliki te pozwalają rozpoznać urządzenie końcowe Użytkownika Serwisu i odpowiednio wyświetlić stronę internetową, dostosowaną do jego indywidualnych potrzeb;<br /><br />b) tworzenia statystyk, które pomagają zrozumieć, w jaki sposób Użytkownicy Serwisu korzystają ze stron internetowych, co umożliwia ulepszanie ich struktury i zawartości;<br /> <br />c) utrzymanie sesji Użytkownika Serwisu (po zalogowaniu), dzięki której Użytkownik nie musi na każdej podstronie Serwisu ponownie wpisywać loginu i hasła (o ile funkcja logowań ie jest dostępna w Serwisie).<br />  <br />3. W ramach Serwisu stosowane są dwa zasadnicze rodzaje plików cookies:<br /> <br />a) "sesyjne" (session cookies), które są plikami tymczasowymi, które przechowywane są w urządzeniu końcowym Użytkownika do czasu wylogowania, opuszczenia strony internetowej lub wyłączenia oprogramowania (przeglądarki internetowej) i które są niezbędne do działania Serwisu oraz korzystania z usług dostępnych w ramach Serwisu;<br /> <br />b) "stałe" (persistent cookies),które przechowywane są w urządzeniu końcowym Użytkownika przez czas określony w parametrach plików cookies lub do czasu ich usunięcia przez Użytkownika.<br /> <br />4. W ramach Serwisu pliki cookies mogą być wykorzystywane w celu:<br /> <br />a) zbierania informacji o sposobie korzystania przez Użytkownika ze stron internetowych Serwisu (np.: informacji na temat obszarów, które odwiedza Użytkownik, czasu jaki na nich spędza oraz problemów jakie na nich napotyka), co pozwala poprawiać działanie stron internetowych Serwisu,<br /> <br />b) zapamiętania wybranych przez Użytkownika ustawień by zapewnić personalizację interfejsu Użytkownika (np. w zakresie wybranego języka lub regionu, z którego pochodzi Użytkownik, rozmiaru czcionki, wyglądu strony internetowej) oraz by dostarczyć Użytkownikowi bardziej spersonalizowane treści i usługi;<br /> <br />5. W wielu przypadkach przeglądark i internetowe domyślnie dopuszczają przechowywanie plików cookies w urządzeniu końcowym Użytkownika. Użytkownicy Serwisu mogą dokonać w każdym czasie zmiany ustawień dotyczących plików cookies w swoich przeglądarkach internetowych. Zmiana ustawień może w szczególności polegać na blokowaniu automatycznej obsługi plików cookies bądź na informowaniu o każdorazowym zamieszczeniu plików cookies w urządzeniu końcowym Użytkownika Serwisu. Szczegółowe informacje o możliwości i sposobach obsługi plików cookies dostępne są w ustawieniach przeglądarki internetowej.<br /> <br />6. Informuje, że ograniczenia stosowania plików cookies mogą wpłynąć na niektóre funkcjonalności dostępne na stronach internetowych Serwisu, co może negatywnie wpłynąć na wygodę korzystania z Serwisu lub doprowadzić do zablokowania niektórych funkcjonalności.</p>';
    // }
}
