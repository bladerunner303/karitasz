update system_user 
set
status = 'INAKTIV',
modifier = 'SYSTEM',
modified = current_timestamp
where name = @name COLLATE utf8_hungarian_ci
;

delete from session
where user_name = @name COLLATE utf8_hungarian_ci
;