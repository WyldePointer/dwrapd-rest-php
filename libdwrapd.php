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

function dwrapd_do_dns_lookup($hostname, $record='A'){

  $result = array();
  $php_record_type = DNS_A;

  if ($hostname == ''){
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

  /*
   *  TODO: Finding the most address-friendly regex
   *        and using it instead of doing the actual lookup.
   */

  if(!filter_var(dwrapd_do_dns_lookup_a($hostname, 1), FILTER_VALIDATE_IP)){
    return -1;
  }

  if (getmxrr($hostname, $mx_records, $weights)){

    if (count($mx_records) == count($weights)){

      foreach ($weights as $key => $value){

        /*
         *  TODO: Making sure the returned address is not spoofed.
         *        (e,g. it's a valid record)
         */

        if (isset($mx_records[$key])){
          $formatted[$value] = $mx_records[$key];
        }

      }

      if (count($formatted)>0){
        return $formatted;
      }

    }

    return $mx_records;
  }


  return 0;
}


