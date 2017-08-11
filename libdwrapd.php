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

function dwrapd_is_valid_domain_name($hostname){

  if (strlen($hostname) > 253){
    return false;
  }

  return preg_match("/^([a-z\d](-*[a-z\d])*)(\.([a-z\d](-*[a-z\d])*))*$/i", $hostname);
}


function dwrapd_do_dns_lookup($hostname, $record='A'){

  $result = array();
  $php_record_type = DNS_A;

  if (empty($hostname)){
    return -1;
  }

  switch ($record){
    case "txt":
    case "TXT":
      $php_record_type = DNS_TXT;
      break;

    case 'a':
    case 'A':
      $php_record_type = DNS_A;
      break;

    default:
      return -13; /* DWRAPD_LOOKUP_NOT_IMPLEMENTED_FOR_RECORD */
  }

  $result = dns_get_record($hostname, $php_record_type);

  if (isset($result[0]["type"])){

    switch ($result[0]["type"]){

      case "TXT":
        return $result[0]["txt"];
        break;

      case 'A':
        return $result[0]["ip"];
        break;

    }

  }

  return false;
}


function dwrapd_do_dns_lookup_a($hostname, $limit=0){

  $ips = NULL;

  if ($limit == 1){

    $ips = gethostbyname($hostname);

  }else{

    $ips = gethostbynamel($hostname);

  }

  return ($ips != $hostname) ? $ips : false;
}


function dwrapd_do_dns_lookup_mx($hostname){

  $mx_records = array();
  $weights = array();
  $formatted = array();

  if (!dwrapd_is_valid_domain_name($hostname)){
    return -1;
  }

  if (getmxrr($hostname, $mx_records, $weights)){

    if (count($mx_records) == count($weights)){

      foreach ($weights as $key => $value){

        if (isset($mx_records[$key])){

          if (dwrapd_is_valid_domain_name($mx_records[$key])){
            $formatted[$mx_records[$key]] = $value;
          }

        }

      }

      if (count($formatted)){
        return $formatted;
      }

    }

    if (count($mx_records)){
      return $mx_records;
    }
  }


  return false;
}


function dwrapd_redis_connect_tcp($ip_address, $port=6379, $index=0){

  if (!extension_loaded("redis")){
    /* TODO: Log "Redis extension is not loaded" */
    return false;
  }

  $redis = new Redis();

  if ($redis->connect($ip_address, $port)){

    if ($index === 0){
      return $redis;
    }

    if ($redis->select($index)){
      /* TODO: Log "Cannot select database $index" */
      return $redis;
    }

  }

  return false;
}


function dwrapd_redis_get_records(Redis $redis, $hostname, $record_type, $limit=0){

  $record = NULL;
  $i = 0;
  $result_array = array();

  $record = unserialize($redis->get($hostname));

  if (isset($record[$record_type])){

    if ($limit === 0){

      return $record[$record_type];

    } elseif ($limit > 0){

      if ($record_type == 'A'){

        foreach ($record[$record_type] as $a_record){

          $result_array[] = $a_record;

          if (++$i >= $limit){
            break;
          }

        }

      }

      if ($record_type == "MX"){

        foreach ($record[$record_type] as $mx_record => $weight){

          $result_array[$mx_record] = $weight;

          if (++$i >= $limit){
            break;
          }

        }

      }

      if (count($result_array)){
        return $result_array;
      }

    }

  }

  return false;
}


function dwrapd_redis_set_records(Redis $redis, $hostname, $record_type, array $records){

  if ($current = unserialize($redis->get($hostname))){

    $current[$record_type] = $records;

    if ($redis->set($hostname, serialize($current))){
      return true;
    }

  } else {

    return $redis->set($hostname, serialize(array($record_type => $records)));

  }

  return false;
}


function dwrapd_redis_set_all_records(Redis $redis, $record){

  $domain = key($record);
  $records = $record[$domain];

  if ($redis->set($domain, serialize($records))){
    return true;
  }

  return false;
}


function dwrapd_redis_set_expire(Redis $redis, $hostname, $ttl){
  return $redis->expire($hostname, $ttl);
}


function dwrapd_redis_select_database(Redis $redis, $database_index){
  return $redis->select($database_index);
}


function dwrapd_redis_save_synchronously(Redis $redis){
  return $redis->save();
}
