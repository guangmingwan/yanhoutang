php bin/magento config:set web/unsecure/base_url http://m2.yanhoutang.com/
php bin/magento config:set web/secure/base_url https://m2.yanhoutang.com/
php bin/magento config:set web/secure/use_in_adminhtml 1
php bin/magento cache:clean
