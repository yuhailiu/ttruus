<?php
echo 'locale test';

ini_set('intl.default_locale', 'de-DE');
echo locale_get_default();
echo '; ';
locale_set_default('fr');

echo locale_get_default();
echo Locale::getDefault();
ini_set('intl.default_locale', 'de-DE');
echo 'Locale::getDefault()';
echo '; ';
Locale::setDefault('fr');
echo Locale::getDefault();

?>