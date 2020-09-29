UPDATE {db_table_prefix}settings SET value='{admin_email}' WHERE varname='admin_email' OR varname='email_from_address';

INSERT INTO {db_table_prefix}users (id, login, pass, password_salt, password_hash, cookie_salt, user_email, logged_last, logged_ip, logged_host, created, timezone, password_verify, user_first_name, user_last_name, user_organization, user_address1, user_address2, user_city, user_state, user_country, user_zip, user_phone, user_fax, user_comment, import_id) VALUES
(1, '{admin_email}', '{admin_pass}', '{admin_password_salt}', 'sha256', '', '{admin_email}', NULL, '', '', NOW(), '', '', '', '', '', '', '', '', '', '', '', '', '', '', 0);

INSERT INTO {db_table_prefix}users_groups_lookup (user_id, group_id) VALUES (1,1);

INSERT INTO {db_table_prefix}users_groups_permissions_lookup SELECT 1, id FROM {db_table_prefix}users_permissions;

INSERT INTO {db_table_prefix}users_groups_permissions_lookup (group_id, permission_id) VALUES
(2, 'admin_login'),
(3, 'admin_login'),
(4, 'user_login'),
(4, 'user_user'),
(4, 'user_advertiser');

INSERT INTO {db_table_prefix}languages (languageid, title, languagecode, charset, textdirection, ismaster, isdefault, decimalseperator, thousandseperator, decimalplaces, currency_prefix, currency_suffix, date_override, time_override, locale, active) VALUES
(1, 'English', 'en-us', 'UTF-8', 'ltr', 0, 1, '.', ',', 2, '$', '', '', '', '', 1);

INSERT INTO {db_table_prefix}menu_links (id, title, link, ordering, logged_in, logged_out, sitemap, parent_id) VALUES
(1, 'Home', 'index.php', 1, 1, 1, 0, null),
(2, 'Browse', '', 5, 1, 1, 0, null),
(3, 'Browse Categories', 'browse_categories.php', 5, 1, 1, 1, 2),
(4, 'Browse Locations', 'browse_locations.php', 10, 1, 1, 1, 2),
(5, 'Events', 'events_calendar.php', 15, 1, 1, 1, 2),
(6, 'Advertise', 'compare.php', 25, 1, 1, 1, null),
(7, 'Advanced Search', 'search.php', 40, 1, 1, 1, null),
(8, 'Submit Contact Request', 'members/user_contact_requests.php?action=add', 45, 1, 1, 0, null),
(9, 'Contact Us', 'contact.php', 50, 1, 1, 1, null),
(10, 'Sitemap', 'sitemap.php', 55, 1, 1, 0, null);

INSERT INTO {db_table_prefix}users_groups (id, name, description) VALUES
(1, 'Administrator', 'Administrator with full rights to every section.'),
(2, 'Super Manager', 'Manager with high level of access.'),
(3, 'Manager', 'Administrator with limited access.'),
(4, 'Registered User', 'User with an account but no administrator level access.'),
(5, 'Awaiting Email Confirmation', 'Users who have attempted to register but have not yet confirmed their email address.');