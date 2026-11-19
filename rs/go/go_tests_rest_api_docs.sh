echo -n "" > storage/app/rest-api-docs.txt
REST_DOC=1  ./vendor/bin/phpunit  
#REST_DOC=1  ./vendor/bin/phpunit --filter=test_it_will_update_page_with_images_position_and_without_upload_new_imgs_case2_docs
# --debug
cat storage/app/rest-api-docs.txt
echo -n "" > storage/app/rest-api-docs.txt
