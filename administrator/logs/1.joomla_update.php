#
#<?php die('Forbidden.'); ?>
#Date: 2021-10-06 07:56:08 UTC
#Software: Joomla Platform 13.1.0 Stable [ Curiosity ] 24-Apr-2013 00:00 GMT

#Fields: datetime	priority clientip	category	message
2021-10-06T07:56:08+00:00	INFO ::1	update	Update started by user Super User (31). Old version is 3.9.15.
2021-10-06T07:56:09+00:00	INFO ::1	update	Downloading update file from https://downloads.joomla.org/cms/joomla3/3-10-2/Joomla_3.10.2-Stable-Update_Package.zip.
2021-10-06T07:56:16+00:00	INFO ::1	update	File Joomla_3.10.2-Stable-Update_Package.zip downloaded.
2021-10-06T07:56:16+00:00	INFO ::1	update	Starting installation of new version.
2021-10-06T07:56:30+00:00	INFO ::1	update	Finalising installation.
2021-10-06T07:56:30+00:00	INFO ::1	update	Ran query from file 3.9.16-2020-02-15. Query text: ALTER TABLE `#__categories` MODIFY `description` mediumtext;.
2021-10-06T07:56:30+00:00	INFO ::1	update	Ran query from file 3.9.16-2020-02-15. Query text: ALTER TABLE `#__categories` MODIFY `params` text;.
2021-10-06T07:56:30+00:00	INFO ::1	update	Ran query from file 3.9.16-2020-02-15. Query text: ALTER TABLE `#__fields` MODIFY `default_value` text;.
2021-10-06T07:56:30+00:00	INFO ::1	update	Ran query from file 3.9.16-2020-02-15. Query text: ALTER TABLE `#__fields_values` MODIFY `value` text;.
2021-10-06T07:56:30+00:00	INFO ::1	update	Ran query from file 3.9.16-2020-02-15. Query text: ALTER TABLE `#__finder_links` MODIFY `description` text;.
2021-10-06T07:56:30+00:00	INFO ::1	update	Ran query from file 3.9.16-2020-02-15. Query text: ALTER TABLE `#__modules` MODIFY `content` text;.
2021-10-06T07:56:30+00:00	INFO ::1	update	Ran query from file 3.9.16-2020-02-15. Query text: ALTER TABLE `#__ucm_content` MODIFY `core_body` mediumtext;.
2021-10-06T07:56:30+00:00	INFO ::1	update	Ran query from file 3.9.16-2020-02-15. Query text: ALTER TABLE `#__ucm_content` MODIFY `core_params` text;.
2021-10-06T07:56:31+00:00	INFO ::1	update	Ran query from file 3.9.16-2020-02-15. Query text: ALTER TABLE `#__ucm_content` MODIFY `core_images` text;.
2021-10-06T07:56:31+00:00	INFO ::1	update	Ran query from file 3.9.16-2020-02-15. Query text: ALTER TABLE `#__ucm_content` MODIFY `core_urls` text;.
2021-10-06T07:56:31+00:00	INFO ::1	update	Ran query from file 3.9.16-2020-02-15. Query text: ALTER TABLE `#__ucm_content` MODIFY `core_metakey` text;.
2021-10-06T07:56:31+00:00	INFO ::1	update	Ran query from file 3.9.16-2020-02-15. Query text: ALTER TABLE `#__ucm_content` MODIFY `core_metadesc` text;.
2021-10-06T07:56:31+00:00	INFO ::1	update	Ran query from file 3.9.16-2020-03-04. Query text: ALTER TABLE `#__users` DROP INDEX `username`;.
2021-10-06T07:56:31+00:00	INFO ::1	update	Ran query from file 3.9.16-2020-03-04. Query text: ALTER TABLE `#__users` ADD UNIQUE INDEX `idx_username` (`username`);.
2021-10-06T07:56:31+00:00	INFO ::1	update	Ran query from file 3.9.19-2020-05-16. Query text: ALTER TABLE `#__ucm_content` MODIFY `core_title` varchar(400) NOT NULL DEFAULT '.
2021-10-06T07:56:31+00:00	INFO ::1	update	Ran query from file 3.9.19-2020-06-01. Query text: INSERT INTO `#__postinstall_messages` (`extension_id`, `title_key`, `description.
2021-10-06T07:56:31+00:00	INFO ::1	update	Ran query from file 3.9.21-2020-08-02. Query text: INSERT INTO `#__postinstall_messages` (`extension_id`, `title_key`, `description.
2021-10-06T07:56:31+00:00	INFO ::1	update	Ran query from file 3.9.22-2020-09-16. Query text: INSERT INTO `#__postinstall_messages` (`extension_id`, `title_key`, `description.
2021-10-06T07:56:31+00:00	INFO ::1	update	Ran query from file 3.9.26-2021-04-07. Query text: INSERT INTO `#__postinstall_messages` (`extension_id`, `title_key`, `description.
2021-10-06T07:56:31+00:00	INFO ::1	update	Ran query from file 3.9.27-2021-04-20. Query text: INSERT INTO `#__postinstall_messages` (`extension_id`, `title_key`, `description.
2021-10-06T07:56:31+00:00	INFO ::1	update	Ran query from file 3.10.0-2020-08-10. Query text: ALTER TABLE `#__template_styles` ADD COLUMN `inheritable` tinyint NOT NULL DEFAU.
2021-10-06T07:56:31+00:00	INFO ::1	update	Ran query from file 3.10.0-2020-08-10. Query text: ALTER TABLE `#__template_styles` ADD COLUMN `parent` varchar(50) DEFAULT '';.
2021-10-06T07:56:31+00:00	INFO ::1	update	Ran query from file 3.10.0-2021-05-28. Query text: INSERT INTO `#__extensions` (`package_id`, `name`, `type`, `element`, `folder`, .
2021-10-06T07:56:31+00:00	INFO ::1	update	Deleting removed files and folders.
2021-10-06T07:56:33+00:00	INFO ::1	update	Cleaning up after installation.
2021-10-06T07:56:33+00:00	INFO ::1	update	Update to version 3.10.2 is complete.
2021-10-28T07:29:45+00:00	INFO ::1	update	Update started by user Super User (376). Old version is 3.10.2.
2021-10-28T07:29:45+00:00	INFO ::1	update	Downloading update file from https://downloads.joomla.org/cms/joomla3/3-10-3/Joomla_3.10.3-Stable-Update_Package.zip.
2021-10-28T07:29:53+00:00	INFO ::1	update	File Joomla_3.10.3-Stable-Update_Package.zip downloaded.
2021-10-28T07:29:53+00:00	INFO ::1	update	Starting installation of new version.
2021-10-28T07:30:07+00:00	INFO ::1	update	Finalising installation.
2021-10-28T07:30:07+00:00	INFO ::1	update	Deleting removed files and folders.
2021-10-28T07:30:08+00:00	INFO ::1	update	Cleaning up after installation.
2021-10-28T07:30:08+00:00	INFO ::1	update	Update to version 3.10.3 is complete.
