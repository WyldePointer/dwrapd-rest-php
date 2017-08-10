<?php
/*
 * Copyright (c) 2017, Sohrab Monfared <sohrab.monfared@gmail.com>
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 *    * Redistributions of source code must retain the above copyright
 *      notice, this list of conditions and the following disclaimer.
 *    * Redistributions in binary form must reproduce the above copyright
 *      notice, this list of conditions and the following disclaimer in the
 *      documentation and/or other materials provided with the distribution.
 *    * Neither the name of the <organization> nor the
 *      names of its contributors may be used to endorse or promote products
 *      derived from this software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND
 * ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
 * WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
 * DISCLAIMED. IN NO EVENT SHALL <COPYRIGHT HOLDER> BE LIABLE FOR ANY
 * DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES
 * (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
 * LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND
 * ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS
 * SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 */

require "libdwrapd.php";
require "functions_misc.php";

$redis_caching_enabled = true;
$redis_ip_address = "127.0.0.1";
$redis_tcp_port = 6379;
$redis_database_index = 0;
$redis_caching_ttl = 10; /* in seconds */

/* Internal variables. DO NOT change them! */
$json = false;
$url_array = NULL;
$limit = 0;
$limit_index = 0;
$record_type_to_lookup = NULL;
$lookup_result = NULL;
$redis = NULL; /* Instance of redis */

$url = url_to_array();

if (!isset($url[0])){
  printf("ERROR_NO_ACTION_SUPPLIED");
  exit(-1);
}

switch ($url[0]){

  case "get_a":
    $record_type_to_lookup = 'A';
    break;

  case "get_mx":
    $record_type_to_lookup = "MX";
    break;

  default:
    printf("ERROR_INVALID_ACTION");
    exit(-2);

}

if (!isset($url[1])){
  printf("ERROR_NO_DOMAIN_SUPPLIED");
  exit(-3);
}

if (!dwrapd_is_valid_domain_name($url[1])){
  printf("ERROR_INVALID_DOMAIN_NAME");
  exit(-4);
}



$json = array_search("json", $url);

$limit_index = array_search("limit", $url);

/* If the word "limit" was found in URL */
if ($limit_index){
  $limit = 1;
}

/* If there was a number next to "limit" in URL */
if ($limit && isset($url[$limit_index+1])){

  if (intval($url[$limit_index+1]) == $url[$limit_index+1]){
    $limit = $url[$limit_index+1];
  }

}

if ($redis_caching_enabled){

  $redis = dwrapd_redis_connect_tcp($redis_ip_address, $redis_tcp_port, $redis_database_index);

  if ($redis){

    $lookup_result = dwrapd_redis_get_records($redis, $url[1], $record_type_to_lookup, $limit);

    if (!$lookup_result){

      if ($record_type_to_lookup == 'A'){
        $lookup_result = dwrapd_do_dns_lookup_a($url[1]);
      }

      if ($record_type_to_lookup == "MX"){
        $lookup_result = dwrapd_do_dns_lookup_mx($url[1]);
      }

      if ($lookup_result){

        dwrapd_redis_set_records($redis, $url[1], $record_type_to_lookup, $lookup_result);

        dwrapd_redis_set_expire($redis, $url[1], $redis_caching_ttl);

      } else {

        /*
         * TODO: Marking the domain name as _NOT_FOUND_ in redis
         *       so we won't lookup again until the cache is expired.
        */

      }

    } else {

      /* TODO: Storing HIT statistics. */

    }

  }

}

if (!$redis){

    if ($record_type_to_lookup == 'A'){
      $lookup_result = dwrapd_do_dns_lookup_a($url[1], $limit);
    }

    if ($record_type_to_lookup == "MX"){
      $lookup_result = dwrapd_do_dns_lookup_mx($url[1], $limit);
    }

}

if ($lookup_result === false || $lookup_result < 0){

  if ($record_type_to_lookup == 'A'){
    printf("ERROR_NO_IP_FOUND");
  }

  if ($record_type_to_lookup == "MX"){
    printf("ERROR_NO_MX_FOUND");
  }

  exit(-5);
}

if (is_array($lookup_result)){

  if ($record_type_to_lookup == 'A'){
    display_ips($lookup_result, $json, $limit);
  }

  if ($record_type_to_lookup == "MX"){
    display_mx_records($lookup_result, $json, $limit);
  }

} else {

  /* Case of single A record lookup since gethostbyname() returns string */

  if ($record_type_to_lookup == 'A' || !empty($lookup_result)){
    echo $lookup_result;
  }

}


return true;
