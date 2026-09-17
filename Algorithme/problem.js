let produits = [
    {"produit":"A", "prix":30, "views": 1200},
    {"produit":"B", "prix":20, "views": 2500},
    {"produit":"C", "prix":15, "views": 1800},
    {"produit":"D", "prix":40, "views": 3000},
    {"produit":"E", "prix":10, "views": 900},
    {"produit":"F", "prix":25, "views": 2000}
]
let populaires = [];
let taille = 0;

for (let i = 0; i < produits.length; i++) {

    if (produits[i].views > 1500) {

        populaires[taille] = produits[i];

        taille++;
    }
}
console.log("Populaire produits: ",populaires);

for (let i = 0; i < populaires.length - 1; i++) {

    for (let j = i + 1; j < populaires.length; j++) {

        if (populaires[i].prix > populaires[j].prix) {

            let temp = populaires[i];

            populaires[i] = populaires[j];

            populaires[j] = temp;
        }
    }
}
console.log("Trier les produits: ",populaires);
let budget = 100;
let total = 0;

let produitsAchetes = [];
let nombreAchetes = 0;

for (let i = 0; i < populaires.length; i++) {

    if (total + populaires[i].prix <= budget) {

        produitsAchetes[nombreAchetes] = populaires[i];

        nombreAchetes++;

        total = total + populaires[i].prix;
    }
}

console.log("Produits achetés :");

for (let i = 0; i < produitsAchetes.length; i++) {

    console.log(produitsAchetes[i].produit);
}

console.log("Nombre :", nombreAchetes);
console.log("Total :", total, "DH");