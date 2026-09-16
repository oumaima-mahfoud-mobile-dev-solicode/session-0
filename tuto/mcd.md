```mermaid
erDiagram

    CLIENT {
        int id_client 
        string nom_client
        string email_client
    }

    COMMANDE {
        int id_commande 
        date date_commande
    }

    PRODUIT {
        int id_produit 
        string nom_produit
        decimal prix_produit
    }

    CLIENT ||--o{ COMMANDE : passer 
    COMMANDE }|--|{ PRODUIT : contenir 

```
CLIENT (0,N) ─── PASSER ─── (1,1) COMMANDE

COMMANDE (1,N) ─── CONTENIR ─── (0,N) PRODUIT