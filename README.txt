<h1 align="center">Opencart 3 Module Skeleton builder</h1>
<p align="center">Fast create module skeleton with admin config-forms</p>


## Requirements instruction
* PHP > 7.0
* php-zip extension
* php-json extension

## Installation instruction

Just upload on server and open in browser DOMAIN/OC-module-skeleton/builder.php 

## Usage

1. Rename config.example.json to config.json
2. Configure config.json with fields that you need.
3. Open DOMAIN/OC-module-skeleton/builder and click *Make skeleton*


## Configuration

Root parameter type mandatory and may be on of module types: advertise,analytics,captcha,dashboard,feed,fraud,module,payment,report,shipping,total.

Depending on the selected type, the builder create the corresponding skeleton.

In cionfig.example.json shown all supported input types and all supported field parameters ( On example not all parameters assigned to all fields).