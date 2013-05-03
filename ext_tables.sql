#
# Table structure for table 'be_users'
#
CREATE TABLE be_users (
	tx_authenticator_secret tinytext,
	tx_authenticator_enabled tinyint(1) DEFAULT '0' NOT NULL
);



#
# Table structure for table 'fe_users'
#
CREATE TABLE fe_users (
		tx_authenticator_secret tinytext,
		tx_authenticator_enabled tinyint(1) DEFAULT '0' NOT NULL
);