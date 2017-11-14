CREATE TABLE `###EVENT_REGISTRATION_TABLE###` (
  ID              BIGINT(20) AUTO_INCREMENT,
  user_id         BIGINT(20) NOT NULL,
  event_id        BIGINT(20) NOT NULL,
  fum_aircraft    VARCHAR(255),
  fum_search_ride VARCHAR(255),
  fum_offer_ride  VARCHAR(255),
  deleted         TINYINT(4),
  PRIMARY KEY (ID)
) ###CHARSET###;