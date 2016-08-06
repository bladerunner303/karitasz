DELIMITER $$

drop procedure if exists init_data
$$

CREATE PROCEDURE init_data ()
BEGIN

    SET SQL_SAFE_UPDATES = 0;

/*
	SET FOREIGN_KEY_CHECKS = 0; 
	truncate table  db_version;
    truncate table history;
    truncate table link;
	truncate table  session;
	truncate table watcher;
    truncate table comment;
	truncate table issue;
    truncate table  user;
	SET FOREIGN_KEY_CHECKS = 1; 
	*/
    
     delete from history;
    delete from link;
	delete from  session;
	delete from watcher;
    delete from comment;
    delete from issue;
    delete from  user;
	delete from  db_version;
    
INSERT INTO db_version VALUES(1,'2015-02-11 22:40:06');
INSERT INTO db_version VALUES(2,'2015-04-07 22:40:06');
INSERT INTO db_version VALUES(3,'2015-05-12 19:25:18');
INSERT INTO db_version VALUES(4,'2015-07-11 12:05:50');
INSERT INTO db_version values(-1,current_timestamp);

INSERT INTO user VALUES('e26cb7f0-57e2-4eb7-8094-67ec97f349be','ACTIVE','admin','8c6976e5b5410415bde908bd4dee15dfb167a9c873fc4bb8a81f6f2ab448a918','admin@admin.org','2015-02-11 22:40:08',NULL,'2015-02-11 22:40:07','admin','2015-02-11 22:40:07');
INSERT INTO user VALUES('365c5593-4750-4d6c-b121-914a64659648','ACTIVE','unit_php','8c6976e5b5410415bde908bd4dee15dfb167a9c873fc4bb8a81f6f2ab448a918','levente.lacz@gmail.com',NULL,NULL,'2015-02-11 22:40:08','admin','2015-02-11 22:40:08');				

INSERT INTO issue VALUES(1,'Folyamatban','Első issue','Első projekt','365c5593-4750-4d6c-b121-914a64659648','2-Normál','unit_php','2015-02-14 21:07:15','unit_php','2015-02-14 21:07:15','Első issue részletesebb leírása, hogy mit kell csinálni','2015-02-14 21:07:15',NULL,'Fejlesztés',NULL);
INSERT INTO issue VALUES(2,'Befejezett','Második issue','Első projekt','e26cb7f0-57e2-4eb7-8094-67ec97f349be','3-Magas','unit_php','2015-02-14 21:17:15','unit_php','2015-02-14 21:17:15','Második issue részletesebb leírása, hogy mit kell csinálni','2015-02-14 21:17:15',NULL,'Hiba','1.1');
INSERT INTO issue VALUES(3,'Folyamatban','Harmadik issue','Egyéb projekt','365c5593-4750-4d6c-b121-914a64659648','3-Magas','unit_php','2015-02-14 21:17:15','unit_php','2015-02-14 21:17:15','Harmadik issue részletesebb leírása, hogy mit kell csinálni','2015-02-14 21:17:15',NULL,'Fejlesztés',NULL);

INSERT INTO comment VALUES('9226db4b-1cda-4c51-8242-d95d269f1a66',1,'unit_php','2015-02-14 21:07:16','Ez egy commentem');
INSERT INTO comment VALUES('0317bc4b-9e2b-4d19-8774-027e8f7ef9bc',1,'unit_php','2015-02-15 01:07:16','Ez másik commentem');
INSERT INTO comment VALUES('f08510f8-f004-46e1-a711-c55a791380ec',2,'admin','2015-01-15 01:07:16','Ez kész');

INSERT INTO watcher VALUES('9053139365139127414',1,'365c5593-4750-4d6c-b121-914a64659648');
INSERT INTO watcher VALUES('-3797032441395037911',1,'e26cb7f0-57e2-4eb7-8094-67ec97f349be');

INSERT INTO link VALUES(3,2,'admin','2015-07-11 12:16:16');

INSERT INTO history VALUES('16ddae96-8b38-49a7-9625-36bc78fb2a82','1','unit_php','2015-02-15 01:04:16','Unit teszt check',NULL,'almafa');
INSERT INTO history VALUES('d8063803-837c-4c98-96ba-fa40648dd280','1','unit_php','2015-02-15 01:07:16','Unit teszt check 2','almafa','körtefa');
INSERT INTO history VALUES('b2afe3f4-b7ba-4c28-a9a9-1bd462b72d7e','2','unit_php','2015-02-15 01:07:16','Unit teszt check 3','almafa','körtefa');
    
END