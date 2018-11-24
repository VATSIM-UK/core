#!/usr/bin/env bash

#define vars
OUTPUT_FILE=".env";
INPUT_FILE=".env.example"
NEW_CONFIG=()

echo "[INFO]: .env file generation."
if [ -f $OUTPUT_FILE ]; then
  read -p ".env Detected, Do you want to skip .env generation? (Y/n): " -n 1 -r reply
  echo ""
  if [[ $reply =~ ^[Yy]$ ]]; then
    exit 0
  else
    read -p "Do you want to use your existing .env to build your new .env file? (Y/n): " -n 1 -r reply
    echo ""
    if [[ $reply =~ ^[Yy]$ ]]; then
      INPUT_FILE=$OUTPUT_FILE
    fi
  fi
fi

echo ""
echo "[INFO]: Generating .env File"
echo "[NOTE]: Press enter to leave the value unchanged"
while IFS='=' read -ra line;
do
  if ! [ -z "$line" ]; then
    if ! [[ ${line:0:1} == '#' ]]; then
      read -p "SET: ${line[0]} (${line[1]}): " input < /dev/tty
      newline="${line[0]}=${input:-${line[1]}}"
      NEW_CONFIG+=("$newline")
    elif [[ ${line:0:2} == '##' ]]; then
      echo "DESC:${line:2}"
    fi
  fi
done < $INPUT_FILE

> $OUTPUT_FILE

for line in "${NEW_CONFIG[@]}"
do
	echo $line >> $OUTPUT_FILE
done
