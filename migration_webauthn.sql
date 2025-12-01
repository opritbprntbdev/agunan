-- Migration: Add WebAuthn credentials table for biometric login
-- Create table to store user biometric credentials
-- Note: Using MyISAM engine to match existing user table (no FK support)

CREATE TABLE IF NOT EXISTS `user_webauthn_credentials` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `credential_id` varchar(255) NOT NULL,
  `public_key` text NOT NULL,
  `counter` int(11) DEFAULT 0,
  `device_name` varchar(100) DEFAULT NULL,
  `user_agent` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `last_used_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `credential_id` (`credential_id`),
  KEY `idx_user_credential` (`user_id`, `credential_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
