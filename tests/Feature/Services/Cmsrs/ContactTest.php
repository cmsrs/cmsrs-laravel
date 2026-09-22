<?php

namespace Tests\Feature\Services\Cmsrs;

use App\Enums\Cmsrs\SortDirection;
use App\Models\Cmsrs\Cms\Contact;
use App\Services\Cmsrs\ConfigService;
use App\Services\Cmsrs\ContactService;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ContactTest extends Base
{
    use RefreshDatabase;

    public $content1;

    public $content2;

    public $pagination;

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
        $this->createUser();

        $this->content1 = [
            'email' => 'test@example.com',
            'message' => 'test message - test1',
        ];
        $this->content2 = [
            'email' => 'ja@cmsrs.pl',
            'message' => 'test2',
        ];

        $this->pagination = ConfigService::getPagination(); // 10 - change .env.testing
        $this->assertEquals(10, $this->pagination);

    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->content1 = null;
        $this->content2 = null;
    }

    public function setTestData()
    {
        $response1 = $this->post('api/contact/en', $this->content1);
        $this->assertTrue($response1->getData()->success);

        $response2 = $this->post('api/contact/en', $this->content2);
        $this->assertTrue($response2->getData()->success);

        $this->assertEquals(2, Contact::All()->count());
    }

    public function test_it_will_get_contact_data_docs()
    {
        $this->setTestData();

        $response = $this->get('api/contacts?token='.$this->token);
        $res = $response->getData();
        $this->assertTrue($res->success);
        $this->assertEquals(2, count($res->data));

        $this->assertEquals($this->content1['email'], $res->data[0]->email);
        $this->assertEquals($this->content2['email'], $res->data[1]->email);

        $this->assertNotEmpty($res->data[0]->created_at_format);
        $this->assertNotEmpty($res->data[1]->created_at_format);

        $this->assertNotEmpty($res->data[0]->id);
        $this->assertNotEmpty($res->data[1]->id);
    }

    public function test_it_will_delete_contact_data_docs()
    {
        $this->setTestData();

        $response = $this->get('api/contacts?token='.$this->token);
        $res = $response->getData();
        $this->assertTrue($res->success);
        $this->assertEquals(2, count($res->data));

        $id = $res->data[0]->id;
        $this->assertNotEmpty($res->data[0]->id);

        $response0 = $this->delete('api/contacts/'.$id.'?token='.$this->token);
        $res0 = $response0->getData();

        $this->assertTrue($res0->success);

        $this->assertEquals(1, Contact::All()->count());
    }

    public function test_it_will_create_contact_by_wrap_create()
    {
        $ret = (new ContactService)->wrapCreate($this->content1);
        $this->assertEquals(1, Contact::All()->count());

        $d = Contact::All()->first()->toArray();
        $this->assertEquals($d['email'], $this->content1['email']);
    }

    /**
     * contact with pagination, searching and sorting
     */
    public function createManyContactMessages($count)
    {
        for ($ii = 1; $ii <= $count; $ii++) {
            $content = [
                'email' => "test$ii@example.com",
                'message' => "test bulk message - test$ii",
            ];

            // (new Contact)->wrapCreate(["email" => "tt$ii@cmsrs.pl", "message" => "test contact message$ii" ]);
            $response1 = $this->post('api/contact/en', $content);
            $this->assertTrue($response1->getData()->success);
        }

        $this->assertEquals($count, Contact::All()->count());
    }

    public function test_it_will_get_many_contact_messages_with_pagination_and_sort()
    {
        $numbersOfMsg = 24;
        $this->createManyContactMessages($numbersOfMsg);
        $contacts = Contact::orderBy('id', 'asc')->get()->toArray();
        $this->assertEquals($numbersOfMsg, count($contacts));

        $response = $this->get('api/contacts/pagination/id/desc?token='.$this->token);
        $res = $response->getData();
        $this->assertTrue($res->success);

        $this->assertEquals($this->pagination, $res->data->per_page);
        $lastPage = (int) (round(($numbersOfMsg + 1) / $res->data->per_page)); // (25/10 = 2.5 - round to up => 3.0)

        $response2 = $this->get('api/contacts/pagination/id/desc?page='.$lastPage.'&token='.$this->token);
        $res2 = $response2->getData();
        $this->assertTrue($res2->success);

        $firstContacts = $contacts[0];
        $lastContacts = $contacts[count($contacts) - 1];

        $firstId = $res2->data->data[3]->id; // 10,10,4 (3 = 4 -1)
        $lastId = $res->data->data[0]->id;

        $this->assertEquals($firstId, $firstContacts['id']);
        $this->assertEquals($lastId, $lastContacts['id']);
    }

    public function test_restrict_columns_to_specific_names()
    {
        $response = $this->get('api/contacts/pagination/fake/desc?token='.$this->token);
        $objContact = new Contact;

        $this->assertEquals(404, $response->status());
        $response->assertJson([
            'success' => false,

            'error' => 'available columns to sort contact message: '.implode(',', $objContact->columnsAllowedToSort),
        ]);
    }

    public function test_restrict_direction_to_specific_names()
    {
        $response = $this->get('api/contacts/pagination/id/fake?token='.$this->token);

        $this->assertEquals(404, $response->status());
        $response->assertJson([
            'success' => false,
            'error' => 'available direction to sort: '.implode(',', SortDirection::values()),
        ]);
    }

    public function test_sort_by_all_columns()
    {
        $numbersOfMsg = 19;
        $this->createManyContactMessages($numbersOfMsg);

        $objContact = new Contact;
        $this->assertNotEmpty(count($objContact->columnsAllowedToSort));
        foreach ($objContact->columnsAllowedToSort as $columnName) {
            $contacts = Contact::orderBy($columnName, 'desc')->get()->toArray();
            $firstContact = $contacts[0];

            $response = $this->get("api/contacts/pagination/$columnName/desc?token=".$this->token);
            $res = $response->getData();
            $this->assertTrue($res->success);

            $firstItem = $res->data->data[0]->{$columnName};

            $this->assertEquals($firstItem, $firstContact[$columnName]);

        }
    }

    public function test_search_contact_messages_by_email_or_message_docs()
    {
        $name = 'abc';
        (new ContactService)->wrapCreate(['email' => "$name@cmsrs.pl", 'message' => 'test contact message']);
        (new ContactService)->wrapCreate(['email' => 'tt@cmsrs.pl', 'message' => "test contact $name"]);

        $url = 'api/contacts/pagination/id/desc?token='.$this->token."&search=$name";
        $response = $this->get($url);
        $res = $response->getData();

        $this->assertTrue($res->success);

        $this->assertEquals(2, count($res->data->data));
        $this->assertEquals("test contact $name", $res->data->data[0]->message);
        $this->assertEquals("$name@cmsrs.pl", $res->data->data[1]->email);
    }
}
