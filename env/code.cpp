#include <iostream>
#include <cmath>

int main() {
    int num;

    // Lee los enteros hasta el final del archivo
    while (std::cin >> num) {
        // Calcula el cuadrado
        int square = std::pow(num, 2);

        // Muestra el cuadrado
        std::cout << square << std::endl;
    }

    return 0;
}
