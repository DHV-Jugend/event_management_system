CREATE TABLE `###EVENT_REGISTRATION_TABLE###` (
  ID       BIGINT(20) AUTO_INCREMENT,
  user_id  BIGINT(20) NOT NULL,
  event_id BIGINT(20) NOT NULL,
  data     JSON,
  deleted  TINYINT(4),
  UNIQUE KEY unique_registration (user_id, event_id),
  PRIMARY KEY (ID)
) ###CHARSET###;