# EasyMovers

EasyMovers is a dynamic, fully-featured PHP & MySQL web application configured for seamless, scalable deployment on both traditional shared hosting (like InfinityFree) and modern cloud platforms (like AWS EC2) using Docker.

## Deployment with Docker (AWS EC2 / Local)

This repository includes a `docker-compose.yml` configuration to orchestrate both the PHP web server and MySQL database simultaneously. The database schema is instantly auto-imported upon launch.

### 1. Requirements
Ensure your host server or local machine has [Docker](https://docs.docker.com/get-docker/) and [Docker Compose](https://docs.docker.com/compose/install/) installed.

### 2. Getting Started
Clone this repository to your instance and enter the directory:
```bash
git clone https://github.com/Van1sha/moving-company.git
cd moving-company
```

### 3. Launching the Environment
To spin up the PHP Apache server and MySQL database, run the building command:
```bash
sudo DOCKER_BUILDKIT=0 docker-compose up -d --build
```
*(Note: The `DOCKER_BUILDKIT=0` prefix guarantees compatibility with default AWS Amazon Linux docker packages).*

### 4. Viewing the Live Website
1. Ensure your server's firewall (AWS EC2 Security Group) has an Inbound Rule allowing **Custom TCP** traffic on Port `8080` from source `0.0.0.0/0`.
2. Open your web browser and visit `http://34.229.163.17:8080`.

## Architecture Enhancements
- **Dynamic Database Credentials**: `db_connect.php` seamlessly routes environment variables from Docker, but safely cascades to static InfinityFree credentials if the variables are absent.
- **Intelligent SMTP Fallback**: `signup.php` has been refactored to catch strict SMTP connection blocks (e.g. Free Tier host port 587 blocking) and silently bypassing the email OTP verification so users can continue registering uninterrupted.
- **Persistent Storage**: Docker volumes are configured to securely persist user data over reboots.
