docker build -t database:1.0 database
docker build -t hive:1.0 .
docker network create hive-network
docker run -d --name hive-database --network hive-network -p 3306:3306 database:1.0
docker run -d --name hive-game --network hive-network -p 8000:8000 hive:1.0