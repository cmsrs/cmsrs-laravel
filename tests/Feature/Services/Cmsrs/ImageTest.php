<?php

namespace Tests\Feature\Services\Cmsrs;

use App\Models\Cmsrs\Cms\Page;
use App\Models\Cmsrs\Image;
use App\Models\Cmsrs\Translate;
use App\Services\Cmsrs\ConfigService;
use App\Services\Cmsrs\ImageService;
use App\Services\Cmsrs\Page\PageService;
use App\Services\Cmsrs\Translation\TranslationReader;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ImageTest extends Base
{
    use RefreshDatabase;

    const STR_DESC_IMG1 = 'description img1';

    private $name1;

    private $name2;

    private $file2;

    private $pageId;

    private $arrPageId;

    private $testImgData;

    private $pagesData;

    private $pageData;

    private $testProductData;

    protected function setUp(): void
    {
        putenv('LANGS="en"');
        putenv('API_SECRET=""');
        putenv('CURRENCY="USD"');
        putenv('CACHE_ENABLE=false');
        putenv('CACHE_ENABLE_FILE="app/cache_enable_test.txt"');
        putenv('DEMO_STATUS=false');
        putenv('IS_SHOP=true');
        putenv('IS_LOGIN=true');
        putenv('IS_REGISTER=true');
        putenv('IS_HEADLESS=false');
        putenv('IS_SSR=true');

        parent::setUp();
        ini_set('memory_limit', '256M');
        $this->createUser();

    }

    private function prepareTestPage($withImages = true)
    {
        $this->arrPageId = [];

        $this->name1 = 'phpunittest1.jpg';
        $file1 = $this->getFixtureBase64($this->name1);

        $this->name2 = 'phpunittest2.jpg';
        $file2 = $this->getFixtureBase64($this->name2);
        $this->file2 = $file2;

        $images = [
            ['name' => $this->name1, 'data' => $file1, 'alt' => ['en' => self::STR_DESC_IMG1]],
            ['name' => $this->name2, 'data' => $file2], // , 'alt' => ['en' => 'description img2' ]]
        ];

        $this->testImgData =
        [
            'title' => ['en' => 'test p2'],
            'short_title' => ['en' => 'p22'],
            'description' => ['en' => 'test1234'],
            'published' => 0,
            'commented' => 0,
            'after_login' => 0,
            'type' => 'shop', // this page will be useful for create product   'contact',
            'content' => ['en' => 'lorem ipsum'],
            'menu_id' => null,
            'page_id' => null,
            'images' => $withImages ? $images : null,
        ];

        $response = $this->post('api/pages?token='.$this->token, $this->testImgData);
        $res = $response->getData();

        $this->assertTrue($res->success);

        $pageId = $res->data->pageId;
        $this->assertNotEmpty($pageId);

        $this->pageData = $res->data->data;

        $this->pageId = $pageId;
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }

    public function test_it_will_set_translate()
    {
        $this->prepareTestPage();

        // one lang: en, and two images
        $this->assertEquals(2, Translate::query()->whereNotNull('image_id')->where('column', 'alt')->count());

        $altEn1 = Translate::query()->whereNotNull('image_id')->where('column', 'alt')->where('lang', 'en')->whereNotNull('value')->get('value')->first()->value;
        $this->assertEquals(self::STR_DESC_IMG1, $altEn1);

        $altEn2 = Translate::query()->whereNotNull('image_id')->where('column', 'alt')->where('lang', 'en')->whereNull('value')->get('value')->first()->value;
        $this->assertEquals(null, $altEn2);
    }

    public function test_it_will_get_page_with_images_with_auth_docs()
    {
        $this->prepareTestPage();

        $response0 = $this->get('api/pages/'.$this->pageId.'?token='.$this->token);
        $res0 = $response0->getData();
        $this->assertTrue($res0->success);

        $pageFields = Page::FIELDS; // app(PageService::class)->pageFields;
        $this->assertFalse(in_array('created_at', $pageFields));
        $this->assertTrue(in_array('id', $pageFields));

        $this->assertFalse(property_exists($res0->data, 'created_at'), 'Property created_at should not exists');
        $this->assertFalse(property_exists($res0->data, 'updated_at'), 'Property updated_at should not exists');
        $this->assertTrue(property_exists($res0->data, 'id'), 'Property id should exists');

        $this->assertNotEmpty($res0->data->id);
        $this->assertNotEmpty($res0->data->images);
    }

    public function test_it_will_get_page_without_images_with_auth_simple()
    {
        $this->markTestSkipped('param /simple in API url is not used in the project');
        $this->prepareTestPage();

        $response0 = $this->get('api/pages/'.$this->pageId.'/simple?token='.$this->token);
        $res0 = $response0->getData();

        $this->assertTrue($res0->success);
        $this->assertNotEmpty($res0->data->id);

        $this->assertTrue(empty($res0->data->images));
    }

    public function test_it_will_get_page_with_images_without_auth_docs()
    {
        $this->markTestSkipped('This API is not used in the project');

        $this->prepareTestPage();

        $response0 = $this->get('api/page/'.$this->pageId.'/fr');  // this method doesn't contain ticket - it is available as guest

        $this->assertFalse($response0->getData()->success);

        $response3 = $this->get('api/page/'.$this->pageId.'/en');

        $res3 = $response3->getData();
        $this->assertTrue($res3->success);

        $this->assertEquals($this->pageId, $res3->data->id);
        $this->assertNotEmpty($res3->data->type);

        $count = 2;
        $this->assertEquals($count, count($res3->data->images));

        $page = Page::findOrFail($this->pageId);

        $arrImages = (app(PageService::class))->arrImages($page, 'en');

        for ($i = 0; $i < $count; $i++) {
            $this->assertEquals($arrImages[$i]['org'], $res3->data->images[$i]->org);
            $this->assertEquals($arrImages[$i]['small'], $res3->data->images[$i]->small);
            $this->assertEquals($arrImages[$i]['medium'], $res3->data->images[$i]->medium);
            $this->assertEquals($arrImages[$i]['id'], $res3->data->images[$i]->id);
            $this->assertEquals($arrImages[$i]['alt']['en'], $res3->data->images[$i]->alt->en);

            // ti is change only after adding parameter to function: arrImages
            if ($i == 0) {
                $this->assertNotEmpty($arrImages[$i]['altlang']);
            }
            if ($i == 1) {
                $this->assertEmpty($arrImages[$i]['altlang']);
            }

            $this->assertTrue(isset($arrImages[$i]['altlang']));
        }
    }

    public function test_it_will_get_pages_with_images_docs()
    {
        $this->prepareTestPage();

        $response2 = $this->get('api/pages?token='.$this->token);

        $res2 = $response2->getData();
        $this->assertTrue($res2->success);

        $this->assertEquals(count($res2->data), 1);

        $this->assertEquals($res2->data[0]->id, $this->pageId);

        $this->assertEquals(count($res2->data[0]->images), count($this->testImgData['images']));

        $this->assertEquals(count((array) $res2->data[0]->images[0]->fs), count(Image::$thumbs) + 1);
        $this->assertEquals(count((array) $res2->data[0]->images[1]->fs), count(Image::$thumbs) + 1);

        $this->assertEquals($res2->data[0]->images[0]->name, $this->testImgData['images'][0]['name']);

        $this->assertIsInt($res2->data[0]->images[0]->position);
        $this->assertIsInt($res2->data[0]->images[0]->page_id);
        $this->assertIsInt($res2->data[0]->images[0]->id);

        // $this->assertObjectHasAttribute('alt', $res2->data[0]->images[0]);
        $this->assertNotEmpty($res2->data[0]->images[0]->alt);
        $this->assertEquals(self::STR_DESC_IMG1, $res2->data[0]->images[0]->alt->en);

        $this->assertEquals($res2->data[0]->images[1]->name, $this->testImgData['images'][1]['name']);

        // $this->assertObjectHasAttribute('alt', $res2->data[0]->images[1]);
        $this->assertNotEmpty($res2->data[0]->images[1]->alt);
        $this->assertEquals($res2->data[0]->images[1]->alt->en, null);

        $this->assertEquals(pathinfo($res2->data[0]->images[1]->fs->org, PATHINFO_BASENAME), $this->name2);

        foreach ($res2->data[0]->images[0]->fs as $key => $urlImg) {
            $fs = public_path($urlImg);
            $this->assertFileExists($fs);
        }

        $page = Page::findOrFail($this->pageId);
        $arrImages = (app(PageService::class))->arrImages($page, 'en');
        $this->assertEquals(2, count($arrImages));

        $this->assertNotEmpty($arrImages[0][Image::IMAGE_ORG]);
        $this->assertNotEmpty($arrImages[0][Image::IMAGE_THUMB_TYPE_SMALL]);
        $this->assertNotEmpty($arrImages[0][Image::IMAGE_THUMB_TYPE_MEDIUM]);
        $this->assertNotEmpty($arrImages[0]['alt']);
        $this->assertNotEmpty($arrImages[0]['id']);
        $this->assertNotEmpty($arrImages[0]['altlang']);
        // $this->clear_imgs();
    }

    public function test_it_will_delete_page_with_images_docs()
    {
        $this->prepareTestPage();

        $allPages = Page::All()->toArray();
        $this->assertEquals(1, count($allPages));

        $responseAllBefore = $this->get('api/images/page/'.$this->pageId.'?token='.$this->token);
        $resAllBefore = $responseAllBefore->getData();

        $i = 0;
        $ImagesInFs = [];
        foreach ($resAllBefore->data as $img) {
            $imgDb = Image::find($img->id);
            $imagesFs = app(ImageService::class)->getAllImage($imgDb);
            $j = 0;
            foreach ($imagesFs as $imgFs) {
                $ImagesInFs[$i][$j] = $imgFs;
                $this->assertFileExists($imgFs);
                $j++;
            }
            $this->assertEquals(3, $j); // org, small, medium
            $i++;
        }
        $this->assertEquals(2, $i);

        $testFile = $ImagesInFs[0][0];
        $this->assertFileExists($testFile);

        $translateBefore = Translate::query()->whereNotNull('image_id')->where('column', 'alt')->get()->toArray();
        $this->assertEquals(2, count($translateBefore));
        $this->assertEquals(self::STR_DESC_IMG1, $translateBefore[0]['value']);
        $this->assertEquals(null, $translateBefore[1]['value']);

        $objPage = Page::find($this->pageId);

        $objImage = $objPage->images()->first();
        $this->assertNotEmpty($objImage->id);
        $altEn = app(TranslationReader::class)->translatesByColumnAndLang($objImage, 'alt', 'en'); // For this method (translatesByColumnAndLang) maybe should create new test.
        $this->assertEquals(self::STR_DESC_IMG1, $altEn);
        $this->assertEquals(2, $objPage->images()->get()->count());

        $response0 = $this->delete('api/pages/'.$this->pageId.'?token='.$this->token);
        $res0 = $response0->getData();

        $this->assertTrue($res0->success);

        $this->assertEquals(0, $objPage->images()->get()->count());

        $translateAfter = Translate::query()->whereNotNull('image_id')->where('column', 'alt')->get()->toArray();
        $this->assertEmpty($translateAfter);

        $responseAllAfter = $this->get('api/pages?token='.$this->token);
        $resAllAfter = $responseAllAfter->getData();
        $this->assertEmpty($resAllAfter->data);

        $responseAllImgAfter = $this->get('api/images/page/'.$this->pageId.'?token='.$this->token);
        $resAllImgAfter = $responseAllImgAfter->getData();
        $this->assertEmpty($resAllImgAfter->data);

        foreach ($resAllBefore->data as $img) {
            $imgDb = Image::find($img->id);
            $this->assertNull($imgDb);
        }

        $ii = 0;
        foreach ($ImagesInFs as $imageByPage) {
            $jj = 0;
            foreach ($imageByPage as $imgFs) {
                $this->assertFileDoesNotExist($imgFs);
                $jj++;
            }
            $this->assertEquals(3, $jj); // org, small, medium
            $ii++;
        }
        $this->assertEquals(2, $ii);

        // checks dir
        $testFileDirname = pathinfo($testFile, PATHINFO_DIRNAME);
        $testFileDirnameDeep = pathinfo($testFileDirname, PATHINFO_DIRNAME);

        $this->assertFileDoesNotExist($testFileDirname);
        $this->assertFileDoesNotExist($testFileDirnameDeep);
        $this->assertFileDoesNotExist($testFile);
    }

    public function test_it_will_delete_many_images()
    {
        $this->prepareTestPage();
        $file1 = $this->getFixtureBase64($this->name1);
        $numbersTestImages = 3;

        $images = [];
        for ($i = 0; $i < $numbersTestImages; $i++) {
            $images[$i] = ['name' => $this->name1, 'data' => $file1, 'alt' => ['en' => 'img'.$i]];
        }
        $testImgData = $this->testImgData;
        $testImgData['images'] = $images;
        $response = $this->put('api/pages/'.$this->pageId.'?token='.$this->token, $testImgData);
        $res = $response->getData();
        $this->assertTrue($res->success);

        $response2 = $this->get('api/images/page/'.$this->pageId.'?token='.$this->token);
        $res2 = $response2->getData();
        $this->assertTrue($res2->success);
        $this->assertEquals($numbersTestImages + 2, count($res2->data)); // 2 initial image, 2+3 = 5

        // delete all items other then one item
        $deleteTestIds = [];
        $leaveTestIds = []; // only one
        foreach ($res2->data as $img) {
            if ($img->name == $this->name1) {
                $deleteTestIds[] = $img->id;
            } else {
                $leaveTestIds[] = $img->id;
            }
        }
        $this->assertEquals(4, count($deleteTestIds));

        $idsToDelete = implode(',', $deleteTestIds);

        $responseDel = $this->delete('api/images/'.$idsToDelete.'?token='.$this->token);
        $resDel = $responseDel->getData();
        $this->assertTrue($resDel->success);

        $response3 = $this->get('api/images/page/'.$this->pageId.'?token='.$this->token);
        $res3 = $response3->getData();
        $this->assertTrue($res3->success);

        $this->assertEquals(1, count($leaveTestIds));
        $this->assertEquals(1, count($res3->data));
        $this->assertEquals($leaveTestIds[0], $res3->data[0]->id);
    }

    public function test_it_will_delete_image_docs()
    {
        $this->prepareTestPage();
        // delete first image
        $response2 = $this->get('api/images/page/'.$this->pageId.'?token='.$this->token);
        $res2 = $response2->getData();

        $imgToDel = $res2->data[0];
        $this->assertNotEmpty($imgToDel->id);

        $imgDir = app(ImageService::class)->getImageDir('page', $imgToDel->page_id, $imgToDel->id);
        $file = $imgDir.'/'.$imgToDel->name;
        $this->assertFileExists($file);

        $fileName = pathinfo($imgToDel->name, PATHINFO_FILENAME);
        $fileExt = pathinfo($imgToDel->name, PATHINFO_EXTENSION);
        foreach (Image::$thumbs as $thumbName => $dimension) {
            $fileThumb = $imgDir.'/'.$fileName.'-'.$thumbName.'.'.$fileExt;
            $this->assertFileExists($fileThumb);
        }

        $translateBefore = Translate::query()->where('image_id', $imgToDel->id)->where('column', 'alt')->get()->toArray();
        $this->assertEquals(1, count($translateBefore));
        $this->assertEquals(self::STR_DESC_IMG1, $translateBefore[0]['value']);

        $responseDel = $this->delete('api/images/'.$imgToDel->id.'?token='.$this->token);
        $resDel = $responseDel->getData();
        $this->assertTrue($resDel->success);

        $translateAfter = Translate::query()->where('image_id', $imgToDel->id)->where('column', 'alt')->get()->toArray();
        $this->assertEmpty($translateAfter);

        $this->assertFileDoesNotExist($file);

        foreach (Image::$thumbs as $thumbName => $dimension) {
            $fileThumb = $imgDir.'/'.$fileName.'-'.$thumbName.'.'.$fileExt;
            $this->assertFileDoesNotExist($fileThumb);
        }

        $response22 = $this->get('api/images/page/'.$this->pageId.'?token='.$this->token);
        $res22 = $response22->getData();
        $this->assertTrue($res22->success);
        $this->assertEquals(count($res22->data), 1);
        // $this->clear_imgs();
    }

    public function test_it_will_get_images_by_page_id_docs()
    {
        $this->prepareTestPage();
        $response2 = $this->get('api/images/page/'.$this->pageId.'?token='.$this->token);

        $res2 = $response2->getData();
        $this->assertTrue($res2->success);
        $this->assertEquals(count($res2->data), 2);

        foreach ($res2->data as $imageUrl) {
            $fs = (array) $imageUrl->fs;

            $this->assertEquals(count($fs), count(Image::$thumbs) + 1);
        }
        $this->assertEquals($res2->data[0]->name, $this->name1);

        // $this->clear_imgs();
    }

    public function test_it_will_add_pages_with_the_same_image_name()
    {
        $this->prepareTestPage();
        // test fake
        $this->testImgData['images'][1]['name'] = $this->name1;
        $this->assertEquals($this->testImgData['images'][0]['name'], $this->testImgData['images'][1]['name']);

        $this->testImgData['title']['en'] = 'uniq tilte';
        $responseErr = $this->post('api/pages?token='.$this->token, $this->testImgData);
        $resErr = $responseErr->getData();

        $this->assertTrue($resErr->success);

        // clear images all pages
        $response = $this->get('api/pages?token='.$this->token);
        $res = $response->getData();
        $this->assertTrue($res->success);

        $this->assertEquals(count($res->data), 2);
        $this->assertEquals(count($res->data[1]->images), 2);

        $this->assertEquals($res->data[1]->images[0]->name, $res->data[1]->images[1]->name);

        foreach ($res->data as $page) {
            $this->arrPageId[] = $page->id;
            // $this->clear_imgs();
        }
    }

    /**
     * update existing images
     */
    public function test_it_will_update_page_with_images_position_and_without_upload_new_imgs_case2_docs()
    {
        $this->prepareTestPage();
        $response0 = $this->get('api/pages/'.$this->pageId.'?token='.$this->token);
        $res0 = $response0->getData();
        $this->assertTrue($res0->success);

        $this->assertEquals(2, count($res0->data->images));
        $id1Before = $res0->data->images[0]->id;
        $id2Before = $res0->data->images[1]->id;

        $this->assertNotEmpty($id1Before);
        $this->assertEquals(1, $res0->data->images[0]->position);
        $this->assertNotEmpty($id2Before);
        $this->assertEquals(2, $res0->data->images[1]->position);

        $this->assertTrue($id1Before < $id2Before);
        $this->assertTrue($res0->data->images[0]->position < $res0->data->images[1]->position);

        $newImagesPosition['title']['en'] = 'Update2 img Title';
        $newImagesPosition['short_title']['en'] = 'u2 test123';
        $newImagesPosition['images'][0]['id'] = $id1Before;
        $newImagesPosition['images'][0]['alt']['en'] = 'last';
        $newImagesPosition['images'][0]['position'] = '202';

        $newImagesPosition['images'][1]['id'] = $id2Before;
        $newImagesPosition['images'][1]['alt']['en'] = 'first';
        $newImagesPosition['images'][1]['position'] = 101;

        $response = $this->put('api/pages/'.$this->pageId.'?token='.$this->token, $newImagesPosition);
        $res = $response->getData();
        $this->assertTrue($res->success);

        // check
        $response1 = $this->get('api/pages/'.$this->pageId.'?token='.$this->token);
        $res1 = $response1->getData();
        $this->assertTrue($res1->success);

        $this->assertEquals($newImagesPosition['title']['en'], $res1->data->title->en);
        $this->assertEquals($newImagesPosition['short_title']['en'], $res1->data->short_title->en);

        $images1 = $res1->data->images;
        $this->assertEquals($id2Before, $images1[0]->id);
        $this->assertEquals(101, $images1[0]->position);
        $this->assertEquals('first', $images1[0]->alt->en);

        $this->assertEquals($id1Before, $images1[1]->id);
        $this->assertEquals(202, $images1[1]->position);
        $this->assertEquals('last', $images1[1]->alt->en);

        // check all pages (we use this api in vue.js)
        $response2 = $this->get('api/pages?token='.$this->token);
        $res2 = $response2->getData();
        $this->assertTrue($res2->success);

        $this->assertEquals($this->pageId, $res2->data[0]->id);
        $images2 = $res2->data[0]->images;

        $this->assertEquals($id2Before, $images2[0]->id);
        $this->assertEquals(101, $images2[0]->position);
        $this->assertEquals('first', $images2[0]->alt->en);

        $this->assertEquals($id1Before, $images2[1]->id);
        $this->assertEquals(202, $images2[1]->position);
        $this->assertEquals('last', $images2[1]->alt->en);
    }

    public function test_it_will_update_page_with_images_docs()
    {
        $this->prepareTestPage();
        $this->assertEquals(count((array) $this->pageData->images), 2);
        $this->assertEquals($this->pageData->title->en, $this->testImgData['title']['en']);

        $response0 = $this->get('api/pages?token='.$this->token);

        $res0 = $response0->getData();
        $this->assertTrue($res0->success);
        $testPage0 = $res0->data[0];

        $existChangeAltImg = $testPage0->images;
        $this->assertEquals(count($existChangeAltImg), 2);

        $alt1 = ['en' => 'alt1'];
        $alt2 = ['en' => null];

        $existChangeAltImg = [
            ['id' => $existChangeAltImg[0]->id, 'alt' => $alt1],
            ['id' => $existChangeAltImg[1]->id, 'alt' => $alt2],
        ];

        $name3 = 'phpunittest2.jpg';
        $file3 = $this->getFixtureBase64($name3);

        $newImages = [
            ['name' => $name3, 'data' => $file3],
            ['name' => $name3, 'data' => $file3],
        ];

        $images = array_merge($existChangeAltImg, $newImages);
        $this->assertEquals(count($images), 4);

        $updateTitle = 'Update img Title';
        $testImgData['title']['en'] = $updateTitle;
        $testImgData['short_title']['en'] = 'test123';
        $testImgData['images'] = $images;

        $translateBefore = Translate::query()->whereNotNull('image_id')->where('column', 'alt')->get()->toArray();
        $this->assertEquals(2, count($translateBefore));
        $this->assertEquals(self::STR_DESC_IMG1, $translateBefore[0]['value']);
        $this->assertEquals(null, $translateBefore[1]['value']);

        // ----PUT--------
        $response = $this->put('api/pages/'.$this->pageId.'?token='.$this->token, $testImgData);
        $res = $response->getData();
        $this->assertTrue($res->success);

        $translateAfter = Translate::query()->whereNotNull('image_id')->where('column', 'alt')->get()->toArray();
        $this->assertEquals(4, count($translateAfter));
        $this->assertEquals($alt1['en'], $translateAfter[0]['value']);
        $this->assertEquals($alt2['en'], $translateAfter[1]['value']);
        $this->assertEquals(null, $translateAfter[2]['value']);
        $this->assertEquals(null, $translateAfter[3]['value']);

        $response2 = $this->get('api/pages?token='.$this->token);
        $res2 = $response2->getData();
        $this->assertTrue($res2->success);

        $testPage = $res2->data[0];

        $this->assertEquals($testPage->id, $this->pageId);

        $this->assertEquals($testPage->title->en, $updateTitle);
        $this->assertEquals(count($testPage->images), 4);

        $this->assertEquals(pathinfo($testPage->images[2]->fs->org, PATHINFO_BASENAME), $name3);
        $this->assertEquals(count((array) $testPage->images[2]->fs), count(Image::$thumbs) + 1);

        $this->assertEquals($testPage->images[0]->alt->en, $alt1['en']);
        $this->assertEquals($testPage->images[1]->alt->en, $alt2['en']);
        $this->assertEquals($testPage->images[2]->alt->en, null);
        $this->assertEquals($testPage->images[3]->alt->en, null);

        // $imageObj11 = Image::find($testPage->images[2]->id);
        // $mediumHtml = app(ImageService::class)->getHtmlImage($imageObj11);
        // $this->assertEquals($mediumHtml, $testPage->images[2]->fs->medium);
    }

    public function test_it_will_get_change_position_images_docs()
    {
        $this->prepareTestPage();
        $response2 = $this->get('api/images/page/'.$this->pageId.'?token='.$this->token);
        $res2 = $response2->getData();
        $this->assertTrue($res2->success);
        $this->assertEquals(2, count($res2->data));

        $resSwap = $this->patch('api/images/position/up/'.$res2->data[0]->id.'?token='.$this->token);

        $response2Swap = $this->get('api/images/page/'.$this->pageId.'?token='.$this->token);
        $res2Swap = $response2Swap->getData();
        $this->assertTrue($res2Swap->success);
        $this->assertEquals(count($res2Swap->data), 2);

        $this->assertEquals($res2Swap->data[0]->name, $this->name2);

        $page = Page::findOrFail($this->pageId);
        $images = $page->images;
        $arrImages = $images->toArray();

        $this->assertTrue($arrImages[0]['position'] < $arrImages[1]['position']);
        // $this->clear_imgs();
    }

    public function test_it_will_get_change_position_images_for_many_items()
    {
        $this->prepareTestPage();
        $file1 = $this->getFixtureBase64($this->name1);
        $numbersTestImages = 3;

        $images = [];
        for ($i = 0; $i < $numbersTestImages; $i++) {
            $images[$i] = ['name' => $this->name1, 'data' => $file1, 'alt' => ['en' => 'img'.$i]];
        }
        $testImgData = $this->testImgData;
        $testImgData['images'] = $images;
        $response = $this->put('api/pages/'.$this->pageId.'?token='.$this->token, $testImgData);
        $res = $response->getData();
        $this->assertTrue($res->success);

        $response2 = $this->get('api/images/page/'.$this->pageId.'?token='.$this->token);
        $res2 = $response2->getData();
        $this->assertTrue($res2->success);
        $this->assertEquals($numbersTestImages + 2, count($res2->data)); // 2 initial image, 2+3 = 5

        $this->assertEquals(self::STR_DESC_IMG1, $res2->data[0]->alt->en);
        $this->assertEquals(null, $res2->data[1]->alt->en);
        $this->assertEquals('img0', $res2->data[2]->alt->en);
        $this->assertEquals('img1', $res2->data[3]->alt->en);
        $this->assertEquals('img2', $res2->data[4]->alt->en);

        $resSwap = $this->patch('api/images/position/down/'.$res2->data[4]->id.'?token='.$this->token);
        $resS = $resSwap->getData();
        $this->assertTrue($resS->success);

        $response3 = $this->get('api/images/page/'.$this->pageId.'?token='.$this->token);
        $res3 = $response3->getData();
        $this->assertTrue($res3->success);

        $this->assertEquals('img2', $res3->data[0]->alt->en); // we swap two items not everything - it is a good approach, because we can set items position as we want.
        $this->assertEquals(null, $res3->data[1]->alt->en);
        $this->assertEquals('img0', $res3->data[2]->alt->en);
        $this->assertEquals('img1', $res3->data[3]->alt->en);
        $this->assertEquals(self::STR_DESC_IMG1, $res3->data[4]->alt->en);

        $resSwap = $this->patch('api/images/position/up/'.$res3->data[3]->id.'?token='.$this->token); // or
        // $resSwap = $this->get('api/images/position/down/'.$res3->data[2]->id.'?token='.$this->token);
        $resS2 = $resSwap->getData();
        $this->assertTrue($resS2->success);

        $response4 = $this->get('api/images/page/'.$this->pageId.'?token='.$this->token);
        $res4 = $response4->getData();
        $this->assertTrue($res4->success);

        $this->assertEquals('img2', $res4->data[0]->alt->en);
        $this->assertEquals(null, $res4->data[1]->alt->en);
        $this->assertEquals('img1', $res4->data[2]->alt->en);
        $this->assertEquals('img0', $res4->data[3]->alt->en); // we swap two items not everything - it is a good approach.
        $this->assertEquals(self::STR_DESC_IMG1, $res4->data[4]->alt->en);

    }

    public function test_it_will_save_one_image_docs()
    {
        $this->prepareTestPage();
        $page = Page::findOrFail($this->pageId);
        $images = $page->images;
        $arrImages = $images->toArray();
        $this->assertTrue(count($arrImages) > 1);

        $image = ['name' => $this->name2, 'data' => $this->file2];

        $type = 'page';
        $response = $this->post('api/image/'.$type.'/'.$this->pageId.'?token='.$this->token, $image);

        $this->assertEquals(200, $response->status());

        $res = $response->getData();
        $this->assertTrue($res->success);

        $page2 = Page::findOrFail($this->pageId);
        $images2 = $page2->images;
        $arrImages2 = $images2->toArray();
        $this->assertEquals(count($arrImages) + 1, count($arrImages2));
    }

    public function test_it_will_try_to_save_wrong1_image_docs()
    {
        $this->prepareTestPage(false);
        $page = Page::findOrFail($this->pageId);
        $images = $page->images;
        $arrImages = $images->toArray();
        $this->assertEquals(0, count($arrImages));

        $image = ['name' => 'fake_name', 'data' => $this->file2];

        $type = 'page';
        $response = $this->post('api/image/'.$type.'/'.$this->pageId.'?token='.$this->token, $image);

        $this->assertEquals(200, $response->status());

        $res = $response->getData();
        $this->assertEquals(false, $res->success);
        $this->assertNotEmpty($res->error);

        $expectedStart = 'File is not an image:';
        $this->assertStringStartsWith($expectedStart, $res->error);

        $arrAllowedUploadFileExt = (new ConfigService)->arrAllowedUploadFileExt();
        $expectedEnding = implode(', ', $arrAllowedUploadFileExt);
        $this->assertTrue(str_ends_with($res->error, $expectedEnding), '---The message does not end with the allowed extensions.---');

        $page2 = Page::findOrFail($this->pageId);
        $images2 = $page2->images;
        $arrImages2 = $images2->toArray();
        $this->assertEquals(0, count($arrImages2));
    }

    public function test_it_will_try_to_save_wrong2_image()
    {
        $this->prepareTestPage(false);
        $page = Page::findOrFail($this->pageId);
        $images = $page->images;
        $arrImages = $images->toArray();
        $this->assertEquals(0, count($arrImages));

        $image = ['name' => '.png', 'data' => $this->file2];

        $type = 'page';
        $response = $this->post('api/image/'.$type.'/'.$this->pageId.'?token='.$this->token, $image);

        $this->assertEquals(200, $response->status());

        $res = $response->getData();
        $this->assertEquals(false, $res->success);
        $this->assertNotEmpty($res->error);

        $expectedStart = 'Wrong file name';
        $this->assertStringStartsWith($expectedStart, $res->error);
    }

    public function test_it_will_save_one_image_with_err_type()
    {
        $this->prepareTestPage();
        $image = ['name' => $this->name2, 'data' => $this->file2];

        $response = $this->post('api/image/pageee/'.$this->pageId.'?token='.$this->token, $image);

        $this->assertEquals(404, $response->status());
        $response->assertJson([
            'success' => false,
            'error' => 'page type not exist',
        ]);

    }

    public function test_it_will_save_one_image_with_err_page()
    {
        $this->prepareTestPage();
        $image = ['name' => $this->name2, 'data' => $this->file2];

        $pageIdFake = 123456;
        $response = $this->post('api/image/page/'.$pageIdFake.'?token='.$this->token, $image);

        $this->assertEquals(404, $response->status());
        $response->assertJson([
            'success' => false,
            'error' => 'obj not found',
        ]);
    }

    /**
     * product test
     */
    private function createProductBelongsToTestPage($i = 1)
    {
        $this->testProductData = [
            'product_name' => ['en' => 'test product name'.$i],
            'sku' => 'AN/34534'.$i,
            'price' => 123,
            'product_description' => ['en' => 'test product description'],
            'page_id' => $this->pageId,
            'published' => 1,
            'images' => [
                ['name' => $this->name1, 'data' => $this->getFixtureBase64($this->name1),  'alt' => ['en' => 'product_img1']],
                ['name' => $this->name1, 'data' => $this->getFixtureBase64($this->name1),  'alt' => ['en' => 'product_img2']],
                ['name' => $this->name1, 'data' => $this->getFixtureBase64($this->name1),  'alt' => ['en' => 'product_img3']],
            ],
        ];
        $response0 = $this->post('api/products?token='.$this->token, $this->testProductData);
        $res0 = $response0->getData();
        $this->assertTrue($res0->success);

        return $res0->data->productId;
    }

    public function test_check_images_numbers_given_product()
    {
        $this->prepareTestPage();

        $productId = $this->createProductBelongsToTestPage(1);
        $this->assertNotEmpty($productId);
        $productId2 = $this->createProductBelongsToTestPage(2);
        $this->assertNotEmpty($productId2); // I would like to that productId == 2 (not 1, because pageId ==1)

        $type = 'product';
        $response2 = $this->get('api/images/'.$type.'/'.$productId2.'?token='.$this->token);
        $res2 = $response2->getData();
        $this->assertTrue($res2->success);
        $this->assertEquals(3, count($res2->data)); // 3 initial image

        foreach ($res2->data as $d) {
            $this->assertEquals($productId2, $d->product_id);
            $this->assertNull($d->page_id);
        }
    }

    public function test_upload_images_to_given_product()
    {
        $this->prepareTestPage();
        $productId = $this->createProductBelongsToTestPage(1);
        $this->assertNotEmpty($productId);
        $productId2 = $this->createProductBelongsToTestPage(2);
        $this->assertNotEmpty($productId2); // I would like to that productId == 2 (not 1, because pageId ==1)

        $image = ['name' => $this->name2, 'data' => $this->file2];

        $type = 'product';
        $response = $this->post('api/image/'.$type.'/'.$productId2.'?token='.$this->token, $image);
        $this->assertEquals(200, $response->status());

        $res = $response->getData();
        $this->assertTrue($res->success);

        $response2 = $this->get('api/images/'.$type.'/'.$productId2.'?token='.$this->token);
        $res2 = $response2->getData();
        $this->assertTrue($res2->success);
        $this->assertEquals(4, count($res2->data)); // 3 initial image, 1 - after upload, so 3+1 = 4

        foreach ($res2->data as $d) {
            $this->assertEquals($productId2, $d->product_id);
            $this->assertNull($d->page_id);
        }
    }

    public function test_position_image_product()
    {
        $this->prepareTestPage();
        $productId = $this->createProductBelongsToTestPage(1);
        $this->assertNotEmpty($productId);
        $productId2 = $this->createProductBelongsToTestPage(2);
        $this->assertNotEmpty($productId2); // I would like to that productId == 2 (not 1, because pageId ==1)

        $type = 'product';
        $response2 = $this->get('api/images/'.$type.'/'.$productId2.'?token='.$this->token);
        $res2 = $response2->getData();
        $this->assertTrue($res2->success);
        $this->assertEquals(3, count($res2->data)); // 3 initial image

        $this->assertEquals('product_img1', $res2->data[0]->alt->en);
        $this->assertEquals('product_img2', $res2->data[1]->alt->en);
        $this->assertEquals('product_img3', $res2->data[2]->alt->en);

        $resSwap = $this->patch('api/images/position/down/'.$res2->data[1]->id.'?token='.$this->token);
        $resS2 = $resSwap->getData();
        $this->assertTrue($resS2->success);

        $response3 = $this->get('api/images/'.$type.'/'.$productId2.'?token='.$this->token);
        $res3 = $response3->getData();
        $this->assertTrue($res3->success);
        $this->assertEquals(3, count($res3->data)); // 3 initial image

        $this->assertEquals('product_img1', $res3->data[0]->alt->en);
        $this->assertEquals('product_img3', $res3->data[1]->alt->en);
        $this->assertEquals('product_img2', $res3->data[2]->alt->en);

        foreach ($res3->data as $d) {
            $this->assertEquals($productId2, $d->product_id);
            $this->assertNull($d->page_id);
        }

    }

    /**
     * update existing images belongs to given product
     */
    public function test_it_will_update_product_with_images_position_and_without_upload_new_imgs_case2_docs()
    {
        $this->prepareTestPage();
        $productId = $this->createProductBelongsToTestPage(1);
        $this->assertNotEmpty($productId);
        $productId2 = $this->createProductBelongsToTestPage(2);
        $this->assertNotEmpty($productId2); // I would like to that productId == 2 (not 1, because pageId ==1)

        $response0 = $this->get('api/products/'.$productId2.'?token='.$this->token);
        $res0 = $response0->getData();
        $this->assertTrue($res0->success);

        $this->assertEquals(3, count($res0->data->images));
        $id1Before = $res0->data->images[0]->id;
        $id2Before = $res0->data->images[1]->id;
        $id3Before = $res0->data->images[2]->id;

        $this->assertNotEmpty($id1Before);
        $this->assertEquals(1, $res0->data->images[0]->position);
        $this->assertNotEmpty($id2Before);
        $this->assertEquals(2, $res0->data->images[1]->position);
        $this->assertNotEmpty($id3Before);
        $this->assertEquals(3, $res0->data->images[2]->position);

        $this->assertTrue($id1Before < $id2Before);
        $this->assertTrue($res0->data->images[0]->position < $res0->data->images[1]->position);

        $newImagesPosition['product_name']['en'] = 'Update2 img Title';
        $newImagesPosition['sku'] = 'sku/23423';
        $newImagesPosition['price'] = '1111';
        $newImagesPosition['page_id'] = $this->pageId;
        $newImagesPosition['images'][0]['id'] = $id1Before;
        $newImagesPosition['images'][0]['alt']['en'] = 'last';
        $newImagesPosition['images'][0]['position'] = 202;

        $newImagesPosition['images'][1]['id'] = $id2Before;
        $newImagesPosition['images'][1]['alt']['en'] = 'first';
        $newImagesPosition['images'][1]['position'] = 101;

        $newImagesPosition['images'][2]['id'] = $id3Before;
        $newImagesPosition['images'][2]['alt']['en'] = 'middle';
        $newImagesPosition['images'][2]['position'] = 155;

        $response = $this->put('api/products/'.$productId2.'?token='.$this->token, $newImagesPosition);
        $res = $response->getData();
        $this->assertTrue($res->success);

        // check
        $response1 = $this->get('api/products/'.$productId2.'?token='.$this->token);
        $res1 = $response1->getData();
        $this->assertTrue($res1->success);

        $this->assertEquals($newImagesPosition['product_name']['en'], $res1->data->product_name->en);
        $this->assertEquals($newImagesPosition['sku'], $res1->data->sku);
        $this->assertEquals($newImagesPosition['price'], $res1->data->price);
        $this->assertEquals($newImagesPosition['page_id'], $res1->data->page_id);

        $this->assertEquals($id2Before, $res1->data->images[0]->id);
        $this->assertEquals(101, $res1->data->images[0]->position);
        $this->assertEquals('first', $res1->data->images[0]->alt->en);

        $this->assertEquals($id3Before, $res1->data->images[1]->id);
        $this->assertEquals(155, $res1->data->images[1]->position);
        $this->assertEquals('middle', $res1->data->images[1]->alt->en);

        $this->assertEquals($id1Before, $res1->data->images[2]->id);
        $this->assertEquals(202, $res1->data->images[2]->position);
        $this->assertEquals('last', $res1->data->images[2]->alt->en);
    }
}
