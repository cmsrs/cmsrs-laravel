<?php

namespace Tests\Support;

class RestDoc
{
    private string $file;

    public function __construct()
    {
        $this->file = __DIR__.'/../../storage/app/rest-api-docs.txt';
    }

    public function clear(): void
    {
        file_put_contents($this->file, '');
    }

    public function add(
        string $method,
        string $url,
        mixed $input,
        mixed $output,
    ): void {
        $test = $this->getTestName();

        file_put_contents(
            $this->file,
            $this->format(
                $test,
                $method,
                $url,
                $input,
                $output,
            ),
            FILE_APPEND
        );
    }

    private function getTestName(): string
    {
        foreach (debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS) as $trace) {
            if (
                isset($trace['class'], $trace['function'])
                && str_starts_with($trace['function'], 'test')
            ) {
                return $trace['class'].'::'.$trace['function'];
            }
        }

        return 'Unknown test';
    }

    private function format(
        string $test,
        string $method,
        string $url,
        mixed $input,
        mixed $output,
    ): string {
        ob_start();

        echo str_repeat('=', 80).PHP_EOL;
        echo "TEST: {$test}".PHP_EOL;
        echo "METHOD: {$method}".PHP_EOL;
        echo "URL: {$url}".PHP_EOL;
        echo str_repeat('=', 80).PHP_EOL;
        echo PHP_EOL;

        if ($input !== null) {
            echo 'INPUT:'.PHP_EOL;
            print_r($input);
        }

        echo PHP_EOL.'OUTPUT:'.PHP_EOL;
        print_r($output);

        echo PHP_EOL;
        echo str_repeat('-', 80).PHP_EOL;
        echo PHP_EOL;

        return ob_get_clean();
    }
}
