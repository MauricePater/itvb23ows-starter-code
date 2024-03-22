#!/bin/bash
docker build -t database:1.0 database
docker build -t hive:1.0 .
docker build -t ai:1.0 ai
docker network create hive-network
docker run -d --name hive-database --network hive-network -p 3306:3306 database:1.0
docker run -d --name hive-game --network hive-network -p 8000:8000 hive:1.0
docker run -d --name hive-ai --network hive-network -p 5000:5000 ai:1.0