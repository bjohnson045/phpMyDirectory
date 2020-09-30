<?php
$db->Execute("DELETE FROM ".T_SETTINGS." WHERE grouptitle = 'yelp';");
$db->Execute("INSERT INTO ".T_SETTINGS." (varname, grouptitle, value, optioncode_type, validationcode) VALUES
('yelp_consumer_key','yelp_reviews','','input',''),
('yelp_consumer_secret','yelp_reviews','','input',''),
('yelp_token','yelp_reviews','','input',''),
('yelp_token_secret','yelp_reviews','','input','')
");
?>