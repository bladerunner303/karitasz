delete from code where id <> 0;
insert into code values ('Y', 'boolean', 'Igen', 'SYSTEM', current_timestamp);
insert into code values ('N', 'boolean', 'Nem', 'SYSTEM', current_timestamp);

insert into code values ('FELAJANLO', 'customer_type', 'Felajánló', 'SYSTEM', current_timestamp);
insert into code values ('KERVENYEZO', 'customer_type', 'Kérvényező', 'SYSTEM', current_timestamp);

insert into code values ('TILTOTT', 'customer_qualification', 'Tiltott', 'SYSTEM', current_timestamp);
insert into code values ('NORMAL', 'customer_qualification', 'Normál', 'SYSTEM', current_timestamp);
insert into code values ('KIEMELT', 'customer_qualification', 'Kiemelt', 'SYSTEM', current_timestamp);

insert into code values ('HAZAS', 'marital_status', 'Házas', 'SYSTEM', current_timestamp);
insert into code values ('EGYEDULALLO', 'marital_status', 'Egyedülálló', 'SYSTEM', current_timestamp);
insert into code values ('ELLETTARS', 'marital_status', 'Élettársi kapcsolatban', 'SYSTEM', current_timestamp);
insert into code values ('OZVEGY', 'marital_status', 'Özvegy', 'SYSTEM', current_timestamp);
insert into code values ('ELVALT', 'marital_status', 'Elvált', 'SYSTEM', current_timestamp);

insert into code values ('FM_GYERMEK', 'family_member', 'Gyermek', 'SYSTEM', current_timestamp);
insert into code values ('FM_UNOKA', 'family_member', 'Unoka', 'SYSTEM', current_timestamp);
insert into code values ('FM_HAZASTARS', 'family_member', 'Házastárs', 'SYSTEM', current_timestamp);
insert into code values ('FM_ELETTARS', 'family_member', 'Élettárs', 'SYSTEM', current_timestamp);
insert into code values ('FM_FOGY_ELTARTOTT', 'family_member', 'Fogyatékos eltartott', 'SYSTEM', current_timestamp);
 
insert into code values ('FELAJANLAS', 'operation_type', 'Felajánlás', 'SYSTEM', current_timestamp);
insert into code values ('KERVENYEZES', 'operation_type', 'Kérvényezés', 'SYSTEM', current_timestamp);

insert into code values ('ROGZITETT', 'operation_status', 'Rögzített', 'SYSTEM', current_timestamp);
insert into code values ('FOLYAMATBAN', 'operation_status', 'Folyamatban', 'SYSTEM', current_timestamp);
insert into code values ('BEFEJEZETT', 'operation_status', 'Befejezett', 'SYSTEM', current_timestamp);

insert into code values ('ROGZITETT_TRANSPORT', 'transport_status', 'Rögzített', 'SYSTEM', current_timestamp);
insert into code values ('KIADOTT_TRANSPORT', 'transport_status', 'Kiadott', 'SYSTEM', current_timestamp);
insert into code values ('BEFEJEZETT_TRANSPORT', 'transport_status', 'Befejezett', 'SYSTEM', current_timestamp);
insert into code values ('SIKERTELEN_TRANSPORT', 'transport_status', 'Sikertelen', 'SYSTEM', current_timestamp);

insert into code values ('AKTIV', 'status', 'Aktív', 'SYSTEM', current_timestamp);
insert into code values ('INAKTIV', 'status', 'Inaktív', 'SYSTEM', current_timestamp);

insert into code values ('NAGYCSALADOS', 'neediness_level', 'Nagycsaládos', 'SYSTEM', current_timestamp);
insert into code values ('HAJLEKTALAN', 'neediness_level', 'Hajléktalan', 'SYSTEM', current_timestamp);
insert into code values ('KISNYUGDIJAS', 'neediness_level', 'Kisnyugdíjas', 'SYSTEM', current_timestamp);
insert into code values ('LETMINIMUM', 'neediness_level', 'Létminimum közelében élő', 'SYSTEM', current_timestamp);
insert into code values ('HATRANYOS_SZOC', 'neediness_level', 'Hátrányos szociális helyzet', 'SYSTEM', current_timestamp);

insert into code values ('PLEBANIA', 'sender', 'Plébánia', 'SYSTEM', current_timestamp);
insert into code values ('INTERNET', 'sender', 'Internet', 'SYSTEM', current_timestamp);
insert into code values ('ISMEROS', 'sender', 'Ismerős', 'SYSTEM', current_timestamp);
insert into code values ('EMMI', 'sender', 'EMMI', 'SYSTEM', current_timestamp);
insert into code values ('KARITASZ', 'sender', 'Katolikus karitasz', 'SYSTEM', current_timestamp);

insert into code values ('MUBER', 'income_type', 'Munkabér', 'SYSTEM', current_timestamp);
insert into code values ('GYES', 'income_type', 'Gyes', 'SYSTEM', current_timestamp);
insert into code values ('NYUGDIJ', 'income_type', 'Nyugdíj', 'SYSTEM', current_timestamp);
insert into code values ('SEGELY', 'income_type', 'Segély', 'SYSTEM', current_timestamp);

insert into code values ('NAME_CHANGE', 'customer_history_data_type', 'Név változás', 'SYSTEM', current_timestamp);
insert into code values ('ADDRESS_CHANGE', 'customer_data_type', 'Cím változás', 'SYSTEM', current_timestamp);
insert into code values ('EMAIL_CHANGE', 'customer_history_data_type', 'Email cím változás', 'SYSTEM', current_timestamp);
insert into code values ('PHONE_CHANGE', 'customer_history_data_type', 'Telefonszám változás', 'SYSTEM', current_timestamp);
insert into code values ('PHONE2_CHANGE', 'customer_history_data_type', 'Másodlagos telefonszám változás', 'SYSTEM', current_timestamp);
insert into code values ('STATUS_CHANGE', 'customer_data_type', 'Státusz változás', 'SYSTEM', current_timestamp);
insert into code values ('MARTIAL_STAT_CHANGE', 'customer_data_type', 'Családi állapot változás', 'SYSTEM', current_timestamp);
insert into code values ('DESCRIPTION_CHANGE', 'customer_history_data_type', 'Megjegyzés változás', 'SYSTEM', current_timestamp);
insert into code values ('QUALIFICATION_CHANGE', 'customer_data_type', 'Minősítés változás', 'SYSTEM', current_timestamp);
insert into code values ('ADD_CONTACT_CHANGE', 'customer_history_data_type', 'További kapcsolattartó változás', 'SYSTEM', current_timestamp);
insert into code values ('TAX_NUMBER_CHANGE', 'customer_history_data_type', 'Adószám változás', 'SYSTEM', current_timestamp);
insert into code values ('TB_NUMBER_CHANGE', 'customer_history_data_type', 'Taj szám változás', 'SYSTEM', current_timestamp);
insert into code values ('BIRTH_DATA_CHANGE', 'customer_history_data_type', 'Születési adatok változás', 'SYSTEM', current_timestamp);
insert into code values ('MEMBER_NEW', 'customer_history_data_type', 'Felvett családtag', 'SYSTEM', current_timestamp);
insert into code values ('MEMBER_MODIFY', 'customer_history_data_type', 'Módosult családtag', 'SYSTEM', current_timestamp);
insert into code values ('MEMBER_REMOVE', 'customer_history_data_type', 'Kitörölt családtag', 'SYSTEM', current_timestamp);


/*
insert into code values ('GT_KOMOD', 'goods_type', 'Komód', 'SYSTEM', current_timestamp);
insert into code values ('GT_TV', 'goods_type', 'Televízió', 'SYSTEM', current_timestamp);
insert into code values ('GT_HUTO', 'goods_type', 'Hűtőszekrény', 'SYSTEM', current_timestamp);
insert into code values ('GT_FELNOTT_AGY', 'goods_type', 'Felnőtt ágy', 'SYSTEM', current_timestamp);
insert into code values ('GT_FELNOTT_AGY_2', 'goods_type', 'Felnőtt ágy 2 személyes', 'SYSTEM', current_timestamp);
insert into code values ('GT_GYERMEK_AGY', 'goods_type', 'Gyermek ágy', 'SYSTEM', current_timestamp);
insert into code values ('GT_BABA_AGY', 'goods_type', 'Baba ágy', 'SYSTEM', current_timestamp);
insert into code values ('GT_SZEKRENY', 'goods_type', 'Szekrény', 'SYSTEM', current_timestamp);
insert into code values ('GT_IRO_ASZTAL', 'goods_type', 'Író asztal', 'SYSTEM', current_timestamp);
insert into code values ('GT_ETKEZO_ASZTAL', 'goods_type', 'Étkező asztal', 'SYSTEM', current_timestamp);
insert into code values ('GT_DOHANYZO_ASZTAL', 'goods_type', 'Dohányzó asztal', 'SYSTEM', current_timestamp);
*/
-- user
delete from system_user where id <> '0';
INSERT INTO `system_user` (`id`, `status`, `name`, `password`, `email`, `last_login`, `last_logout`, `last_password_change`, `modifier`, `modified`) VALUES
('a', 'AKTIV', 'LEVI', '64f5afe732fa4a8255747b150298df58db4330322c2928a33f6bfc6fb02c0756', 'xxx@xxx.hu', '2016-06-15 08:13:14', '2016-06-13 16:24:20', '2016-06-05 01:20:50', 'SYSTEM', '2016-05-31 08:44:13');
INSERT INTO `system_user` (`id`, `status`, `name`, `password`, `email`, `last_login`, `last_logout`, `last_password_change`, `modifier`, `modified`) VALUES
('b', 'AKTIV', 'JERNE', '64f5afe732fa4a8255747b150298df58db4330322c2928a33f6bfc6fb02c0756', 'xxx@xxx.hu', '2016-06-15 08:13:14', '2016-06-13 16:24:20', '2016-06-05 01:20:50', 'SYSTEM', '2016-05-31 08:44:13');
INSERT INTO `system_user` (`id`, `status`, `name`, `password`, `email`, `last_login`, `last_logout`, `last_password_change`, `modifier`, `modified`) VALUES
('c', 'AKTIV', 'MARTA', '64f5afe732fa4a8255747b150298df58db4330322c2928a33f6bfc6fb02c0756', 'xxx@xxx.hu', '2016-06-15 08:13:14', '2016-06-13 16:24:20', '2016-06-05 01:20:50', 'SYSTEM', '2016-05-31 08:44:13');

