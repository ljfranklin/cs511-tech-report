CREATE DATABASE wordpress;
CREATE USER 'wordpress'@'localhost' IDENTIFIED BY 'wp1234';
GRANT ALL PRIVILEGES ON wordpress.* TO 'wordpress'@'localhost';

CREATE DATABASE tech_papers;
GRANT ALL PRIVILEGES ON tech_papers.* TO 'wordpress'@'localhost';
