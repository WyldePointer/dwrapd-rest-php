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

$redis_ip_address = "127.0.0.1";
$redis_tcp_port = 6379;
$redis_database_index = 1;
$redis_save_to_disk = true;

$redis = NULL;
$record = NULL;

if (!isset($argv[1])){
  printf("Usage: %s record_file.json\n", $argv[0]);
  return -1;
}

/* Not using mime_content_type() since it depends on 'File Info' extension. */
if (substr($argv[1], -4) != "json"){
  printf("Only JSON files are accepted.\n");
  return -2;
}

if (!file_exists($argv[1])){
  printf("Can not access the file.\n");
  return -3;
}

$record = json_decode(file_get_contents($argv[1]), true);

if (!$record){
  printf("Not a valid JSON file.\n");
  return -4;
}

$redis = dwrapd_redis_connect_tcp($redis_ip_address, $redis_tcp_port, $redis_database_index);

if (!$redis){
  printf("Cannot connect to redis.\n");
  return -5;
}

if (dwrapd_redis_set_all_records($redis, $record)){

  printf("Records for %s successfully added to database.\n", key($record));

  if ($redis_save_to_disk){

    printf("Writing data to disk: ");

    if (dwrapd_redis_save_synchronously($redis)){

      printf(" Successful.\n");

    } else {

      printf(" Failed.\n");

      return -6;

    }

  }

} else {

  printf("Failed to set records for %s.\n", key($record));

  return -7;

}

return 0;
