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

INSERT INTO customers
(first_name, last_name, email, password)
VALUES
('Admin', 'Admin', 'admin@test.com', '1234');

INSERT INTO collections
(name, description)
VALUES
('Ete 2026', 'Collection ete 2026'),
('Automne 2026', 'Collection automne 2026');

INSERT INTO products
(name, description, price, stock, image, id_customer, id_collection)
VALUES
('T-shirt blanc', 'T-shirt simple en coton', 120.00, 10, 'tshirt.jpg', 1, 1),
('Robe noire', 'Robe elegante pour femme', 250.00, 5, 'robe.jpg', 1, 1),
('Sac rose', 'Sac a main moderne', 180.00, 8, 'sac.jpg', 1, 1),
('Chaussures blanches', 'Chaussures confortables', 300.00, 6, 'chaussures.jpg', 1, 2);
UPDATE products
set image = concat('images/' , image)
where image Not like 'images/%';