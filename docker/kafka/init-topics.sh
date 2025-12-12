#!/bin/bash
set -e

KAFKA_BOOTSTRAP_SERVER=${KAFKA_BOOTSTRAP_SERVER:-localhost:9092}
TOPICS="logs.laravel logs.web3-service logs.activities"

echo "Waiting for Kafka to be ready..."
sleep 10

for topic in $TOPICS; do
  echo "Creating topic: $topic"
  /opt/kafka/bin/kafka-topics.sh \
    --bootstrap-server $KAFKA_BOOTSTRAP_SERVER \
    --create \
    --topic $topic \
    --partitions 1 \
    --replication-factor 1 \
    --if-not-exists || true
done

echo "Topics created successfully:"
/opt/kafka/bin/kafka-topics.sh --bootstrap-server $KAFKA_BOOTSTRAP_SERVER --list
