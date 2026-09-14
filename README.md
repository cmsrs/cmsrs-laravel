<p align="center">
    <img src="http://www.cmsrs.pl/images/cms/logo_cmsrs.png"><br/>
    Modern CMS for websites and galleries, and even stores, without the chaos.
</p>
</br>
<p align="center">
<a href="https://www.php.net/"><img src="https://img.shields.io/badge/php-8.5-yellowgreen"></a>
<a href="https://github.com/laravel/laravel"><img src="https://img.shields.io/badge/Laravel-13-yellowgreen"></a>
<a href="https://www.cmsrs.pl/en/cms/cmsrs/coverage-test"><img src="https://img.shields.io/badge/coverage-90%25-yellowgreen"></a>
<a href="#"><img src="https://img.shields.io/badge/PHPStan-8-yellowgreen"></a>
<a href="https://github.com/cmsrs/cmsrs-laravel/blob/master/LICENSE"><img src="https://img.shields.io/badge/license-MIT-green"></a>
</p>
</br>
</br>

<p>CmsRS is a next-generation <a href="https://en.wikipedia.org/wiki/Content_management_system" target="_blank">CMS</a> for websites, galleries, and online stores, built as a clean alternative to bloated CMS solutions.</p>

<p>It uses Laravel as the backend/API and Vue.js as the administration panel, with a flexible frontend that can be built with Blade or used headlessly with Nuxt.</p>

</br>
<p>
    <a target="_blank" href="https://www.cmsrs.pl/en/cms/cmsrs/cmsrs-installation">🚀 Install</a> | 
    <a target="_blank" href="https://www.cmsrs.pl/en/cms/cmsrs/demo-version">🌐 Demo</a> | 
    <a target="_blank" href="https://www.cmsrs.pl/en/cms/cmsrs/documentation">📚 Documentation</a> | 
    <a target="_blank" href="https://www.cmsrs.pl/en/cms/cmsrs/rest-api">🔌 REST API</a> |    
    <a target="_blank" href="https://www.cmsrs.pl/en/cms/cmsrs/about-cmsrs">ℹ️ About cmsRS</a>
</p>
</br>


## cmsRS ECOSYSTEM:

![cmsRS ecosystem diagram](./rs/readme_imgs/cmsrs_ecosystem.svg)

<ul>
<li>(1) <a href="https://github.com/cmsrs/cmsrs-laravel" target="_blank">GitHub – cmsrs-laravel - Laravel backend / API</a></li>
<li>(2) <a href="https://github.com/cmsrs/cmsrs-vuejs" target="_blank">GitHub – cmsrs-vuejs - Vue.js administration panel</a></li>
<li>(3) <a href="https://github.com/cmsrs/cmsrs-nuxt" target="_blank">GitHub – cmsrs-nuxt - Nuxt frontend </a></li>
</ul>

</br>
</br>

<div>
    <img width="400" alt="admin panel - cmsRS" src="./rs/intro_imgs/s1.png">
</div>

</br>

## 🤔 WHY cmsRS?

Unlike traditional CMS platforms:
- Clear separation of concerns: Laravel backend/API, Vue.js administration panel, and flexible frontend layer
- Laravel-based API backend with Vue.js administration panel
- Flexible frontend approach: Blade-based rendering or headless frontend via REST API
- Predictable updates supported by automated testing
- Developer-friendly architecture based on modern standards
- Clean and logical database structure


## ✨ FEATURES

- ⚡ Modern Laravel API + Vue.js/Nuxt architecture
- 🌍 Multi-language support
- 📝 Content management system for pages and custom content
- 🧭 Flexible menu and navigation management
- 🖼️ Gallery system
- 🛒 Product catalog management
- 🔐 User authentication and access management
- 🧪 90% test coverage
- 🧠 Clean and predictable architecture


## REQUIRED PACKAGES

```php-cli``` – PHP command-line interface

```php-dom``` and ```php-xml``` – for XML parsing (used in layouts and configs)

```php-curl``` – for HTTP requests

```php-mysql``` – for MySQL database connection

```php-mbstring``` – for multibyte string support (required by PHPUnit and some packages)

```php-gd``` – for image processing (used in gallery, sliders, etc.)

Make sure all extensions match your installed PHP version (e.g., php8.5-mysql, php8.5-mbstring, etc.)

## INSTALLATION

Before running the script, make sure you have configured the database connection (host, database name, username, password, and port).

Run the following command to create the project:

```bash
composer create-project cmsrs/cmsrs-laravel
cd cmsrs-laravel
php artisan cmsrs:install
php artisan serve
```

Once the server is running, open your browser and navigate to:

```bash
http://127.0.0.1:8000
```

## RUN TESTS (RECOMMENDED)

* Prepare .env.testing file, and change db connection (DB_DATABASE should be different than the one in the .env file):


```bash
cp .env .env.testing 
```

* run tests: 

```bash
./vendor/bin/phpunit
```

## MANAGEMENT

* Go to the website http://127.0.0.1:8000/admin/

    log in as:

    username: adm@cmsrs.pl

    default password: cmsrs123

* Create main page (page type: **main_page**)

* Add menu
    
* Add pages   


## CLI COMMANDS 

* Create sitemap (it is recommended to put this command in the crontab file): 

```bash
php artisan cmsrs:create-site-map
```

* Create client user or edit password for user: 

```bash
php artisan cmsrs:create-client {user} {password}
```

* Change admin password:

```bash
php artisan cmsrs:change-admin-pass {new-password}
```

## 🌐 DEMO

- [Frontend](https://demo.cmsrs.pl)
- [Admin Panel](https://demo.cmsrs.pl/admin-demo/)
- [Headless / Nuxt](https://demo-headless.cmsrs.pl/)

## REPORTING ISSUES AND SUGGESTIONS

If you notice any problems or have ideas to improve the project, please use the [Issues](https://github.com/cmsrs/cmsrs-laravel/issues) section to let me know.
If you like it, give it a star!
Your support motivates me to keep improving the project. Thank you! :)

## CONTRIBUTING

Contributions are welcome!  
Feel free to open issues or submit pull requests.

## LICENSE

This project is licensed under the MIT License.
