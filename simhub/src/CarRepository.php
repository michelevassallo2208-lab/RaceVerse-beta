<?php
// src/CarRepository.php
class CarRepository {
    protected $pdo;
    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }
    public function getCarsByGame(string $game): array {
        if ($game === 'LMU') {
            return [
                'Toyota GR010-Hybrid',
                'Porsche 963',
                'Ferrari 499P',
                'Cadillac V-Series.R',
                'BMW M Hybrid V8',
            ];
        }
        return [];
    }
}
