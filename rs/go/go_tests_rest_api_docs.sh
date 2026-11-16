echo -n "" > storage/app/rest-api-docs.txt
REST_DOC=1  ./vendor/bin/phpunit  
# --debug
cat storage/app/rest-api-docs.txt
echo -n "" > storage/app/rest-api-docs.txt
