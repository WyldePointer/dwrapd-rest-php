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

$json = false;
$url_array = NULL;
$limit = 0;
$limit_index = 0;
$lookup_result = NULL;
$ip_array = array();
$mx_array = array();

$url = url_to_array();

if (!isset($url[0])){
  printf("ERROR_NO_ACTION_SUPPLIED");
  exit(-1);
}

if (!isset($url[1])){
  printf("ERROR_NO_DOMAIN_SUPPLIED");
  exit(-2);
}

if (!dwrapd_is_valid_domain_name($url[1])){
  printf("ERROR_INVALID_DOMAIN_NAME");
  exit(-3);
}

$json = array_search("json", $url);

$limit_index = array_search("limit", $url);

/* If the word "limit" was found in URL */
if ($limit_index){
  $limit = 1;
}

/* If there was a number next to "limit" in URL */
if (isset($url[$limit_index+1])){

  if (intval($url[$limit_index+1]) == $url[$limit_index+1]){
    $limit = $url[$limit_index+1];
  }

}

if ($url[0] == "get_a"){

  $lookup_result = dwrapd_do_dns_lookup_a($url[1], $limit);

  if ($lookup_result === false || $lookup_result < 0){
    printf("ERROR_NO_IP_FOUND");
    exit(-4);
  }

  if (is_array($lookup_result)){

    foreach ($lookup_result as $ip){

      if(filter_var($ip, FILTER_VALIDATE_IP)){
        $ip_array[] = $ip;
      }

      if (count($ip_array) >= $limit && $limit > 0){
        break;
      }

    }

  } else {

    if (!empty($lookup_result)){
      echo $lookup_result;
    }

  }

  if (count($ip_array)){

    if ($json){

      echo json_encode($ip_array);

    } else {

      foreach ($ip_array as $ip){
        echo $ip, "\n";
      }

    }

  }

}

if ($url[0] == "get_mx"){

  $lookup_result = dwrapd_do_dns_lookup_mx($url[1]);

  if ($lookup_result === false || $lookup_result < 0){
    printf("ERROR_NO_MX_FOUND");
    exit(-5);
  }

  if (is_array($lookup_result)){

    foreach ($lookup_result as $record => $weight){

      $mx_array[$record] = $weight;

      if (count($mx_array) >= $limit && $limit > 0){
        break;
      }

    }

    if (count($mx_array)){

      if ($json){

        echo json_encode($mx_array);

      } else {

        foreach ($mx_array as $record => $weight){
          echo $record, ' ', $weight, "\n";
        }

      }

    }

  }

}

return true;
