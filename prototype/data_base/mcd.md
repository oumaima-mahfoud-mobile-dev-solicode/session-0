# MCD - Gestion des produits

```mermaid
erDiagram

    CUSTOMER {
        int id_customer 
        varchar first_name
        varchar last_name
        varchar email
        varchar password
    }

    COLLECTION {
        int id_collection 
        varchar name
        text description
    }

    PRODUCT {
        int id_product 
        varchar name
        text description
        decimal price
        int stock
        varchar image
        int id_customer 
        int id_collection 
    }

    CUSTOMER ||--o{ PRODUCT : AJOUTER
    COLLECTION ||--o{ PRODUCT : CONTENIR
 ````
    1 Customer → 0 à plusieurs Products
    1 Collection → 0 à plusieurs Products