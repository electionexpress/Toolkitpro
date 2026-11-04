<?php
declare(strict_types=1);
return ['name'=>getenv('APP_NAME') ?: 'ToolkitPro','env'=>getenv('APP_ENV') ?: 'production','debug'=>filter_var(getenv('APP_DEBUG') ?: false,FILTER_VALIDATE_BOOLEAN),'url'=>getenv('APP_URL') ?: 'http://localhost','locale'=>getenv('APP_LOCALE') ?: 'bn','timezone'=>getenv('APP_TIMEZONE') ?: 'Asia/Dhaka'];