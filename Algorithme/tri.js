let tableau = [8, 3, 6, 1, 5];

console.log("Avant le tri :", tableau);

for (let i = 0; i < tableau.length; i++) {
    for (let j = i + 1; j < tableau.length; j++) {

        if (tableau[i] > tableau[j]) {
            let temp = tableau[i];
            tableau[i] = tableau[j];
            tableau[j] = temp;
        }

    }
}

console.log("Après le tri :", tableau);