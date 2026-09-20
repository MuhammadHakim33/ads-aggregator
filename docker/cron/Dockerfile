FROM muhammadhakim33/ads-aggregator-app:latest

RUN apt-get update && apt-get install -y cron && rm -rf /var/lib/apt/lists/*

COPY docker/cron/crontab /etc/cron.d/ads-cron
RUN chmod 0644 /etc/cron.d/ads-cron && crontab /etc/cron.d/ads-cron

CMD ["cron", "-f"]
