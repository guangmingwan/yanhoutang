mysql -uroot -pinchoo -hdb < init.sql
mysql -uroot -pinchoo -hdb yanhoutang_m2 < yanhoutang_m2.sql
php bin/magento migrate:settings --auto --reset vendor/magento/data-migration-tool/etc/opensource-to-opensource/1.9.3.8/config.xml
php bin/magento migrate:data --auto --reset vendor/magento/data-migration-tool/etc/opensource-to-opensource/1.9.3.8/config.xml
