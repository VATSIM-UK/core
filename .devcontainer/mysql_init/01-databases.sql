-- Database initialisation script.

-- Create the application databases.
CREATE DATABASE IF NOT EXISTS `core`;
CREATE DATABASE IF NOT EXISTS `cts`;

-- Grant root access from other containers in the Docker network.
-- Intended for local development only.
GRANT ALL PRIVILEGES ON *.* TO 'root'@'%';
