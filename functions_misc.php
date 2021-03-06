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

function url_to_array(){

  $sorted = array();

  $all = explode('/', $_SERVER["REQUEST_URI"]);
  unset($all[0]);  /* remove the empty index from the beginning of array */

  foreach ($all as $param){
    if ((intval($param)!=0) && ($param == intval($param)) && (strlen($param) == strlen(intval($param))) ){
      $sorted[] = intval($param);
    }else{
      if (!empty($param)){
        $sorted[] = $param;
      }
    }
  }

  return $sorted;
}


function display_ips(array $lookup_result, $json=0, $limit=0){

  $ip_array = array();

  foreach ($lookup_result as $ip){

    if(filter_var($ip, FILTER_VALIDATE_IP)){
      $ip_array[] = $ip;
    }

    if (count($ip_array) >= $limit && $limit > 0){
      break;
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

  return true;
}


function display_mx_records(array $lookup_result, $json, $limit){

  $mx_array = array();

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

  return true;
}
