--
-- Final view structure for view `device_event_info_with_block_list`
--

DROP VIEW IF EXISTS `device_event_info_with_block_list`;
DROP TABLE IF EXISTS `device_event_info_with_block_list`;

CREATE VIEW `device_event_info_with_block_list` AS SELECT 
  `device_event_info`.`id` AS `id`,
  `device_event_info`.`owner_name` AS `owner_name`,
  `device_event_info`.`device_os` AS `device_os`,
  `device_event_info`.`distr` AS `distr`,
  `device_event_info`.`domain` AS `domain`,
  `device_event_info`.`device_id` AS `device_id`,
  `device_event_info`.`registration_id` AS `registration_id`,
  `device_event_info`.`notification_type` AS `notification_type`,
  `device_event_info`.`oem_id` AS `oem_id`,
  `device_event_info`.`reg_ts` AS `reg_ts`,
  `device_event_info`.`update_ts` AS `update_ts`,
  `device_event_info`.`ver` AS `ver`,
  `device_event_block_list`.`block_uids` AS `block_uids`
  from `device_event_info`
  left join `device_event_block_list` on `device_event_info`.`id` = `device_event_block_list`.`device_event_info_id`;

--
-- Final view structure for view `query_info`
--

DROP VIEW IF EXISTS `query_info`;
DROP TABLE IF EXISTS `query_info`;

CREATE VIEW `query_info` AS select 
  if((`device`.`purpose` LIKE 'RV%'),`stream_server`.`internal_address`,`tunnel_server`.`internal_address`) AS `internal_ip_addr`,
  if((`device`.`purpose` LIKE 'RV%'),`stream_server`.`external_address`,`tunnel_server`.`internal_address`) AS `ip_addr`,
  if((`device`.`purpose` LIKE 'RV%'),5544,`tunnel_server_assignment`.`bind_port`) AS `internal_port`,
  if((`device`.`purpose` LIKE 'RV%'),`stream_server`.`external_port`,`tunnel_server_assignment`.`bind_port`) AS `port`,
  if((`device`.`purpose` LIKE 'RV%'),`stream_server_assignment`.`url_path`,`device`.`url_path`) AS `url_path`,
  `device`.`id` AS `id`,
  `device`.`uid` AS `uid`,
  `device`.`name` AS `name`,
  `device`.`owner_id` AS `owner_id`,
  `device`.`purpose` AS `purpose`,
  if((`device`.`purpose` LIKE 'RV%'),'http://',`device`.`url_prefix`) AS `url_prefix`,
  `device`.`enc` AS `enc`,
  `device`.`mac_addr` AS `mac_addr`,
  `device`.`service_type` AS `service_type`,
  `device`.`device_type` AS `device_type`,
  `user`.`name` AS `user_name`,
  `user`.`reg_email` AS `reg_email`,
  `user`.`oem_id` AS `oem_id`,
  `device_models`.`features` AS `features`,
  `device_models`.`default_id` AS `default_id`,
  `device_models`.`default_pw` AS `default_pw`,
  `device_models`.`manufacturer` AS `manufacturer`,
  `device_models`.`model` AS `model`,
  `device_models`.`version` AS `device_models_version`,
  if(isnull(`signal_server_online_client_list`.`id`),'false','true') AS `is_signal_online` 
from (((((((
  `device` left join `stream_server_assignment` on((`device`.`uid` = `stream_server_assignment`.`device_uid`))) 
  left join `stream_server` on((`stream_server_assignment`.`stream_server_uid` = `stream_server`.`uid`))) 
  left join `tunnel_server_assignment` on(((`device`.`url_prefix` = `tunnel_server_assignment`.`url_prefix`) and (`device`.`uid` = `tunnel_server_assignment`.`device_uid`)))) 
  left join `tunnel_server` on((`tunnel_server`.`uid` = `tunnel_server_assignment`.`tunnel_server_uid`) and (`tunnel_server`.`purpose` = 'TUNNEL'))) 
  left join `user` on((`device`.`owner_id` = `user`.`id`))) 
  left join `device_models` on((`device`.`model_id` = `device_models`.`id`))) 
  left join `signal_server_online_client_list` on((`device`.`uid` = `signal_server_online_client_list`.`uid`)));

--
-- Final view structure for view `query_share`
--

DROP VIEW IF EXISTS `query_share`;
DROP TABLE IF EXISTS `query_share`;

CREATE VIEW `query_share` AS select 
  `device_share`.`visitor_id` AS `visitor_id`,
  if((`device`.`purpose` LIKE 'RV%'),`stream_server`.`internal_address`,`tunnel_server`.`internal_address`) AS `internal_ip_addr`,
  if((`device`.`purpose` LIKE 'RV%'),`stream_server`.`external_address`,`tunnel_server`.`internal_address`) AS `ip_addr`,
  if((`device`.`purpose` LIKE 'RV%'),5544,`tunnel_server_assignment`.`bind_port`) AS `internal_port`,
  if((`device`.`purpose` LIKE 'RV%'),`stream_server`.`external_port`,`tunnel_server_assignment`.`bind_port`) AS `port`,
  if((`device`.`purpose` LIKE 'RV%'),`stream_server_assignment`.`url_path`,`device`.`url_path`) AS `url_path`,
  `device`.`id` AS `id`,
  `device`.`uid` AS `uid`,
  `device`.`name` AS `name`,
  `device`.`owner_id` AS `owner_id`,
  `device`.`purpose` AS `purpose`,
  if((`device`.`purpose` LIKE 'RV%'),'http://',`device`.`url_prefix`) AS `url_prefix`,
  `device`.`enc` AS `enc`,
  `device`.`mac_addr` AS `mac_addr`,
  `device`.`service_type` AS `service_type`,
  `device`.`device_type` AS `device_type`,
  `user`.`name` AS `user_name`,
  `user`.`reg_email` AS `reg_email`,
  `user`.`oem_id` AS `oem_id`,
  `device_models`.`features` AS `features`,
  `device_models`.`default_id` AS `default_id`,
  `device_models`.`default_pw` AS `default_pw`,
  `device_models`.`manufacturer` AS `manufacturer`,
  `device_models`.`model` AS `model`,
  `device_models`.`version` AS `device_models_version`,
  if(isnull(`signal_server_online_client_list`.`id`),'false','true') AS `is_signal_online` 
from ((((((((
  `device_share` left join `device` on((`device`.`uid` = `device_share`.`uid`))) 
  left join `stream_server_assignment` on((`device`.`uid` = `stream_server_assignment`.`device_uid`))) 
  left join `stream_server` on((`stream_server_assignment`.`stream_server_uid` = `stream_server`.`uid`))) 
  left join `tunnel_server_assignment` on(((`device`.`url_prefix` = `tunnel_server_assignment`.`url_prefix`) and (`device`.`uid` = `tunnel_server_assignment`.`device_uid`)))) 
  left join `tunnel_server` on((`tunnel_server`.`uid` = `tunnel_server_assignment`.`tunnel_server_uid`) and (`tunnel_server`.`purpose` = 'TUNNEL'))) 
  left join `user` on((`device`.`owner_id` = `user`.`id`))) 
  left join `device_models` on((`device`.`model_id` = `device_models`.`id`))) 
  left join `signal_server_online_client_list` on((`device`.`uid` = `signal_server_online_client_list`.`uid`)));

--
-- Final view structure for view `device_mapped`
--

DROP VIEW IF EXISTS `device_mapped`;
DROP TABLE IF EXISTS `device_mapped`;

CREATE VIEW `device_mapped` AS select 
  `a`.`id` AS `id`,
  `a`.`uid` AS `uid`,
  `a`.`license_id` AS `license_id`,
  `a`.`sid` AS `sid`,
  `a`.`name` AS `name`,
  `a`.`owner_id` AS `owner_id`,
  `a`.`mac_addr` AS `mac_addr`,
  `a`.`identifier` AS `identifier`,
  `a`.`service_type` AS `service_type`,
  `a`.`device_type` AS `device_type`,
  `a`.`purpose` AS `purpose`,
  `a`.`url_prefix` AS `url_prefix`,
  `a`.`ip_addr` AS `ip_addr`,
  `b`.`bind_port` AS `port`,
  `a`.`url_path` AS `url_path`,
  `a`.`reg_date` AS `reg_date`,
  `a`.`update_date` AS `update_date`,
  `a`.`expire_date` AS `expire_date`,
  `a`.`query_geo_locate` AS `query_geo_locate`,
  `a`.`geo_locate_lat` AS `geo_locate_lat`,
  `a`.`geo_locate_lng` AS `geo_locate_lng`,
  `a`.`public_status` AS `public_status`,
  `a`.`internal_ip_addr` AS `internal_ip_addr`,
  `a`.`internal_port` AS `internal_port`,
  `a`.`enc` AS `enc`,
  `a`.`version` AS `version`,
  `a`.`auth` AS `auth`,
  `a`.`model_id` AS `model_id`,
  `a`.`update_model_id` AS `update_model_id` 
from (`device` `a` left join `tunnel_server_assignment` `b` 
  on(((`a`.`uid` = `b`.`device_uid`) and (`a`.`url_prefix` = `b`.`url_prefix`))));
-- Dump completed on 2014-03-04 14:21:24
