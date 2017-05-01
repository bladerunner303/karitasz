drop schema karitasz;

CREATE SCHEMA karitasz DEFAULT CHARACTER SET utf8 COLLATE utf8_hungarian_ci ;

use karitasz;

-- My global sequence
CREATE TABLE sequence(
	current_value int not null AUTO_INCREMENT,
	constraint pk_sequence primary key(current_value)
)
engine innodb;

delimiter $$

CREATE FUNCTION sequence_nextval() RETURNS INT
    DETERMINISTIC
BEGIN
	
	insert into sequence values();
	RETURN LAST_INSERT_ID();
END $$

delimiter ;

create table code (
	id						varchar(20) not null, 
	code_type				varchar(35) not null, 
	code_value				varchar(50) not null, 
	modifier 				varchar(20) not null,
	modified 				timestamp 	not null default current_timestamp,
	constraint pk_code primary key (id)
)
engine innodb;

CREATE TABLE system_user (
	id 						varchar(36) not null,
	status 					varchar(20) not null, 
	name 					varchar(20) not null, 
	password 				varchar(105) not null, 
	email 					varchar(105), 
	roles					varchar(255),
	last_login 				timestamp null, 
	last_logout 			timestamp null,  
	last_password_change 	timestamp null, 
	modifier 				varchar(20)  not null, 
	modified 				timestamp not null default current_timestamp,
	constraint pk_user primary key (id),
	constraint ck_user_status check (status in (select id from code where code_type = 'status')),
	constraint uk_user_name unique (name)
)
engine innodb;

create table bad_login (
	id						varchar(36) not null,
	user_id					varchar(36) not null,
	created					timestamp not null default current_timestamp,
	status					varchar(20) not null,
	constraint pk_bad_login primary key (id),
	constraint fk_user_bad_login foreign key (user_id) references system_user(id),
	constraint ck_bad_login_status check (status in (select id from code where code_type = 'status'))
)
engine innodb;

CREATE TABLE session
(
	id 						varchar(36) not null,
	ip 						varchar(40) not null,
	browser_hash 			varchar(64) not null,
	user_name 				varchar(20) not null,
	user_id 				varchar(36) not null,
	user_roles				varchar(255) null,
	login_time 				timestamp not null default current_timestamp ,
	last_activity 			timestamp not null default current_timestamp ,
	logout_time 			timestamp null,
	constraint pk_session primary key (id),
	constraint fk_session_user foreign key (user_id) references system_user(id)
)
engine innodb;

create table log_page_load (
	id						varchar(36) not null,
	page					varchar(105)not null,
	page_load_time			timestamp not null default current_timestamp ,
	user_id					varchar(36) not null,
	user_name				varchar(20) not null,
	ip						varchar(64) not null,
	user_agent				varchar(255) null,
	constraint pk_log_page_load primary key(id),
	constraint fk_log_page_load_user foreign key (user_id) references system_user(id)
)
engine innodb;

create table log_control_run (
	id						varchar(36) not null,
	control					varchar(105)not null,
	control_begin_time		timestamp not null default current_timestamp ,
	control_end_time		timestamp not null default current_timestamp , 
	user_id					varchar(36) not null,
	user_name				varchar(20) not null,
	ip						varchar(64) not null,
	user_agent				varchar(255) null,
	constraint pk_log_control_run primary key(id),
	constraint fk_log_control_run_user foreign key (user_id) references system_user(id)

)
engine innodb;


create table customer
(
	id 							varchar(8) not null,
	surname						varchar(35) not null,
	forename					varchar(35)	null,
	customer_type				varchar(20) not null, 
	zip							varchar(4)	not null, 
	city						varchar(35) not null,
	street						varchar(35) not null,
	email						varchar(105)null, 
	phone						varchar(20) not null, 
	phone2						varchar(20) null,
	qualification				varchar(20)	not null,
	description					varchar(500)null,
	additional_contact			varchar(50) null, 
	additional_contact_phone 	varchar(20)	null,
	status 						varchar(20)	not null,
	marital_status				varchar(20) null,
	tax_number					varchar(20) null, 
	tb_number					varchar(20) null,
	birth_date					date 		null,
	birth_place					varchar(35) null,
	mother_name					varchar(50) null,
	creator 					varchar(20) not null,
	created 					timestamp 	not null default current_timestamp,
	modifier 					varchar(20) not null,
	modified 					timestamp 	not null default current_timestamp,
	constraint pk_customer primary key (id),
	constraint ck_customer_type check (customer_type in (select id from code where code_type = 'customer_type')),
	constraint ck_customer_qualification check (qualification in (select id from code where code_type = 'customer_qualification')),
	constraint ck_customer_marital_status check (marital_status in (select id from code where code_type = 'marital_status'))
)
engine innodb;

create table customer_history
(
	id						varchar(36) not null,  
	customer_id				varchar(10)	not null, 
	data_type				varchar(30) not null, 
	old_value				varchar(500)null, 
	new_value				varchar(500)null, 
	creator 				varchar(20) not null,
	created 				timestamp 	not null default current_timestamp,
	constraint pk_customer_history primary key (id),
	constraint fk_customer_customer_history foreign key (customer_id) references customer(id)
)
engine innodb;

create table customer_family_member
(
	id						varchar(36) not null,
	customer_id				varchar(10) not null,
	name					varchar(50) not null,
	family_member_customer varchar(10) null, 
	birth_date				date		null,
	family_member_type		varchar(20) not null,
	description				varchar(255)null,
	constraint pk_customer_family primary key(id),
	constraint fk_customer_customer_family foreign key (customer_id) references customer(id),
	constraint fk_customer_family_member_type foreign key (family_member_type) references code(id),
	constraint ck_customer_family_member_type check (family_member_type in (select id from code where code_type='family_member'))
)
engine innodb;

create table operation
(
	id						integer 	not null AUTO_INCREMENT,
	operation_type			varchar(20) not null,
	has_transport			varchar(1)  not null,
	is_wait_callback		varchar(1)  not null,
	customer_id				varchar(10)	not null, 
	status					varchar(20) not null, 
	description				varchar(500)null, 
	neediness_level			varchar(20) null,
	sender					varchar(20) null,
	income_type				varchar(20) null,
	income					integer		null,
	others_income			integer		null,
	creator 				varchar(20) not null,
	created 				timestamp 	not null default current_timestamp,
	modifier 				varchar(20) not null,
	modified 				timestamp 	not null default current_timestamp,
	last_status_changed		timestamp	not null default current_timestamp,
	last_status_changed_user varchar(20) not null,
	constraint pk_operation primary key (id),
	constraint fk_customer_operation foreign key (customer_id) references customer(id),
	constraint ck_operation_type check (operation_type in (select id from code where code_type = 'operation_type')),
	constraint ck_operation_status check (status in (select id from code where code_type = 'operation_status')),
	constraint ck_operation_neediness check (neediness_level in (select id from code where code_type='neediness_level')),
	constraint ck_operation_sender check (sender in (select id from code where code_type='sender')),
	constraint ck_operation_income_type check (income_type in (select id from code where code_type='income_type')),
 	constraint ck_operation_has_transponrt check (has_transport in ('Y', 'N'))
)
engine innodb;

create table operation_detail
(
	id						varchar(36) not null, 
	operation_id			integer		not null, 
	name					varchar(50) not null, 
	goods_type				varchar(20) not null,
	storehouse_id			varchar(36) null, 
	status					varchar(20) not null, 
	order_indicator			integer		not null,
	detail_id				varchar(36) null,
	constraint pk_operation_detail primary key (id),
	constraint fk_operation_operation_detail foreign key (operation_id) references operation(id),
	constraint ck_operation_detail_status check (status in (select id from code where code_type = 'operation_status'))
)
engine innodb;



create table transport
(
	id						integer 	not null AUTO_INCREMENT, 
	transport_date			date		not null, 
	status					varchar(20) not null, 
	creator 				varchar(20) not null,
	created 				timestamp 	not null default current_timestamp,
	modifier 				varchar(20) not null,
	modified 				timestamp 	not null default current_timestamp,
	constraint pk_transport primary key (id),
	constraint ck_transport_status check (status in (select id from code where code_type = 'transport_status'))
)
engine innodb;

create table transport_address
(
	id						varchar(36) not null, 
	operation_id			integer		not null, 
	transport_id			integer		not null, 
	zip						varchar(4)	not null, 
	city					varchar(35) not null,
	street					varchar(35) not null, 
	description				varchar(500) null,
	status					varchar(20) not null,
	order_indicator			integer		not null,
	constraint pk_transport_address primary key (id),
	constraint fk_transport_address_operation foreign key (operation_id) references operation(id),
	constraint fk_transport_address_transport foreign key (transport_id) references transport(id),
	constraint ck_transport_address_status check (status in (select id from code where code_type = 'transport_status'))
)
engine innodb;

create table transport_address_item
(
	id						varchar(36) not null,
	transport_address_id	varchar(36) not null,
	operation_detail_id		varchar(36) not null, 
	status					varchar(20) not null, 
	creator 				varchar(20) not null,
	created 				timestamp 	not null default current_timestamp,
	modifier 				varchar(20) not null,
	modified 				timestamp 	not null default current_timestamp,
	constraint pk_transport_address_item primary key (id),
	constraint fk_transport_address_item foreign key (transport_address_id) references transport_address(id),
	constraint fk_address_item_op_detail foreign key (operation_detail_id) references operation_detail(id),
	constraint ck_transport_address_item_status check (status in (select id from code where code_type = 'transport_status'))
)
engine innodb;

create table file_content (
	id 							varchar(36) not null,
	content						longblob	not null,
	constraint pk_file_content primary key (id)
)
engine innodb;

create table file_meta_data (
	id							varchar(36) not null,
	file_content_id				varchar(36) not null,
	name						varchar(105) not null, 
	extension					varchar(10) not null,
	size						integer	not null,
	creator 					varchar(20) not null,
	created						timestamp not null default current_timestamp,
	last_downloaded				timestamp null,
	constraint pk_file_meta_data primary key(id),
	constraint fk_file_md_content foreign key (file_content_id) references file_content(id)
)
engine innodb;

create table operation_file (
	operation_id				integer not null,
	file_meta_data_id			varchar(36) not null,
	constraint pk_operation_file primary key (operation_id, file_meta_data_id),
	constraint fk_file_md_operation_file foreign key (file_meta_data_id) references file_meta_data(id)
)
engine innodb;

create table operation_detail_file (
	operation_detail_id			varchar(36) not null, 
	file_meta_data_id			varchar(36) not null,
	constraint pk_operation_detail_file primary key (operation_detail_id, file_meta_data_id),
	constraint fk_file_md_operation_detail_file foreign key (file_meta_data_id) references file_meta_data(id)	
)
engine innodb;

INSERT INTO `system_user` (`id`, `status`, `name`, `password`, `email`, `last_login`, `last_logout`, `last_password_change`, `modifier`, `modified`) VALUES
('a', 'AKTIV', 'LEVI', '64f5afe732fa4a8255747b150298df58db4330322c2928a33f6bfc6fb02c0756', 'xxx@xxx.hu', '2016-06-15 08:13:14', '2016-06-13 16:24:20', '2016-06-05 01:20:50', 'SYSTEM', '2016-05-31 08:44:13');
INSERT INTO `system_user` (`id`, `status`, `name`, `password`, `email`, `last_login`, `last_logout`, `last_password_change`, `modifier`, `modified`) VALUES
('b', 'AKTIV', 'JERNE', '64f5afe732fa4a8255747b150298df58db4330322c2928a33f6bfc6fb02c0756', 'xxx@xxx.hu', '2016-06-15 08:13:14', '2016-06-13 16:24:20', '2016-06-05 01:20:50', 'SYSTEM', '2016-05-31 08:44:13');
INSERT INTO `system_user` (`id`, `status`, `name`, `password`, `email`, `last_login`, `last_logout`, `last_password_change`, `modifier`, `modified`) VALUES
('c', 'AKTIV', 'MARTA', '64f5afe732fa4a8255747b150298df58db4330322c2928a33f6bfc6fb02c0756', 'xxx@xxx.hu', '2016-06-15 08:13:14', '2016-06-13 16:24:20', '2016-06-05 01:20:50', 'SYSTEM', '2016-05-31 08:44:13');