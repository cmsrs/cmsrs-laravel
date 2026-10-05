# before running this script - comment the following line
# return; in add method of Support/RestDoc.php file to generating rest api docs on every test run

echo -n "" > storage/app/rest-api-docs.txt
./vendor/bin/phpunit
cat storage/app/rest-api-docs.txt
