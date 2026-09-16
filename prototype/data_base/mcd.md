# MCD - Gestion des produits

```mermaid
erDiagram

    CUSTOMER {
        int id_customer PK
        varchar first_name
        varchar last_name
        varchar email
        varchar password
    }

    COLLECTION {
        int id_collection PK
        varchar name
        text description
    }

    PRODUCT {
        int id_product PK
        varchar name
        text description
        decimal price
        int stock
        varchar image
        int id_customer FK
        int id_collection FK
    }

    CUSTOMER ||--o{ PRODUCT : AJOUTER
    COLLECTION ||--o{ PRODUCT : CONTENIR
 ````
    1 Customer → 0 à plusieurs Products
    1 Collection → 0 à plusieurs Products