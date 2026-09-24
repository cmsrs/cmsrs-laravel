<?php

declare(strict_types=1);

namespace App\Services\Cmsrs\Cms;

use App\Models\Cmsrs\Cms\Contact;

class ContactService
{
    /** @var array<int, string> */
    protected $fillable = [
        'email',
        'message',
    ];

    /** @var array<int, string> */
    public $columnsAllowedToSort = [
        'id',
        'email',
        'message',
        'created_at',
        'updated_at',
    ];

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getAllData()
    {
        $out = [];
        $contacts = Contact::All();
        foreach ($contacts as $contact) {
            $item = [];
            $item['id'] = $contact->id;
            $item['email'] = $contact->email;
            $item['message'] = $contact->message;
            $item['created_at_format'] = $contact->created_at
                ? $contact->created_at->format('Y-m-d H:i:s')
                : null;
            $out[] = $item;
        }

        return $out;
    }

    /**
     * @param  array<string, string>  $data
     *                                       only for demo purpose
     */
    public function wrapCreate(array $data): Contact
    {
        return Contact::create($data);
    }
}
