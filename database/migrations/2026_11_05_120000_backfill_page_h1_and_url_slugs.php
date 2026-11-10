<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        $titles = DB::table('translates')
            ->where('column', 'title')
            ->whereNotNull('page_id')
            ->whereNotNull('value')
            ->orderBy('page_id')
            ->orderBy('lang')
            ->get(['page_id', 'lang', 'value']);

        foreach ($titles as $title) {
            foreach (['h1' => $title->value, 'url_slug' => $this->uniqueSlug((string) $title->value, (string) $title->lang)] as $column => $value) {
                $exists = DB::table('translates')->where('page_id', $title->page_id)
                    ->where('lang', $title->lang)->where('column', $column)->exists();
                if (! $exists) {
                    DB::table('translates')->insert([
                        'page_id' => $title->page_id,
                        'lang' => $title->lang,
                        'column' => $column,
                        'value' => $value,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }
    }

    private function uniqueSlug(string $value, string $lang): string
    {
        $base = Str::slug($value, '-') ?: 'page';
        $slug = $base;
        $suffix = 2;
        while (DB::table('translates')->where('column', 'url_slug')->where('lang', $lang)->where('value', $slug)->exists()) {
            $slug = $base.'-'.$suffix++;
        }

        return $slug;
    }

    public function down(): void
    {
        // Keep content intact on rollback; these are translation data, not schema columns.
    }
};
