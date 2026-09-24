<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Data\Demo;
use App\Models\Cmsrs\Cms\Comment;
use App\Models\Cmsrs\Shop\Product;
use App\Models\User;
use App\Services\Cmsrs\Cms\ContactService;
use App\Services\Cmsrs\Shop\DeliverService;
use App\Services\Cmsrs\Shop\PaymentService;
use App\Services\Cmsrs\Shop\Product\ProductService;
use Illuminate\Console\Command;

class LoadDemoDataCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cmsrs:load-demo-data';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Load Demo Data';

    // private $langs;
    // private $translate;
    // private $content;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();

        // $this->langs = ['en'];
        // $this->translate = new TranslateService;
        // $this->translate->setArrLangs($this->langs);
        // $this->content = new ContentService;
        // $this->content->setArrLangs($this->langs);
    }

    private function wrapSaveUser(int $i): int
    {
        $name = 'client'.$i;
        $emailClient = $name.'@cmsrs.pl';
        $user32 = new User([
            'name' => $name,
            'email' => $emailClient,
            'role' => User::$role_dict['client'],
        ]);
        $user32->password = 'cmsrs456';
        $user32->save();
        if (empty($user32->id)) {
            exit('Sth wrong with save user');
        }

        return $user32->id;
    }

    /**
     * @param  array<int, Product>  $products
     * @return array<string, mixed>
     */
    private function getDataSaveCheckout(array $products, int $i): array
    {
        $prod0 = empty($products[0]) ? exit("can't find product0 to checkout") : $products[0];
        $prod1 = empty($products[1]) ? exit("can't find product1 to checkout") : $products[1];
        $prod2 = empty($products[2]) ? exit("can't find product2 to checkout") : $products[2];
        $prod3 = empty($products[3]) ? exit("can't find product3 to checkout") : $products[3];

        if (empty($prod0->id) || empty($prod1->id) || empty($prod2->id) || empty($prod3->id)) {
            exit('product id not occur');
        }

        $name = 'client'.$i;
        $emailClient = $name.'@cmsrs.pl';

        $data =

        [
            'products' => [
                0 => [
                    'id' => $prod0->id,
                    'qty' => (($i + 1) % 4) + 1, // we don't  want 0, therefore we add 1 at the end
                ],

                1 => [
                    'id' => $prod1->id,
                    'qty' => (($i + 2) % 4) + 1,
                ],
                2 => [
                    'id' => $prod2->id,
                    'qty' => (($i + 3) % 4) + 1,
                ],
                3 => [
                    'id' => $prod3->id,
                    'qty' => (($i + 4) % 4) + 1,
                ],

            ],
            'lang' => 'en',
            'email' => $emailClient,
            'first_name' => $name,
            'last_name' => 'Kowalski',
            'address' => "ul. Kolejowa $i m 2",
            'country' => 'Polska',
            'city' => 'Warszawa',
            'telephone' => 123456788 + $i,
            'postcode' => '03-456',
            'deliver' => DeliverService::KEY_DPD_COURIER,
            'payment' => PaymentService::KEY_CASH,
        ];

        return $data;
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        ini_set('memory_limit', '1028M');
        $objDemoData = new Demo;

        /* --------------------- */
        /* --- pages an menu --- */
        /* --------------------- */

        $p = $objDemoData->pagesAndMenu();

        /* --------------------- */
        /* --- comments -------- */
        /* --------------------- */
        Comment::create(['page_id' => $p['p2']->id,  'content' => 'First test comment - test1']);
        Comment::create(['page_id' => $p['p2']->id,  'content' => 'Second test comment - test2']);

        /* --------------------- */
        /* --- products -------- */
        /* --------------------- */

        $products = $objDemoData->product($p);

        /* --------------------- */
        /* --- contacts -------- */
        /* --------------------- */
        for ($ii = 1; $ii <= 32; $ii++) {
            (new ContactService)->wrapCreate(['email' => "tt$ii@cmsrs.pl", 'message' => "test contact message$ii"]);
        }

        /* --------------------- */
        /* ---users ----------- */
        /* --------------------- */

        /** and */

        /* --------------------- */
        /* ---checkout--------- */
        /* --------------------- */

        // It,s created by the 'seed'
        // $user = new User([
        //     'email'    => 'adm@cmsrs.pl',
        //     'name'     => 'adm',
        //     'role' => User::$role_dict['admin']
        // ]);
        // $user->password = 'cmsrs123';
        // $user->save();

        /**
         * remove to seeder
         */
        /*
        $emailClient = 'client@cmsrs.pl';
        $user2 = new User([
            'email'    => $emailClient,
            'name'     => 'client',
            'role' => User::$role_dict['client']
        ]);
        $user2->password = 'cmsrs456';
        $user2->save();
         */

        /*
        $numberOfFakeUsers = 32;
        //$demoUsers = [];
        for($i=1; $i<=$numberOfFakeUsers; $i++){ //!!!! dry
            $name = 'client'.$i;
            $emailClient = $name.'@cmsrs.pl';
            $user32 = new User([
                'name' => $name,
                'email' => $emailClient,
                'role' => User::$role_dict['client']
            ]);
            $user32->password = 'cmsrs456';
            $user32->save();
        }
        */

        $numberOfFakeUsers = 32;
        for ($i = 1; $i <= $numberOfFakeUsers; $i++) {
            $userId = $this->wrapSaveUser($i);
            if (empty($userId)) {
                exit('not found user');
            }

            $d = $this->getDataSaveCheckout($products, $i);

            $sessionId = 'demo123';
            [
                // 'productsDataAndTotalAmount' => $productsDataAndTotalAmount,
                // 'checkout' => $checkout,
                'objCheckout' => $objCheckout
            ] = app(ProductService::class)->saveCheckout($d, $userId, $sessionId); // TODO DI
            if (empty($objCheckout->id)) {
                exit('sth wrong with create checkout');
            }
        }

        /* --------------------- */
        /* ---orders----------- */
        /* --------------------- */
        /*
        $uu =  User::where('email', '=', $emailClient)->first();
        $order1 = [
            'qty' => 2,
            'user_id' => $uu->id,
            'product_id' =>  $products[0]->id
        ];

        $order2 = [
            'qty' => 7,
            'user_id' => $uu->id,
            'product_id' =>  $products[2]->id
        ];

        Order::create($order1);
        Order::create($order2);
        */
        return 0;
    }
}
