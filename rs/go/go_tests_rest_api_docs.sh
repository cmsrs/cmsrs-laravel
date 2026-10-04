echo -n "" > storage/app/rest-api-docs.txt
./vendor/bin/phpunit
cat storage/app/rest-api-docs.txt
