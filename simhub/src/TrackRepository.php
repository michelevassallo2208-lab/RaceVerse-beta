<?php
// src/TrackRepository.php
class TrackRepository {
    protected $pdo;
    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }
    public function getTracksByGame(string $game): array {
        if ($game === 'LMU') {
            return [
                'Le Mans Circuit de la Sarthe',
                'Spa-Francorchamps',
                'Monza',
                'Silverstone',
                'Sebring',
            ];
        }
        return [];
    }
}
