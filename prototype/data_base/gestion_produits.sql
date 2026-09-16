CREATE DATABASE gestion_produits;

USE gestion_produits;

CREATE TABLE customers (
    id_customer INT AUTO_INCREMENT PRIMARY KEY,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL
);

CREATE TABLE collections (
    id_collection INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT
);

CREATE TABLE products (
    id_product INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    description TEXT,
    price DECIMAL(10,2) NOT NULL,
    stock INT NOT NULL,
    image VARCHAR(255),
    id_customer INT NOT NULL,
    id_collection INT NOT NULL,

    FOREIGN KEY (id_customer)
        REFERENCES customers(id_customer),

    FOREIGN KEY (id_collection)
        REFERENCES collections(id_collection)
);